<?php

namespace Tests\Feature;

use App\Models\Cambio;
use App\Models\User;
use App\Models\Alumno;
use App\Models\Convenio;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CambioHistorialTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_cambios_index()
    {
        $admin = User::factory()->admin()->create();
        Cambio::factory()->count(5)->create(['usuario_id' => $admin->id]);

        $response = $this->actingAs($admin)->get(route('admin.cambios.index'));

        $response->assertOk();
        $response->assertViewIs('admin.cambios.index');
        $response->assertViewHas('cambios');
    }

    public function test_admin_can_view_cambio_detail()
    {
        $admin = User::factory()->admin()->create();
        $cambio = Cambio::factory()->create(['usuario_id' => $admin->id]);

        $response = $this->actingAs($admin)->get(route('admin.cambios.show', $cambio));

        $response->assertOk();
        $response->assertViewIs('admin.cambios.show');
        $response->assertViewHas('cambio', $cambio);
    }

    public function test_cambios_can_be_filtered_by_usuario()
    {
        $admin1 = User::factory()->admin()->create(['name' => 'Admin Uno']);
        $admin2 = User::factory()->admin()->create(['name' => 'Admin Dos']);
        
        Cambio::factory()->count(3)->create(['usuario_id' => $admin1->id]);
        Cambio::factory()->count(2)->create(['usuario_id' => $admin2->id]);

        $response = $this->actingAs($admin1)->get(route('admin.cambios.index', ['usuario_id' => $admin1->id]));

        $response->assertOk();
        $response->assertSee('Admin Uno');
        // El select de usuarios puede mostrar todos los admins, solo verificamos que los cambios sean del admin filtrado
        $view = $response->assertViewIs('admin.cambios.index')->viewData('cambios');
        foreach ($view as $cambio) {
            $this->assertEquals($admin1->id, $cambio->usuario_id);
        }
    }

    public function test_cambios_can_be_filtered_by_accion()
    {
        $admin = User::factory()->admin()->create();
        Cambio::factory()->count(3)->create(['usuario_id' => $admin->id, 'accion' => 'created']);
        Cambio::factory()->count(2)->create(['usuario_id' => $admin->id, 'accion' => 'deleted']);

        $response = $this->actingAs($admin)->get(route('admin.cambios.index', ['accion' => 'created']));

        $response->assertOk();
        $response->assertSee('created');
    }

    public function test_cambios_can_be_searched()
    {
        $admin = User::factory()->admin()->create();
        Cambio::factory()->create([
            'usuario_id' => $admin->id,
            'descripcion' => 'Cambio importante en convenio',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.cambios.index', ['search' => 'convenio']));

        $response->assertOk();
        $response->assertSee('Cambio importante en convenio');
    }

    public function test_cambio_model_registers_created_event()
    {
        $admin = User::factory()->admin()->create();
        
        $alumno = Alumno::factory()->create(['user_id' => $admin->id]);

        $this->assertDatabaseHas('cambios', [
            'registrable_type' => Alumno::class,
            'registrable_id' => $alumno->id,
            'accion' => 'created',
        ]);
    }

    public function test_cambio_model_registers_updated_event()
    {
        $admin = User::factory()->admin()->create();
        
        $alumno = Alumno::factory()->create(['user_id' => $admin->id]);
        
        // Modificar el alumno
        $alumno->update(['telefono' => '666555444']);

        $this->assertDatabaseHas('cambios', [
            'registrable_type' => Alumno::class,
            'registrable_id' => $alumno->id,
            'accion' => 'updated',
            'campo' => 'telefono',
        ]);
    }

    public function test_cambio_model_registers_estado_change()
    {
        $convenio = Convenio::factory()->create(['estado' => 'no_firmado']);

        $convenio->update(['estado' => 'firmado']);

        $this->assertDatabaseHas('cambios', [
            'registrable_type' => Convenio::class,
            'registrable_id' => $convenio->id,
            'accion' => 'estado_cambiado',
            'campo' => 'estado',
        ]);
    }

    public function test_cambio_model_registers_deleted_event()
    {
        $admin = User::factory()->admin()->create();
        
        $alumno = Alumno::factory()->create(['user_id' => $admin->id]);
        $alumnoId = $alumno->id;
        
        $alumno->delete();

        $this->assertDatabaseHas('cambios', [
            'registrable_type' => Alumno::class,
            'registrable_id' => $alumnoId,
            'accion' => 'deleted',
        ]);
    }

    public function test_cambio_has_relationships()
    {
        $admin = User::factory()->admin()->create();
        $cambio = Cambio::factory()->create(['usuario_id' => $admin->id]);

        $this->assertInstanceOf(User::class, $cambio->usuario);
        $this->assertEquals($admin->id, $cambio->usuario->id);
    }

    public function test_cambio_static_methods()
    {
        $admin = User::factory()->admin()->create();
        $alumno = Alumno::factory()->create(['user_id' => $admin->id]);
        
        // Crear cambios manualmente usando el modelo directamente
        $cambio = Cambio::create([
            'registrable_type' => Alumno::class,
            'registrable_id' => $alumno->id,
            'accion' => 'updated',
            'campo' => 'email',
            'antes' => json_encode(['email' => 'old@test.com']),
            'despues' => json_encode(['email' => 'new@test.com']),
            'descripcion' => 'Email actualizado',
            'usuario_id' => $admin->id,
        ]);

        $cambios = Cambio::paraRegistrable($alumno);
        $this->assertGreaterThanOrEqual(1, $cambios->count());

        $todos = Cambio::conFiltros(usuarioId: $admin->id);
        $this->assertGreaterThanOrEqual(1, $todos->count());
    }

    public function test_non_admin_cannot_view_cambios()
    {
        $profesor = User::factory()->profesor()->create();

        $response = $this->actingAs($profesor)->get(route('admin.cambios.index'));

        $response->assertForbidden();
    }
}