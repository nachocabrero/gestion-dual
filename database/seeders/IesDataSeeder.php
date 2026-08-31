<?php

namespace Database\Seeders;

use App\Models\Asignatura;
use App\Models\Ciclo;
use App\Models\Empresa;
use App\Models\Familia;
use App\Models\Convenio;
use App\Models\Grupo;
use App\Models\Linea;
use App\Models\Profesor;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class IesDataSeeder extends Seeder
{
    public function run(): void
    {
        // --- Profesores ---
        $profesoresData = [
            ['name' => 'Antonio García López', 'email' => 'agarcia@ieshlanz.es', 'especialidad' => 'Bases de Datos', 'es_tutor' => true, 'es_coordinador_dual' => true],
            ['name' => 'María Fernández Ruiz', 'email' => 'mfernandez@ieshlanz.es', 'especialidad' => 'Desarrollo Web', 'es_tutor' => true, 'es_coordinador_dual' => false],
            ['name' => 'José Martín Soto', 'email' => 'jmartin@ieshlanz.es', 'especialidad' => 'Sistemas Informáticos', 'es_tutor' => false, 'es_coordinador_dual' => true],
            ['name' => 'Ana Rodríguez Peña', 'email' => 'arodriguez@ieshlanz.es', 'especialidad' => 'Seguridad Informática', 'es_tutor' => true, 'es_coordinador_dual' => false],
            ['name' => 'Pedro Sánchez Navarro', 'email' => 'psanchez@ieshlanz.es', 'especialidad' => 'Desarrollo Multiplataforma', 'es_tutor' => false, 'es_coordinador_dual' => false],
            ['name' => 'Laura Jiménez Torres', 'email' => 'ljimenez@ieshlanz.es', 'especialidad' => 'Formación Laboral', 'es_tutor' => true, 'es_coordinador_dual' => false],
            ['name' => 'Carlos Romero Gil', 'email' => 'cromero@ieshlanz.es', 'especialidad' => 'Redes', 'es_tutor' => false, 'es_coordinador_dual' => false],
            ['name' => 'Isabel Moreno Castillo', 'email' => 'imoreno@ieshlanz.es', 'especialidad' => 'Acceso a Datos', 'es_tutor' => true, 'es_coordinador_dual' => false],
        ];

        $profesores = [];
        foreach ($profesoresData as $p) {
            $user = User::firstOrCreate(
                ['email' => $p['email']],
                [
                    'name' => $p['name'],
                    'password' => Hash::make('password'),
                    'roles' => [User::ROLE_PROFESOR],
                    'is_active' => true,
                    'consent_rgpd' => true,
                    'consent_rgpd_at' => now(),
                ]
            );

            $profesor = Profesor::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'especialidad' => $p['especialidad'],
                    'es_tutor' => $p['es_tutor'],
                    'es_coordinador_dual' => $p['es_coordinador_dual'],
                ]
            );

            $profesores[] = $profesor;
        }

        // --- Asignar asignaturas a profesores ---
        $asignaturas = Asignatura::where('is_active', true)->get();
        $ciclos = Ciclo::where('is_active', true)->get();

        // DAW
        $daw = $ciclos->firstWhere('codigo', 'DAW');
        if ($daw) {
            $dawAsigs = $asignaturas->where('ciclo_id', $daw->id);
            if (isset($profesores[1])) $profesores[1]->asignaturas()->attach($dawAsigs->where('codigo', 'DAWES')->first()?->id);
            if (isset($profesores[1])) $profesores[1]->asignaturas()->attach($dawAsigs->where('codigo', 'DAWEC')->first()?->id);
            if (isset($profesores[0])) $profesores[0]->asignaturas()->attach($dawAsigs->where('codigo', 'BD')->first()?->id);
            if (isset($profesores[5])) $profesores[5]->asignaturas()->attach($dawAsigs->where('codigo', 'FG')->first()?->id);
        }

        // DAM
        $dam = $ciclos->firstWhere('codigo', 'DAM');
        if ($dam) {
            $damAsigs = $asignaturas->where('ciclo_id', $dam->id);
            if (isset($profesores[4])) $profesores[4]->asignaturas()->attach($damAsigs->where('codigo', 'DAMES')->first()?->id);
            if (isset($profesores[4])) $profesores[4]->asignaturas()->attach($damAsigs->where('codigo', 'DAMC')->first()?->id);
            if (isset($profesores[0])) $profesores[0]->asignaturas()->attach($damAsigs->where('codigo', 'BD')->first()?->id);
            if (isset($profesores[7])) $profesores[7]->asignaturas()->attach($damAsigs->where('codigo', 'DAIM')->first()?->id);
        }

        // SMR
        $smr = $ciclos->firstWhere('codigo', 'SMR');
        if ($smr) {
            $smrAsigs = $asignaturas->where('ciclo_id', $smr->id);
            if (isset($profesores[2])) $profesores[2]->asignaturas()->attach($smrAsigs->where('codigo', 'MME')->first()?->id);
            if (isset($profesores[6])) $profesores[6]->asignaturas()->attach($smrAsigs->where('codigo', 'SOM')->first()?->id);
            if (isset($profesores[7])) $profesores[7]->asignaturas()->attach($smrAsigs->where('codigo', 'RL')->first()?->id);
            if (isset($profesores[3])) $profesores[3]->asignaturas()->attach($smrAsigs->where('codigo', 'SR')->first()?->id);
            if (isset($profesores[5])) $profesores[5]->asignaturas()->attach($smrAsigs->where('codigo', 'FG')->first()?->id);
        }

        // --- Asignar profesores a grupos (equipos educativos) ---
        $grupos = Grupo::where('is_active', true)->get();
        foreach ($grupos as $index => $grupo) {
            if (isset($profesores[$index % count($profesores)])) {
                $profesores[$index % count($profesores)]->gruposImpartidos()->syncWithoutDetaching([$grupo->id]);
            }
        }

        // --- Empresas de ejemplo ---
        $empresasData = [
            ['nombre' => 'Café de la Cruz', 'cif' => 'B12345678', 'direccion' => 'Calle Pintor Manuel Roldán, 12, Granada', 'telefono' => '958 22 12 34', 'email' => 'info@cafelacruz.es', 'responsable' => 'Juan Cruz López', 'dni' => '12345678A'],
            ['nombre' => 'Diputación de Granada', 'cif' => 'S1800001J', 'direccion' => 'Av. del Pintor Manuel Roldán, s/n, Granada', 'telefono' => '958 03 92 00', 'email' => 'info@degranada.es', 'responsable' => 'María del Carmen Ruiz', 'dni' => '87654321B'],
            ['nombre' => 'Ayuntamiento de Granada', 'cif' => 'S1800002K', 'direccion' => 'Plaza del ayuntamiento, s/n, Granada', 'telefono' => '958 20 20 00', 'email' => 'info@granada.es', 'responsable' => 'Antonio Sánchez', 'dni' => '11223344C'],
            ['nombre' => 'Junta de Andalucía', 'cif' => 'S1800003L', 'direccion' => 'Calle Adelfos, 20, El Palmar, Granada', 'telefono' => '955 02 12 00', 'email' => 'info@juntadeandalucia.es', 'responsable' => 'Francisco Jiménez', 'dni' => '55667788D'],
            ['nombre' => 'CajaGranada', 'cif' => 'A18123456', 'direccion' => 'Calle Recogidas, 56, Granada', 'telefono' => '958 23 45 67', 'email' => 'rrhh@cajagranada.es', 'responsable' => 'Elena Torres', 'dni' => '99887766E'],
        ];

        foreach ($empresasData as $e) {
            Empresa::firstOrCreate(
                ['cif' => $e['cif']],
                [
                    'nombre' => $e['nombre'],
                    'direccion' => $e['direccion'],
                    'telefono' => $e['telefono'],
                    'email' => $e['email'],
                    'responsable_nombre' => $e['responsable'],
                    'responsable_dni' => $e['dni'],
                    'is_active' => true,
                ]
            );
        }

        // --- Alumnos de ejemplo ---
        $nombres = [
            'Lucia', 'Pablo', 'Sara', 'Diego', 'Carmen', 'Hugo', 'Paula', 'Adrian',
            'Marta', 'Alex', 'Elena', 'Ivan', 'Claudia', 'Mario', 'Nerea', 'Javier',
            'Sofia', 'Daniel', 'Ana', 'Roberto', 'Laura', 'David', 'Mireia', 'Marc',
        ];
        $apellidos = ['Garcia', 'Martinez', 'Lopez', 'Fernandez', 'Rodriguez', 'Sanchez', 'Ramirez', 'Torres', 'Flores', 'Rivera', 'Gomez', 'Diaz'];

        $alumnos = [];
        foreach ($nombres as $i => $nombre) {
            $apellido1 = $apellidos[$i % count($apellidos)];
            $apellido2 = $apellidos[($i + 3) % count($apellidos)];
            $email = strtolower($nombre[0] . $apellido1 . $i . '@alumnoieshlanz.es');

            // Nombre con acentos reales
            $nombresConAcentos = [
                'Lucia' => 'Lucía', 'Pablo' => 'Pablo', 'Sara' => 'Sara', 'Diego' => 'Diego',
                'Carmen' => 'Carmen', 'Hugo' => 'Hugo', 'Paula' => 'Paula', 'Adrian' => 'Adrián',
                'Marta' => 'Marta', 'Alex' => 'Álex', 'Elena' => 'Elena', 'Ivan' => 'Iván',
                'Claudia' => 'Claudia', 'Mario' => 'Mario', 'Nerea' => 'Nerea', 'Javier' => 'Javier',
                'Sofia' => 'Sofía', 'Daniel' => 'Daniel', 'Ana' => 'Ana', 'Roberto' => 'Roberto',
                'Laura' => 'Laura', 'David' => 'David', 'Mireia' => 'Mireia', 'Marc' => 'Marc',
            ];
            $apellidosConAcentos = [
                'Garcia' => 'García', 'Martinez' => 'Martínez', 'Lopez' => 'López',
                'Fernandez' => 'Fernández', 'Rodriguez' => 'Rodríguez', 'Sanchez' => 'Sánchez',
                'Ramirez' => 'Ramírez', 'Torres' => 'Torres', 'Flores' => 'Flores',
                'Rivera' => 'Rivera', 'Gomez' => 'Gómez', 'Diaz' => 'Díaz',
            ];
            $nombreReal = $nombresConAcentos[$nombre] ?? $nombre;
            $apellido1Real = $apellidosConAcentos[$apellido1] ?? $apellido1;
            $apellido2Real = $apellidosConAcentos[$apellido2] ?? $apellido2;
            $nombreCompleto = "$nombreReal $apellido1Real $apellido2Real";

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $nombreCompleto,
                    'password' => Hash::make('password'),
                    'roles' => [User::ROLE_ALUMNO],
                    'is_active' => true,
                    'consent_rgpd' => true,
                    'consent_rgpd_at' => now(),
                ]
            );

            // Asignar a un grupo aleatorio
            $grupo = $grupos->random();
            $alumno = \App\Models\Alumno::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'linkedin_url' => null,
                    'telefono' => '6' . rand(10000000, 99999999),
                    'domicilio' => 'Calle Ejemplo, ' . rand(1, 100) . ', Granada',
                    'fecha_nacimiento' => '200' . rand(5, 8) . '-' . str_pad(rand(1, 12), 2, '0', STR_PAD_LEFT) . '-' . str_pad(rand(1, 28), 2, '0', STR_PAD_LEFT),
                ]
            );

            // Asignar a grupo (relación muchos a muchos)
            if ($grupo) {
                $alumno->grupos()->syncWithoutDetaching([$grupo->id]);
            }

            // Matricular en el ciclo del grupo
            if ($grupo && $grupo->linea && $grupo->linea->ciclo) {
                if (!\App\Models\AlumnoCicloMatricula::where('alumno_id', $alumno->id)->where('ciclo_id', $grupo->linea->ciclo->id)->exists()) {
                    \App\Models\AlumnoCicloMatricula::create([
                        'alumno_id' => $alumno->id,
                        'ciclo_id' => $grupo->linea->ciclo->id,
                        'curso_academico' => '2025-2026',
                        'matriculado_at' => now(),
                    ]);
                }
            }

            $alumnos[] = $alumno;
        }

        // --- Asignar convenios a empresas ---
        $empresas = Empresa::where('is_active', true)->get();
        foreach ($empresas as $empresa) {
            foreach ($ciclos as $ciclo) {
                $alumnosCiclo = \App\Models\AlumnoCicloMatricula::where('ciclo_id', $ciclo->id)
                    ->where('curso_academico', '2025-2026')
                    ->pluck('alumno_id');

                foreach ($alumnosCiclo as $alumnoId) {
                    if (!\App\Models\Convenio::where('empresa_id', $empresa->id)->where('alumno_id', $alumnoId)->exists()) {
                        $firmado = rand(0, 1) === 1;
                        \App\Models\Convenio::create([
                            'empresa_id' => $empresa->id,
                            'alumno_id' => $alumnoId,
                            'grupo_id' => \App\Models\Alumno::find($alumnoId)->grupos()->first()?->id,
                            'tutor_laboral_id' => $empresa->tutoresLaborales()->inRandomOrder()->first()?->id,
                            'tutor_docente_id' => $profesores[array_rand($profesores)]->id,
                            'numero_horas' => rand(200, 500),
                            'fecha_inicio' => now()->subMonths(3)->format('Y-m-d'),
                            'fecha_fin' => now()->addMonths(9)->format('Y-m-d'),
                            'estado' => $firmado ? 'firmado' : 'no_firmado',
                            'fecha_firma' => $firmado ? now()->format('Y-m-d') : null,
                        ]);
                    }
                }
            }
        }

        // --- Sustituciones de ejemplo ---
        $sustitucionesData = [
            [0, 2, 'Baja médica', 15, 20],
            [3, 5, 'Asignación a otro centro', 10, 15],
            [1, 4, 'Permiso por maternidad', 5, 30],
        ];

        foreach ($sustitucionesData as $s) {
            $titular = $profesores[$s[0]];
            $sustituto = $profesores[$s[1]];
            $diasInicio = rand(-30, 0);
            $diasFin = $diasInicio + rand(5, 45);

            \App\Models\Sustitucion::firstOrCreate(
                [
                    'profesor_titular_id' => $titular->id,
                    'profesor_sustituto_id' => $sustituto->id,
                    'fecha_inicio' => now()->addDays($diasInicio)->format('Y-m-d'),
                    'fecha_fin' => now()->addDays($diasFin)->format('Y-m-d'),
                ],
                [
                    'is_active' => true,
                ]
            );
        }

        // --- Calificaciones de ejemplo ---
        $evaluaciones = ['primera', 'segunda', 'tercera'];
        foreach ($alumnos as $alumno) {
            $asigsAleatorias = $asignaturas->random(rand(2, 5));
            foreach ($asigsAleatorias as $asig) {
                foreach ($evaluaciones as $eval) {
                    if (rand(0, 3) !== 0) { // 75% de probabilidad de tener nota
                        if (!\App\Models\Calificacion::where('alumno_id', $alumno->id)->where('asignatura_id', $asig->id)->where('evaluacion', $eval)->exists()) {
                            \App\Models\Calificacion::create([
                                'alumno_id' => $alumno->id,
                                'asignatura_id' => $asig->id,
                                'evaluacion' => $eval,
                                'nota' => round(rand(30, 100) / 10, 1),
                            ]);
                        }
                    }
                }
            }
        }
    }
}