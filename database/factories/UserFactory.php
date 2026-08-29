<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'roles' => [fake()->randomElement(['alumno', 'profesor', 'empresa'])],
            'is_active' => true,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate the user is an admin.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'roles' => ['admin'],
        ]);
    }

    /**
     * Indicate the user is a professor.
     */
    public function profesor(): static
    {
        return $this->state(fn (array $attributes) => [
            'roles' => ['profesor'],
        ]);
    }

    /**
     * Indicate the user is a student.
     */
    public function alumno(): static
    {
        return $this->state(fn (array $attributes) => [
            'roles' => ['alumno'],
        ]);
    }

    /**
     * Indicate the user has not consented RGPD.
     */
    public function withoutRgpdConsent(): static
    {
        return $this->state(fn (array $attributes) => [
            'consent_rgpd' => false,
            'consent_rgpd_at' => null,
        ]);
    }

    /**
     * Indicate the user is deactivated.
     */
    public function deactivated(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}