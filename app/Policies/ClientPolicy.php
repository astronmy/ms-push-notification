<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;

class ClientPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canPermission('clientes.ver');
    }

    public function view(User $user, Client $client): bool
    {
        return $user->canPermission('clientes.ver');
    }

    public function create(User $user): bool
    {
        return $user->canPermission('clientes.crear');
    }

    public function update(User $user, Client $client): bool
    {
        return $user->canPermission('clientes.editar');
    }

    public function delete(User $user, Client $client): bool
    {
        return $user->canPermission('clientes.eliminar');
    }
}
