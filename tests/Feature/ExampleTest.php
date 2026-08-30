<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        // La ruta raíz redirige a /login si no auth, a /dashboard si auth
        $response = $this->get('/');

        $response->assertRedirect();
    }
}