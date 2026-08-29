<?php
namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LangTest extends TestCase
{
    public function test_translation(): void
    {
        $result = __('Nuevo Proyecto');
        echo "\nRESULT: [" . $result . "]";
        $this->assertTrue(true);
    }
}
