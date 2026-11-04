<?php

namespace App\Http\Controllers;

use App\Models\ComunicacionEmpresa;
use App\Models\CursoAcademico;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ComunicacionEmpresaController extends Controller
{
    /**
     * Verificar permisos para gestionar registros de comunicación.
     * Solo el profesor responsable del seguimiento de la empresa,
     * el coordinador dual o el administrador tienen permiso.
     */
    private function authorizeManage(Empresa $empresa, ?ComunicacionEmpresa $comunicacion = null): void
    {
        $user = auth()->user();

        if ($user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_COORDINADOR_DUAL])) {
            return;
        }

        $profesor = $user->profesor;
        if ($profesor) {
            if ($empresa->profesor_id === $profesor->id) {
                return;
            }
            if ($comunicacion && $comunicacion->profesor_id === $profesor->id) {
                return;
            }
        }

        abort(403, 'Solo el profesor responsable del seguimiento de la empresa o el administrador pueden gestionar este registro de comunicación.');
    }

    /**
     * Registrar una nueva comunicación con la empresa.
     */
    public function store(Request $request, Empresa $empresa): RedirectResponse
    {
        $this->authorizeManage($empresa);

        $validated = $request->validate([
            'tipo' => ['required', 'string', 'in:telefono,email,presencial'],
            'motivo' => ['required', 'string', 'max:5000'],
            'respuesta' => ['nullable', 'string', 'max:5000'],
        ], [
            'tipo.required' => 'Debes indicar el canal de comunicación (teléfono, email o visita presencial).',
            'tipo.in' => 'El canal de comunicación seleccionado no es válido.',
            'motivo.required' => 'El motivo de la comunicación es obligatorio.',
        ]);

        $cursoActual = CursoAcademico::active()->orderBy('fecha_inicio', 'desc')->first();

        ComunicacionEmpresa::create([
            'empresa_id' => $empresa->id,
            'profesor_id' => auth()->user()->profesor?->id ?? $empresa->profesor_id,
            'user_id' => auth()->id(),
            'curso_academico_id' => $cursoActual?->id,
            'tipo' => $validated['tipo'],
            'motivo' => $validated['motivo'],
            'respuesta' => $validated['respuesta'] ?? null,
            'fecha' => now(),
        ]);

        return redirect()->back(fallback: route('empresas.show', $empresa))
            ->with('success', 'Registro de comunicación guardado correctamente.');
    }

    /**
     * Actualizar el contenido de un registro de comunicación.
     */
    public function update(Request $request, Empresa $empresa, ComunicacionEmpresa $comunicacion): RedirectResponse
    {
        abort_if($comunicacion->empresa_id !== $empresa->id, 404);

        $this->authorizeManage($empresa, $comunicacion);

        $validated = $request->validate([
            'tipo' => ['required', 'string', 'in:telefono,email,presencial'],
            'motivo' => ['required', 'string', 'max:5000'],
            'respuesta' => ['nullable', 'string', 'max:5000'],
        ], [
            'tipo.required' => 'Debes indicar el canal de comunicación (teléfono, email o visita presencial).',
            'tipo.in' => 'El canal de comunicación seleccionado no es válido.',
            'motivo.required' => 'El motivo de la comunicación es obligatorio.',
        ]);

        $comunicacion->update([
            'tipo' => $validated['tipo'],
            'motivo' => $validated['motivo'],
            'respuesta' => $validated['respuesta'] ?? null,
        ]);

        return redirect()->back(fallback: route('empresas.show', $empresa))
            ->with('success', 'Registro de comunicación actualizado correctamente.');
    }

    /**
     * Eliminar un registro de comunicación.
     */
    public function destroy(Empresa $empresa, ComunicacionEmpresa $comunicacion): RedirectResponse
    {
        abort_if($comunicacion->empresa_id !== $empresa->id, 404);

        $this->authorizeManage($empresa, $comunicacion);

        $comunicacion->delete();

        return redirect()->back(fallback: route('empresas.show', $empresa))
            ->with('success', 'Registro de comunicación eliminado correctamente.');
    }
}

