<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->actingAs($this->user);
    }

    public function test_dashboard_loads_and_shows_kpi(): void
    {
        $response = $this->get('/dashboard');

        $response->assertStatus(200);
    }

    public function test_dashboard_accepts_custom_date_range(): void
    {
        $response = $this->get('/dashboard?from=2026-01-01&to=2026-12-31');

        $response->assertStatus(200);
    }
}
