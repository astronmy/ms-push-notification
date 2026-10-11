<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canPermission('usuarios.ver');
    }

    public function view(User $user, User $model): bool
    {
        return $user->canPermission('usuarios.ver');
    }

    public function create(User $user): bool
    {
        return $user->canPermission('usuarios.crear');
    }

    public function update(User $user, User $model): bool
    {
        return $user->canPermission('usuarios.editar');
    }

    public function delete(User $user, User $model): bool
    {
        return $user->canPermission('usuarios.eliminar');
    }
}
