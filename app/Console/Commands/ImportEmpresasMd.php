<?php

namespace App\Console\Commands;

use App\Models\Empresa;
use App\Models\TutorLaboral;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportEmpresasMd extends Command
{
    protected $signature = 'empresas:import-md {file=empresas_26_27.md} {--dry-run : Ejecutar en modo simulación sin guardar cambios}';
    protected $description = 'Importa y actualiza empresas desde el listado en formato Markdown sin sobreescribir información existente';

    public function handle(): int
    {
        $filePath = base_path($this->argument('file'));
        if (!file_exists($filePath)) {
            $this->error("El archivo no existe en: {$filePath}");
            return Command::FAILURE;
        }

        $content = file_get_contents($filePath);
        $sections = preg_split('/\n(?=## )/', $content);
        // Descartar encabezado antes del primer '## '
        array_shift($sections);

        $this->info("Procesando " . count($sections) . " secciones de empresas del archivo Markdown...");

        $mdEmpresas = [];
        foreach ($sections as $sec) {
            $lines = explode("\n", trim($sec));
            $title = trim(str_replace('##', '', $lines[0]));
            if (empty($title)) {
                continue;
            }

            $data = ['nombre' => $title];
            for ($i = 1; $i < count($lines); $i++) {
                $line = trim($lines[$i]);
                if (preg_match('/^- \*\*([^*]+):\*\*\s*(.*)$/', $line, $matches)) {
                    $data[trim($matches[1])] = trim($matches[2]);
                }
            }
            $mdEmpresas[] = $data;
        }

        $dbEmpresas = Empresa::with('tutoresLaborales')->get();
        $this->info("Empresas existentes en la base de datos: " . $dbEmpresas->count());

        $dbByCif = [];
        $dbByName = [];
        foreach ($dbEmpresas as $e) {
            $c = $this->normCif($e->cif);
            if ($c) {
                $dbByCif[$c] = $e;
            }
            $n = $this->normName($e->nombre);
            if ($n) {
                $dbByName[$n] = $e;
            }
        }

        $aliases = [
            $this->normName('Vertigo Apps') => $this->normName('Vértigo Apps'),
            $this->normName('Kyndryl') => $this->normName('Kyndryl'),
            $this->normName('Bemovil (Cellnet Tecom, S.L.) Central') => $this->normName('Cellnet Tecom, S.L.'),
            $this->normName('Winecta (App&Web)') => $this->normName('App&Web'),
            $this->normName('Desgranados desarrollo web') => $this->normName('Área F5 (Desgranados desarrollo web)'),
        ];

        // CIFs compartidos por instituciones públicas (Junta, UGR, Ayuntamiento)
        $sharedCifs = ['S4111001F', 'Q1818002F', 'P1808900C'];

        $toInsert = [];
        $toUpdate = [];
        $alreadyComplete = 0;
        $processedNames = [];

        foreach ($mdEmpresas as $m) {
            $nameNorm = $this->normName($m['nombre']);
            $cifRaw = isset($m['CIF/DNI']) ? trim($m['CIF/DNI']) : '';
            $cifNorm = $this->normCif($cifRaw);

            // Evitar secciones duplicadas exactas dentro del MD
            if (isset($processedNames[$nameNorm])) {
                continue;
            }
            $processedNames[$nameNorm] = true;

            $foundDb = null;
            if ($cifNorm && !in_array($cifNorm, $sharedCifs, true) && isset($dbByCif[$cifNorm])) {
                $foundDb = $dbByCif[$cifNorm];
            } elseif (isset($dbByName[$nameNorm])) {
                $foundDb = $dbByName[$nameNorm];
            } elseif (isset($aliases[$nameNorm], $dbByName[$aliases[$nameNorm]])) {
                $foundDb = $dbByName[$aliases[$nameNorm]];
            }

            if ($foundDb) {
                $fieldsDiff = [];
                if (empty($foundDb->direccion) && !empty($m['Dirección'])) {
                    $fieldsDiff['direccion'] = $m['Dirección'];
                }
                if (empty($foundDb->telefono) && !empty($m['Teléfono 1'])) {
                    $fieldsDiff['telefono'] = $m['Teléfono 1'];
                }
                if (empty($foundDb->telefono_2) && !empty($m['Teléfono 2'])) {
                    $fieldsDiff['telefono_2'] = $m['Teléfono 2'];
                } elseif (empty($foundDb->telefono_2) && !empty($foundDb->telefono) && !empty($m['Teléfono 1']) && $m['Teléfono 1'] !== $foundDb->telefono) {
                    $fieldsDiff['telefono_2'] = $m['Teléfono 1'];
                }
                if (empty($foundDb->email) && !empty($m['Email'])) {
                    $fieldsDiff['email'] = $m['Email'];
                }
                if (empty($foundDb->responsable_nombre) && !empty($m['Representante legal'])) {
                    $fieldsDiff['responsable_nombre'] = $m['Representante legal'];
                }
                if (empty($foundDb->responsable_dni) && !empty($m['DNI representante legal'])) {
                    $fieldsDiff['responsable_dni'] = $m['DNI representante legal'];
                }
                if (empty($foundDb->cif) && !empty($cifRaw)) {
                    $fieldsDiff['cif'] = $cifRaw;
                }

                $tutorDiff = null;
                if ($foundDb->tutoresLaborales->isEmpty() && !empty($m['Tutor laboral'])) {
                    $tutorDiff = [
                        'nombre' => $m['Tutor laboral'],
                        'email' => !empty($m['Email tutor laboral']) ? $m['Email tutor laboral'] : null,
                        'telefono' => !empty($m['Teléfono tutor laboral']) ? $m['Teléfono tutor laboral'] : null,
                    ];
                }

                if (!empty($fieldsDiff) || $tutorDiff !== null) {
                    $toUpdate[] = [
                        'empresa' => $foundDb,
                        'fields' => $fieldsDiff,
                        'tutor' => $tutorDiff,
                    ];
                } else {
                    $alreadyComplete++;
                }
            } else {
                $tel1 = $m['Teléfono 1'] ?? null;
                $tel2 = $m['Teléfono 2'] ?? null;
                if ($m['nombre'] === 'Nevada Telecom') {
                    $tel1 = '624603832';
                    $tel2 = '613999999';
                }

                $newEmpresaData = [
                    'nombre' => $m['nombre'],
                    'cif' => !empty($cifRaw) ? $cifRaw : null,
                    'direccion' => $m['Dirección'] ?? null,
                    'telefono' => $tel1,
                    'telefono_2' => $tel2,
                    'email' => $m['Email'] ?? null,
                    'responsable_nombre' => $m['Representante legal'] ?? null,
                    'responsable_dni' => $m['DNI representante legal'] ?? null,
                    'is_active' => true,
                ];

                $tutorNew = null;
                if (!empty($m['Tutor laboral'])) {
                    $tutorNew = [
                        'nombre' => $m['Tutor laboral'],
                        'email' => !empty($m['Email tutor laboral']) ? $m['Email tutor laboral'] : null,
                        'telefono' => !empty($m['Teléfono tutor laboral']) ? $m['Teléfono tutor laboral'] : null,
                    ];
                }

                $toInsert[] = [
                    'empresa' => $newEmpresaData,
                    'tutor' => $tutorNew,
                ];
            }
        }

        $this->info("---------------------------------------------");
        $this->info("Empresas existentes ya completas: {$alreadyComplete}");
        $this->info("Empresas existentes a actualizar (completando campos vacíos): " . count($toUpdate));
        $this->info("Empresas NUEVAS a insertar: " . count($toInsert));
        $this->info("---------------------------------------------");

        if ($this->option('dry-run')) {
            $this->warn("Modo SIMULACIÓN (--dry-run). No se han aplicado cambios.");
            return Command::SUCCESS;
        }

        DB::transaction(function () use ($toUpdate, $toInsert) {
            foreach ($toUpdate as $item) {
                /** @var Empresa $empresa */
                $empresa = $item['empresa'];
                if (!empty($item['fields'])) {
                    $empresa->update($item['fields']);
                }
                if ($item['tutor']) {
                    TutorLaboral::create([
                        'empresa_id' => $empresa->id,
                        'nombre' => $item['tutor']['nombre'],
                        'email' => $item['tutor']['email'],
                        'telefono' => $item['tutor']['telefono'],
                    ]);
                }
            }

            foreach ($toInsert as $item) {
                $empresa = Empresa::create($item['empresa']);
                if ($item['tutor']) {
                    TutorLaboral::create([
                        'empresa_id' => $empresa->id,
                        'nombre' => $item['tutor']['nombre'],
                        'email' => $item['tutor']['email'],
                        'telefono' => $item['tutor']['telefono'],
                    ]);
                }
            }
        });

        $totalFinal = Empresa::count();
        $this->info("¡Importación completada con éxito!");
        $this->info("Total de empresas en la base de datos ahora: {$totalFinal}");

        return Command::SUCCESS;
    }

    private function normCif(?string $cif): string
    {
        if (empty($cif)) {
            return '';
        }
        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $cif));
    }

    private function normName(?string $name): string
    {
        if (empty($name)) {
            return '';
        }
        $name = mb_strtolower($name, 'UTF-8');
        $trans = [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u',
            'ñ' => 'n', 'ü' => 'u'
        ];
        $name = strtr($name, $trans);
        return preg_replace('/[^a-z0-9]/', '', $name);
    }
}
