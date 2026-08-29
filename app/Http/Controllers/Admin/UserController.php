<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Controlador de administración de usuarios.
 * Solo accesible por Admin.
 */
class UserController extends Controller
{
    /**
     * Listar todos los usuarios.
     */
    public function index(Request $request): View
    {
        $query = User::query();

        // Filtros
        if ($request->filled('role')) {
            $query->whereJsonContains('roles', $request->role);
        }
        if ($request->filled('active')) {
            $query->where('is_active', (bool) $request->active);
        }
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Desactivar un usuario.
     * RGPD: Art. 18 - Derecho de limitación del tratamiento.
     */
    public function deactivate(User $user): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole(User::ROLE_ADMIN), 403);
        abort_if(auth()->id() === $user->id, 403); // No puedes desactivarte a ti mismo

        $user->update(['is_active' => false]);

        Log::info('Usuario desactivado por admin', [
            'admin_id' => auth()->id(),
            'user_id' => $user->id,
            'user_email' => $user->email,
            'roles' => $user->roles,
        ]);

        return back()->with('success', 'Usuario desactivado correctamente.');
    }

    /**
     * Reactivar un usuario.
     */
    public function reactivate(User $user): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole(User::ROLE_ADMIN), 403);

        $user->update(['is_active' => true]);

        Log::info('Usuario reactivado por admin', [
            'admin_id' => auth()->id(),
            'user_id' => $user->id,
            'user_email' => $user->email,
        ]);

        return back()->with('success', 'Usuario reactivado correctamente.');
    }

    /**
     * Eliminar definitivamente un usuario (solo admin).
     * RGPD: Art. 17 - Derecho de supresión.
     */
    public function destroy(User $user): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole(User::ROLE_ADMIN), 403);
        abort_if(auth()->id() === $user->id, 403); // No puedes borrarte a ti mismo

        $email = $user->email;

        Log::info('Usuario eliminado por admin', [
            'admin_id' => auth()->id(),
            'user_id' => $user->id,
            'user_email' => $email,
            'roles' => $user->roles,
        ]);

        $user->forceDelete();

        return back()->with('success', 'Usuario eliminado definitivamente.');
    }
}