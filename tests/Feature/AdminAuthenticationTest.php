<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_registration_is_disabled(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register', [
            'name' => 'Cliente',
            'email' => 'cliente@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertNotFound();

        $this->assertDatabaseEmpty('users');
    }

    public function test_administrator_can_log_in_and_is_redirected_to_dashboard(): void
    {
        $admin = User::factory()->admin()->create([
            'password' => 'secure-password',
        ]);

        $response = $this->post(route('login.store'), [
            'email' => $admin->email,
            'password' => 'secure-password',
        ]);

        $this->assertAuthenticatedAs($admin);
        $response->assertRedirect(route('admin.dashboard', absolute: false));
    }

    public function test_guest_is_redirected_to_login_from_admin_dashboard(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_authenticated_non_admin_user_receives_forbidden_response(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_administrator_can_view_dashboard_with_database_statistics(): void
    {
        $admin = User::factory()->admin()->create();
        Category::query()->create([
            'name' => 'Soins',
            'slug' => 'soins',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/Dashboard')
                ->where('statistics.products', 0)
                ->where('statistics.categories', 1)
                ->where('statistics.orders', 0)
                ->where('statistics.pendingOrders', 0)
            );
    }

    public function test_administrator_can_open_the_protected_admin_modules(): void
    {
        $admin = User::factory()->admin()->create();
        $modules = [
            'admin.categories.index' => 'admin/categories/Index',
            'admin.product-types.index' => 'admin/product-types/Index',
            'admin.products.index' => 'admin/products/Index',
        ];

        foreach ($modules as $routeName => $component) {
            $this->actingAs($admin)
                ->get(route($routeName))
                ->assertOk()
                ->assertInertia(fn (Assert $page) => $page
                    ->component($component)
                );
        }

        $this->actingAs($admin)
            ->get(route('admin.orders'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/orders/Index')
            );
    }

    public function test_administrator_can_log_out(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('logout'));

        $response->assertRedirect(route('home'));
        $this->assertGuest();
    }

    public function test_admin_seeder_is_idempotent_and_preserves_existing_password(): void
    {
        config()->set([
            'admin.name' => 'Administratrice NATE',
            'admin.email' => 'admin@nate.test',
            'admin.password' => 'initial-password',
        ]);

        $this->app->make(AdminSeeder::class)->run();
        $admin = User::query()->where('email', 'admin@nate.test')->firstOrFail();
        $initialPasswordHash = $admin->password;

        $this->assertTrue($admin->is_admin);
        $this->assertTrue(Hash::check('initial-password', $initialPasswordHash));

        config()->set('admin.password', 'replacement-password');
        $this->app->make(AdminSeeder::class)->run();

        $admin->refresh();

        $this->assertSame(1, User::query()->where('email', 'admin@nate.test')->count());
        $this->assertSame($initialPasswordHash, $admin->password);
        $this->assertFalse(Hash::check('replacement-password', $admin->password));
    }

    public function test_is_admin_cannot_be_mass_assigned(): void
    {
        $user = User::factory()->create();

        $user->fill(['is_admin' => true]);
        $user->save();

        $this->assertFalse($user->fresh()->is_admin);
    }
}
