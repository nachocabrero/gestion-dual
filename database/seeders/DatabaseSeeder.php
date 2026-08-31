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
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@ieshlanz.es',
            'password' => Hash::make('password'),
            'roles' => [User::ROLE_ADMIN, User::ROLE_PROFESOR, User::ROLE_COORDINADOR_DUAL],
            'is_active' => true,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);

        // Profesor de prueba
        User::create([
            'name' => 'Profesor de Prueba',
            'email' => 'profesor@ieshlanz.es',
            'password' => Hash::make('password'),
            'roles' => [User::ROLE_PROFESOR, User::ROLE_COORDINADOR_DUAL],
            'is_active' => true,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);

        // Alumno de prueba
        User::create([
            'name' => 'Alumno de Prueba',
            'email' => 'alumno@test.com',
            'password' => Hash::make('password'),
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => true,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);
    }
}