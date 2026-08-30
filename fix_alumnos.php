<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = \App\Models\User::whereJsonContains('roles', 'alumno')->doesntHave('alumno')->get();
foreach ($users as $user) {
    \App\Models\Alumno::create(['user_id' => $user->id]);
    echo "Creado perfil Alumno para user ID: " . $user->id . "\n";
}
echo "Fix aplicado a " . $users->count() . " usuarios.\n";
