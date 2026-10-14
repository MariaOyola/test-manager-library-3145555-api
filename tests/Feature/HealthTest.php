<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\TestCase;

class HealthTest extends TestCase
{
    public function test_health_endpoint_returns_ok(): void
    {
        $this->get('/health')
            ->assertOk()
            ->assertExactJson(['status' => 'ok']);
    }

    public function createApplication()
    {
        return require __DIR__.'/../../bootstrap/app.php';
    }
}
