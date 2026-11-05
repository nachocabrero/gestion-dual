<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Ciclo;
use App\Models\CursoAcademico;
use App\Models\Empresa;
use App\Models\Grupo;
use App\Models\Linea;
use App\Models\OfertaPractica;
use App\Models\SolicitudPractica;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class CvDownloadNombreTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_cv_download_filename_attribute_en_modelo_alumno(): void
    {
        $user = User::factory()->create([
            'name' => 'María José / Gómez: García',
            'roles' => [User::ROLE_ALUMNO],
            'cv_url' => 'cvs/maria_cv.pdf',
        ]);
        $alumno = Alumno::factory()->create(['user_id' => $user->id]);

        $this->assertEquals('CV IESHLANZ - María José Gómez García.pdf', $alumno->cv_download_filename);
    }

    public function test_descarga_cv_desde_alumnos_cv_route(): void
    {
        $admin = User::factory()->create(['roles' => [User::ROLE_ADMIN], 'consent_rgpd' => true]);
        $userAlumno = User::factory()->create([
            'name' => 'Antonio Lopez',
            'roles' => [User::ROLE_ALUMNO],
            'cv_url' => 'cvs/antonio_cv.pdf',
            'consent_rgpd' => true,
        ]);
        Storage::disk('public')->put('cvs/antonio_cv.pdf', 'Contenido del CV de Antonio');
        $alumno = Alumno::factory()->create(['user_id' => $userAlumno->id]);

        $response = $this->actingAs($admin)->get(route('alumnos.cv', $alumno));

        $response->assertOk();
        $this->assertStringContainsString('attachment;', $response->headers->get('content-disposition'));
        $this->assertStringContainsString('CV IESHLANZ - Antonio Lopez.pdf', $response->headers->get('content-disposition'));
    }

    public function test_descarga_cv_falla_con_aviso_si_no_hay_cv(): void
    {
        $admin = User::factory()->create(['roles' => [User::ROLE_ADMIN], 'consent_rgpd' => true]);
        $userAlumno = User::factory()->create([
            'name' => 'Alumno Sin CV',
            'roles' => [User::ROLE_ALUMNO],
            'cv_url' => null,
            'consent_rgpd' => true,
        ]);
        $alumno = Alumno::factory()->create(['user_id' => $userAlumno->id]);

        $response = $this->actingAs($admin)->get(route('alumnos.cv', $alumno));

        $response->assertRedirect();
        $response->assertSessionHas('warning');
    }

    public function test_descarga_cv_desde_oferta_practica(): void
    {
        $profesor = User::factory()->create(['roles' => [User::ROLE_PROFESOR], 'consent_rgpd' => true]);
        $oferta = OfertaPractica::factory()->create([
            'creador_id' => $profesor->id,
            'creador_type' => User::class,
            'especialidad_requerida' => 'Desarrollo Web',
        ]);

        $userAlumno = User::factory()->create([
            'name' => 'Lucia Fernandez',
            'roles' => [User::ROLE_ALUMNO],
            'cv_url' => 'cvs/lucia.pdf',
            'consent_rgpd' => true,
        ]);
        Storage::disk('public')->put('cvs/lucia.pdf', 'PDF de Lucia');
        $alumno = Alumno::factory()->create(['user_id' => $userAlumno->id]);
        SolicitudPractica::factory()->create(['oferta_id' => $oferta->id, 'alumno_id' => $alumno->id]);

        $response = $this->actingAs($profesor)->get(route('ofertas.descargar-cv-alumno', [$oferta, $alumno]));

        $response->assertOk();
        $this->assertStringContainsString('attachment;', $response->headers->get('content-disposition'));
        $this->assertStringContainsString('CV IESHLANZ - Lucia Fernandez.pdf', $response->headers->get('content-disposition'));
    }

    public function test_descarga_cv_desde_oferta_compartida_token_para_empresa(): void
    {
        $oferta = OfertaPractica::factory()->create(['especialidad_requerida' => 'Sistemas']);

        $userAlumno = User::factory()->create([
            'name' => 'Marcos Ramos',
            'roles' => [User::ROLE_ALUMNO],
            'cv_url' => 'cvs/marcos.pdf',
            'consent_rgpd' => true,
        ]);
        Storage::disk('public')->put('cvs/marcos.pdf', 'PDF de Marcos');
        $alumno = Alumno::factory()->create(['user_id' => $userAlumno->id]);
        SolicitudPractica::factory()->create(['oferta_id' => $oferta->id, 'alumno_id' => $alumno->id]);

        $response = $this->get(route('ofertas.compartida.descargar-cv', ['token' => $oferta->token, 'alumno' => $alumno->id]));

        $response->assertOk();
        $this->assertStringContainsString('attachment;', $response->headers->get('content-disposition'));
        $this->assertStringContainsString('CV IESHLANZ - Marcos Ramos.pdf', $response->headers->get('content-disposition'));
    }

    public function test_descarga_zip_cvs_contiene_archivos_con_formato_normalizado(): void
    {
        $profesor = User::factory()->create(['roles' => [User::ROLE_PROFESOR], 'consent_rgpd' => true]);
        $oferta = OfertaPractica::factory()->create([
            'creador_id' => $profesor->id,
            'creador_type' => User::class,
            'especialidad_requerida' => 'DAM',
        ]);

        $u1 = User::factory()->create(['name' => 'Beatriz Santos', 'roles' => [User::ROLE_ALUMNO], 'cv_url' => 'cvs/b.pdf', 'consent_rgpd' => true]);
        $u2 = User::factory()->create(['name' => 'Carlos Perez', 'roles' => [User::ROLE_ALUMNO], 'cv_url' => 'cvs/c.pdf', 'consent_rgpd' => true]);
        Storage::disk('public')->put('cvs/b.pdf', 'PDF Beatriz');
        Storage::disk('public')->put('cvs/c.pdf', 'PDF Carlos');

        $a1 = Alumno::factory()->create(['user_id' => $u1->id]);
        $a2 = Alumno::factory()->create(['user_id' => $u2->id]);
        SolicitudPractica::factory()->create(['oferta_id' => $oferta->id, 'alumno_id' => $a1->id]);
        SolicitudPractica::factory()->create(['oferta_id' => $oferta->id, 'alumno_id' => $a2->id]);

        $response = $this->actingAs($profesor)->get(route('ofertas.descargar-cvs', $oferta));

        $response->assertOk();
        $this->assertEquals('application/zip', $response->headers->get('content-type'));

        $zip = new ZipArchive();
        $zip->open($response->getFile()->getPathname());
        $this->assertNotFalse($zip->locateName('CV IESHLANZ - Beatriz Santos.pdf'));
        $this->assertNotFalse($zip->locateName('CV IESHLANZ - Carlos Perez.pdf'));
        $zip->close();
    }

    public function test_alumno_puede_descargar_su_propio_cv_pero_no_el_de_otro(): void
    {
        $user1 = User::factory()->create(['name' => 'Alumno Uno', 'roles' => [User::ROLE_ALUMNO], 'cv_url' => 'cvs/uno.pdf', 'consent_rgpd' => true]);
        $user2 = User::factory()->create(['name' => 'Alumno Dos', 'roles' => [User::ROLE_ALUMNO], 'cv_url' => 'cvs/dos.pdf', 'consent_rgpd' => true]);
        Storage::disk('public')->put('cvs/uno.pdf', 'Uno');
        Storage::disk('public')->put('cvs/dos.pdf', 'Dos');

        $alumno1 = Alumno::factory()->create(['user_id' => $user1->id]);
        $alumno2 = Alumno::factory()->create(['user_id' => $user2->id]);

        // Alumno 1 puede descargar su propio CV
        $responsePropio = $this->actingAs($user1)->get(route('alumnos.cv', $alumno1));
        $responsePropio->assertOk();
        $this->assertStringContainsString('CV IESHLANZ - Alumno Uno.pdf', $responsePropio->headers->get('content-disposition'));

        // Alumno 1 NO puede descargar el CV de Alumno 2
        $responseAjeno = $this->actingAs($user1)->get(route('alumnos.cv', $alumno2));
        $responseAjeno->assertForbidden();
    }
}
