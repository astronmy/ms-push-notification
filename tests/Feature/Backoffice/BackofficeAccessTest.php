<?php

namespace Tests\Feature\Backoffice;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackofficeAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_and_admin_can_open_the_modules(): void
    {
        $this->get('/admin/clients')->assertRedirect('/login');

        $this->seed(RolePermissionSeeder::class);
        $admin = User::query()->where('email', config('push.admin_email'))->firstOrFail();
        $role = Role::query()->where('slug', Role::SLUG_ADMIN)->firstOrFail();
        $this->assertTrue($admin->role->is($role));

        $this->actingAs($admin)->get('/login')->assertRedirect('/admin/dashboard');

        foreach ([
            '/admin/dashboard',
            '/admin/clients',
            '/admin/devices',
            '/admin/notifications',
            '/admin/queue',
            '/admin/logs/api',
            '/admin/logs/firebase-requests',
            '/admin/logs/firebase-logs',
            '/admin/users',
            '/admin/roles',
        ] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }
}
