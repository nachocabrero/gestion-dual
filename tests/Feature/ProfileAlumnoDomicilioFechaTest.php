<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\User;
use Tests\TestCase;

class ProfileAlumnoDomicilioFechaTest extends TestCase
{
    /**
     * El alumno ve los campos de fecha de nacimiento y domicilio en el formulario de perfil.
     */
    public function test_alumno_sees_fecha_nacimiento_and_domicilio_fields_in_profile_edit(): void
    {
        $user = User::factory()->create([
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => true,
        ]);

        $alumno = Alumno::create([
            'user_id' => $user->id,
            'domicilio' => 'Calle Recogidas 10, Granada',
            'fecha_nacimiento' => '2004-05-15',
        ]);

        $response = $this->actingAs($user)->get(route('profile.edit'));

        $response->assertOk();
        $response->assertSee('Fecha de nacimiento (Opcional)');
        $response->assertSee('Domicilio habitual (Opcional)');
        $response->assertSee('name="fecha_nacimiento"', false);
        $response->assertSee('name="domicilio"', false);
        $response->assertSee('value="2004-05-15"', false);
        $response->assertSee('value="Calle Recogidas 10, Granada"', false);
    }

    /**
     * El alumno puede actualizar su domicilio y fecha de nacimiento.
     */
    public function test_alumno_can_update_domicilio_and_fecha_nacimiento(): void
    {
        $user = User::factory()->create([
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => true,
        ]);

        $alumno = Alumno::create([
            'user_id' => $user->id,
            'domicilio' => 'Antigua dirección 1',
            'fecha_nacimiento' => '2003-01-01',
        ]);

        $response = $this->actingAs($user)->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'domicilio' => 'Calle Nueva Esperanza 25, 2ºA, 18003 Granada',
            'fecha_nacimiento' => '2004-08-20',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('profile.edit'));

        $alumno->refresh();
        $this->assertSame('Calle Nueva Esperanza 25, 2ºA, 18003 Granada', $alumno->domicilio);
        $this->assertSame('2004-08-20', $alumno->fecha_nacimiento?->format('Y-m-d'));
    }

    /**
     * Los campos domicilio y fecha de nacimiento son opcionales y pueden dejarse en blanco.
     */
    public function test_domicilio_and_fecha_nacimiento_are_optional_and_can_be_cleared(): void
    {
        $user = User::factory()->create([
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => true,
        ]);

        $alumno = Alumno::create([
            'user_id' => $user->id,
            'domicilio' => 'Calle Existente 1',
            'fecha_nacimiento' => '2002-12-12',
        ]);

        $response = $this->actingAs($user)->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'domicilio' => '',
            'fecha_nacimiento' => '',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('profile.edit'));

        $alumno->refresh();
        $this->assertNull($alumno->domicilio);
        $this->assertNull($alumno->fecha_nacimiento);
    }
}
