<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    /**
     * @var list<array{module: string, action: string, label: string}>
     */
    private array $catalog = [
        ['module' => 'dashboard', 'action' => 'ver', 'label' => 'Ver inicio'],
        ['module' => 'clientes', 'action' => 'ver', 'label' => 'Ver clientes'],
        ['module' => 'clientes', 'action' => 'crear', 'label' => 'Crear clientes'],
        ['module' => 'clientes', 'action' => 'editar', 'label' => 'Editar clientes'],
        ['module' => 'clientes', 'action' => 'eliminar', 'label' => 'Eliminar clientes'],
        ['module' => 'dispositivos', 'action' => 'ver', 'label' => 'Ver usuarios'],
        ['module' => 'dispositivos', 'action' => 'crear', 'label' => 'Crear usuarios'],
        ['module' => 'dispositivos', 'action' => 'editar', 'label' => 'Editar usuarios'],
        ['module' => 'dispositivos', 'action' => 'eliminar', 'label' => 'Eliminar usuarios'],
        ['module' => 'notificaciones', 'action' => 'ver', 'label' => 'Ver notificaciones'],
        ['module' => 'notificaciones', 'action' => 'crear', 'label' => 'Crear notificaciones'],
        ['module' => 'notificaciones', 'action' => 'editar', 'label' => 'Editar notificaciones'],
        ['module' => 'notificaciones', 'action' => 'eliminar', 'label' => 'Eliminar notificaciones'],
        ['module' => 'cola', 'action' => 'ver', 'label' => 'Ver la cola'],
        ['module' => 'cola', 'action' => 'cancelar', 'label' => 'Cancelar envíos'],
        ['module' => 'cola', 'action' => 'reintentar', 'label' => 'Reintentar envíos'],
        ['module' => 'logs', 'action' => 'ver', 'label' => 'Ver logs'],
        ['module' => 'usuarios', 'action' => 'ver', 'label' => 'Ver usuarios del backoffice'],
        ['module' => 'usuarios', 'action' => 'crear', 'label' => 'Crear usuarios del backoffice'],
        ['module' => 'usuarios', 'action' => 'editar', 'label' => 'Editar usuarios del backoffice'],
        ['module' => 'usuarios', 'action' => 'eliminar', 'label' => 'Eliminar usuarios del backoffice'],
        ['module' => 'roles', 'action' => 'ver', 'label' => 'Ver roles'],
        ['module' => 'roles', 'action' => 'crear', 'label' => 'Crear roles'],
        ['module' => 'roles', 'action' => 'editar', 'label' => 'Editar roles'],
        ['module' => 'roles', 'action' => 'eliminar', 'label' => 'Eliminar roles'],
    ];

    public function run(): void
    {
        DB::transaction(function () {
            $permissionIds = [];

            foreach ($this->catalog as $item) {
                $permission = Permission::query()->updateOrCreate(
                    ['slug' => $item['module'].'.'.$item['action']],
                    $item,
                );
                $permissionIds[] = $permission->id;
            }

            $admin = Role::query()->updateOrCreate(
                ['slug' => Role::SLUG_ADMIN],
                [
                    'name' => 'Administrador',
                    'is_system' => true,
                    'is_active' => true,
                ],
            );

            $admin->permissions()->sync($permissionIds);

            User::query()->updateOrCreate(
                ['email' => config('push.admin_email')],
                [
                    'name' => config('push.admin_name'),
                    'password' => config('push.admin_password'),
                    'role_id' => $admin->id,
                ],
            );
        });
    }
}
