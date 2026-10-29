<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page()
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_administrators_are_redirected_from_legacy_dashboard_url()
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('admin.dashboard'));
    }
}
