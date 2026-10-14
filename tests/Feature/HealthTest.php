<?php

namespace Tests\Feature;

use Tests\TestCase;

final class HealthTest extends TestCase
{
    public function test_health_is_public(): void
    {
        $this->getJson('/health')->assertOk()->assertExactJson(['status' => 'ok']);
        $this->getJson('/api/v1/health')->assertOk()->assertExactJson(['status' => 'ok']);
    }
}
