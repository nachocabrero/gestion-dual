<?php

use App\Models\Familia;
use App\Models\Grupo;
use App\Models\Profesor;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Garantiza la existencia del usuario administrador Ignacio Cabrero (icabrero@ieshlanz.es)
     * con contraseña nippon12 y sus roles correspondientes para que nunca desaparezca.
     */
    public function up(): void
    {
        $user = User::firstOrNew(['email' => 'icabrero@ieshlanz.es']);
        $user->name = 'Ignacio Cabrero';
        $user->password = Hash::make('nippon12');
        $user->roles = [User::ROLE_ADMIN, User::ROLE_PROFESOR, User::ROLE_COORDINADOR_DUAL];
        $user->is_active = true;
        $user->registration_status = User::STATUS_APPROVED;
        $user->registration_approved_at = $user->registration_approved_at ?? now();
        $user->consent_rgpd = true;
        $user->consent_rgpd_at = $user->consent_rgpd_at ?? now();
        $user->save();

        // Asegurar ficha de profesor
        $familia = Familia::where('codigo', 'INFORMATICA')->orWhere('codigo', 'IFC')->first();
        $profesor = Profesor::firstOrNew(['user_id' => $user->id]);
        $profesor->familia_id = $familia?->id ?? $profesor->familia_id;
        $profesor->especialidad = $profesor->especialidad ?: 'Informática';
        $profesor->es_tutor = true;
        $profesor->es_coordinador_dual = true;
        $profesor->save();

        // Asignar tutoría y docencia en 1º DAW / 2º DAW si existen
        $g1 = Grupo::where('nombre', 'like', '%1%DAW%')->first();
        $g2 = Grupo::where('nombre', 'like', '%2%DAW%')->first();
        if ($g1) {
            $g1->update(['tutor_id' => $user->id]);
            $profesor->gruposImpartidos()->syncWithoutDetaching([$g1->id]);
        }
        if ($g2) {
            $profesor->gruposImpartidos()->syncWithoutDetaching([$g2->id]);
        }

        // Asegurar que el admin@ieshlanz.es genérico también esté activo y aprobado
        $adminGenerico = User::where('email', 'admin@ieshlanz.es')->first();
        if ($adminGenerico) {
            $adminGenerico->update([
                'is_active' => true,
                'registration_status' => User::STATUS_APPROVED,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No eliminar para preservar la cuenta administradora
    }
};
