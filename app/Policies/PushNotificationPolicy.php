<?php

namespace App\Policies;

use App\Models\PushNotification;
use App\Models\User;

class PushNotificationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canPermission('notificaciones.ver') || $user->canPermission('cola.ver');
    }

    public function view(User $user, PushNotification $pushNotification): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->canPermission('notificaciones.crear');
    }

    public function update(User $user, PushNotification $pushNotification): bool
    {
        return $user->canPermission('notificaciones.editar');
    }

    public function delete(User $user, PushNotification $pushNotification): bool
    {
        return $user->canPermission('notificaciones.eliminar');
    }

    public function cancel(User $user, PushNotification $pushNotification): bool
    {
        return $user->canPermission('cola.cancelar');
    }

    public function retry(User $user, PushNotification $pushNotification): bool
    {
        return $user->canPermission('cola.reintentar');
    }
}
