<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }

    public function test_user_can_upload_avatar_and_cv_together(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Updated User',
                'email' => $user->email,
                'avatar' => \Illuminate\Http\UploadedFile::fake()->image('avatar.jpg'),
                'cv' => \Illuminate\Http\UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/profile');

        $user->refresh();
        $this->assertNotNull($user->avatar_url);
        $this->assertNotNull($user->cv_url);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($user->avatar_url);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($user->cv_url);
    }

    public function test_user_can_upload_avatar_and_image_cv_together(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Updated User Image CV',
                'email' => $user->email,
                'avatar' => \Illuminate\Http\UploadedFile::fake()->image('avatar.png'),
                'cv' => \Illuminate\Http\UploadedFile::fake()->image('cv_document.jpg'),
                'portfolio_url' => 'https://miportfolio.dev',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/profile');

        $user->refresh();
        $this->assertNotNull($user->avatar_url);
        $this->assertNotNull($user->cv_url);
        $this->assertSame('https://miportfolio.dev', $user->portfolio_url);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($user->avatar_url);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($user->cv_url);
    }

    public function test_empty_portfolio_url_is_converted_to_null(): void
    {
        $user = User::factory()->create(['portfolio_url' => 'https://previous.dev']);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'User Without Portfolio',
                'email' => $user->email,
                'portfolio_url' => 'https://',
            ]);

        $response->assertSessionHasNoErrors();
        $user->refresh();
        $this->assertNull($user->portfolio_url);
    }

    public function test_portfolio_is_hidden_from_user_card_when_blank(): void
    {
        $user = User::factory()->create([
            'roles' => [User::ROLE_ALUMNO],
            'portfolio_url' => null,
            'is_active' => true,
        ]);
        $alumno = Alumno::create(['user_id' => $user->id, 'portfolio_url' => null]);

        $response = $this
            ->actingAs($user)
            ->get(route('alumnos.show', $alumno));

        $response->assertOk();
        $response->assertDontSee('Portfolio Externo / Web Personal');

        // Si tiene portfolio, sí debe mostrarse
        $user->update(['portfolio_url' => 'https://mi-portfolio-personal.com']);
        $alumno->refresh();

        $responseWithPortfolio = $this
            ->actingAs($user)
            ->get(route('alumnos.show', $alumno));

        $responseWithPortfolio->assertOk();
        $responseWithPortfolio->assertSee('Portfolio Externo / Web Personal');
        $responseWithPortfolio->assertSee('https://mi-portfolio-personal.com');
    }

    public function test_user_can_upload_webp_avatar(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Webp User',
                'email' => $user->email,
                'avatar' => \Illuminate\Http\UploadedFile::fake()->create('avatar.webp', 600, 'image/webp'),
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/profile');

        $user->refresh();
        $this->assertNotNull($user->avatar_url);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($user->avatar_url);
    }

    public function test_avatar_over_2mb_is_rejected(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Large Avatar User',
                'email' => $user->email,
                'avatar' => \Illuminate\Http\UploadedFile::fake()->create('avatar_large.jpg', 2500, 'image/jpeg'),
            ]);

        $response->assertSessionHasErrors('avatar');
        $this->assertNull($user->fresh()->avatar_url);
    }

    public function test_avatar_under_2mb_is_accepted(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $user = User::factory()->create();

        // 1500 KB (1.5 MB) > 500 KB y < 2048 KB
        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Medium Avatar User',
                'email' => $user->email,
                'avatar' => \Illuminate\Http\UploadedFile::fake()->image('avatar_1500k.jpg')->size(1500),
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/profile');

        $user->refresh();
        $this->assertNotNull($user->avatar_url);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($user->avatar_url);
    }
}
