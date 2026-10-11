<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePermission
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(403);
        }

        foreach ($permissions as $permission) {
            if ($user->canPermission($permission)) {
                return $next($request);
            }
        }

        abort(403, __('role.messages.forbidden'));
    }
}
