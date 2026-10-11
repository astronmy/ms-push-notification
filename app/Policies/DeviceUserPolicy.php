<?php

namespace App\Policies;

use App\Models\DeviceUser;
use App\Models\User;

class DeviceUserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canPermission('dispositivos.ver');
    }

    public function view(User $user, DeviceUser $deviceUser): bool
    {
        return $user->canPermission('dispositivos.ver');
    }

    public function create(User $user): bool
    {
        return $user->canPermission('dispositivos.crear');
    }

    public function update(User $user, DeviceUser $deviceUser): bool
    {
        return $user->canPermission('dispositivos.editar');
    }

    public function delete(User $user, DeviceUser $deviceUser): bool
    {
        return $user->canPermission('dispositivos.eliminar');
    }
}
