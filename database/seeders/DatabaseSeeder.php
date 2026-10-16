<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(AcademicStructureSeeder::class);
        $this->call(IesDataSeeder::class);

        // Admin por defecto
        User::firstOrCreate(
            ['email' => 'admin@ieshlanz.es'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password'),
                'roles' => [User::ROLE_ADMIN, User::ROLE_PROFESOR, User::ROLE_COORDINADOR_DUAL],
                'is_active' => true,
                'registration_status' => User::STATUS_APPROVED,
                'consent_rgpd' => true,
                'consent_rgpd_at' => now(),
            ]
        );

        // Administrador Ignacio Cabrero
        $userCabrero = User::firstOrCreate(
            ['email' => 'icabrero@ieshlanz.es'],
            [
                'name' => 'Ignacio Cabrero',
                'password' => Hash::make('nippon12'),
                'roles' => [User::ROLE_ADMIN, User::ROLE_PROFESOR, User::ROLE_COORDINADOR_DUAL],
                'is_active' => true,
                'registration_status' => User::STATUS_APPROVED,
                'registration_approved_at' => now(),
                'consent_rgpd' => true,
                'consent_rgpd_at' => now(),
            ]
        );
        $userCabrero->update([
            'password' => Hash::make('nippon12'),
            'roles' => [User::ROLE_ADMIN, User::ROLE_PROFESOR, User::ROLE_COORDINADOR_DUAL],
            'is_active' => true,
            'registration_status' => User::STATUS_APPROVED,
        ]);

        $familiaInf = \App\Models\Familia::where('codigo', 'INFORMATICA')->orWhere('codigo', 'IFC')->first();
        $profesorCabrero = \App\Models\Profesor::firstOrCreate(
            ['user_id' => $userCabrero->id],
            [
                'familia_id' => $familiaInf?->id,
                'especialidad' => 'Informática',
                'es_tutor' => true,
                'es_coordinador_dual' => true,
            ]
        );

        $gDaw1 = \App\Models\Grupo::where('nombre', 'like', '%1%DAW%')->first();
        $gDaw2 = \App\Models\Grupo::where('nombre', 'like', '%2%DAW%')->first();
        if ($gDaw1) {
            $gDaw1->update(['tutor_id' => $userCabrero->id]);
            $profesorCabrero->gruposImpartidos()->syncWithoutDetaching([$gDaw1->id]);
        }
        if ($gDaw2) {
            $profesorCabrero->gruposImpartidos()->syncWithoutDetaching([$gDaw2->id]);
        }

        // Profesor de prueba
        User::firstOrCreate(
            ['email' => 'profesor@ieshlanz.es'],
            [
                'name' => 'Profesor de Prueba',
                'password' => Hash::make('password'),
                'roles' => [User::ROLE_PROFESOR, User::ROLE_COORDINADOR_DUAL],
                'is_active' => true,
                'registration_status' => User::STATUS_APPROVED,
                'consent_rgpd' => true,
                'consent_rgpd_at' => now(),
            ]
        );

        // Alumno de prueba
        User::firstOrCreate(
            ['email' => 'alumno@test.com'],
            [
                'name' => 'Alumno de Prueba',
                'password' => Hash::make('password'),
                'roles' => [User::ROLE_ALUMNO],
                'is_active' => true,
                'registration_status' => User::STATUS_APPROVED,
                'consent_rgpd' => true,
                'consent_rgpd_at' => now(),
            ]
        );
    }
}