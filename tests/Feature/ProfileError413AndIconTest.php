<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class ProfileError413AndIconTest extends TestCase
{
    /**
     * Verificar que el layout app y el dashboard cargan correctamente con el icono de proyectos actualizado.
     */
    public function test_proyectos_module_link_renders_in_sidebar_and_dashboard(): void
    {
        $user = User::factory()->create([
            'roles' => [User::ROLE_ADMIN],
            'is_active' => true,
            'registration_status' => User::STATUS_APPROVED,
        ]);

        $this->actingAs($user);

        // Sidebar en app layout
        $response = $this->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee('Módulo de Proyectos (2º)');
        $response->assertSee('Repositorio de Proyectos');
        // Verificar que no está la ruta del puzzle antiguo (M19.428)
        $response->assertDontSee('M19.428 15.428');
    }

    /**
     * Verificar que el formulario de edición de perfil carga correctamente con las instrucciones de tamaño y formato.
     */
    public function test_profile_edit_page_renders_with_upload_limits_and_format_information(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
            'registration_status' => User::STATUS_APPROVED,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('profile.edit'));
        $response->assertOk();
        $response->assertSee('2 MB');
        $response->assertSee('5 MB');
        $response->assertSee('JPG, PNG, WebP');
        $response->assertSee('PDF, JPG, PNG, WebP');
        $response->assertSee('profileEditForm()', false);
    }

    /**
     * Verificar que PostTooLargeException devuelve JSON con código 413 y explicación detallada en peticiones AJAX/JSON.
     */
    public function test_post_too_large_exception_returns_413_json_with_informative_explanation(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
            'registration_status' => User::STATUS_APPROVED,
        ]);

        $this->actingAs($user);

        // Simular excepción PostTooLargeException en el Exception Handler
        $exception = new PostTooLargeException();
        $handler = app(\App\Exceptions\Handler::class);

        $request = \Illuminate\Http\Request::create(route('profile.update'), 'POST');
        $request->headers->set('Accept', 'application/json');
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $response = $handler->render($request, $exception);

        $this->assertEquals(413, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $data);
        $this->assertStringContainsString('Error 413', $data['message']);
        $this->assertStringContainsString('2 MB', $data['message']);
        $this->assertStringContainsString('5 MB', $data['message']);
        $this->assertArrayHasKey('details', $data);
    }

    /**
     * Verificar que PostTooLargeException redirige de vuelta con aviso informativo en peticiones normales de formulario.
     */
    public function test_post_too_large_exception_redirects_back_with_flash_explanation(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
            'registration_status' => User::STATUS_APPROVED,
        ]);

        $this->actingAs($user);

        $exception = new PostTooLargeException();
        $handler = app(\App\Exceptions\Handler::class);

        $request = \Illuminate\Http\Request::create(route('profile.update'), 'POST');
        $request->setLaravelSession(app('session.store'));

        $response = $handler->render($request, $exception);

        $this->assertTrue($response->isRedirection());
        $this->assertArrayHasKey('error_413', session()->all());
        $this->assertStringContainsString('Error 413', session('error_413'));
    }

    /**
     * Verificar que la vista dedicada de error 413 existe y contiene toda la información de límites y formatos.
     */
    public function test_error_413_view_renders_informative_content(): void
    {
        $view = view('errors.413')->render();

        $this->assertStringContainsString('413', $view);
        $this->assertStringContainsString('Payload Too Large', $view);
        $this->assertStringContainsString('2 MB', $view);
        $this->assertStringContainsString('JPG, PNG', $view);
        $this->assertStringContainsString('PDF', $view);
        $this->assertStringContainsString('Volver a Editar Mi Perfil', $view);
    }

    /**
     * Verificar que ProfileUpdateRequest rechaza archivos que superen los tamaños máximos o formatos no válidos.
     */
    public function test_profile_update_validates_file_sizes_and_formats(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'is_active' => true,
            'registration_status' => User::STATUS_APPROVED,
        ]);

        $this->actingAs($user);

        // Avatar demasiado grande (> 2MB = 2048 KB)
        $oversizedAvatar = UploadedFile::fake()->create('avatar_large.jpg', 3000, 'image/jpeg');

        $response = $this->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $oversizedAvatar,
        ]);

        $response->assertSessionHasErrors('avatar');
        $this->assertStringContainsString('2 MB', session('errors')->first('avatar'));

        // Formato de avatar no permitido (ej. txt o gif)
        $invalidFormatAvatar = UploadedFile::fake()->create('avatar.gif', 500, 'image/gif');

        $response2 = $this->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $invalidFormatAvatar,
        ]);

        $response2->assertSessionHasErrors('avatar');
        $this->assertStringContainsString('formato', session('errors')->first('avatar'));
    }

    /**
     * Verificar que actualizar datos como empresa y horas de 1º sin subir archivos responde 200 JSON por AJAX sin error 413.
     */
    public function test_profile_update_without_files_returns_json_success_when_requested_via_ajax(): void
    {
        $user = User::factory()->create([
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => true,
            'registration_status' => User::STATUS_APPROVED,
        ]);

        $alumno = \App\Models\Alumno::create([
            'user_id' => $user->id,
            'empresa_practicas_primero' => null,
            'horas_practicas_primero' => null,
        ]);

        $this->actingAs($user);

        $response = $this->patchJson(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'empresa_practicas_primero' => 'NTT Data Granada',
            'horas_practicas_primero' => 120,
        ]);

        $response->assertOk();
        $response->assertJson([
            'status' => 'profile-updated',
        ]);

        $alumno->refresh();
        $this->assertEquals('NTT Data Granada', $alumno->empresa_practicas_primero);
        $this->assertEquals(120, $alumno->horas_practicas_primero);
    }
}

