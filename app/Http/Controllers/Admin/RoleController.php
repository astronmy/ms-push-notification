<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Role::class);

        $roles = Role::query()
            ->withCount(['users', 'permissions'])
            ->when($request->filled('name'), fn ($query) => $query->where('name', 'like', '%'.$request->string('name').'%'))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.roles.index', compact('roles'));
    }

    public function create(): View
    {
        $this->authorize('create', Role::class);

        return view('admin.roles.create', [
            'permissions' => Permission::groupedByModule(),
        ]);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $role = Role::query()->create([
            'name' => $data['name'],
            'slug' => $this->uniqueSlug($data['name']),
            'is_system' => false,
            'is_active' => $request->boolean('is_active', true),
        ]);

        $role->permissions()->sync($data['permissions'] ?? []);

        return redirect()
            ->route('admin.roles.index')
            ->with('status', __('role.messages.created'));
    }

    public function edit(Role $role): View
    {
        $this->authorize('update', $role);
        $role->load('permissions');

        return view('admin.roles.edit', [
            'role' => $role,
            'permissions' => Permission::groupedByModule(),
            'selected' => $role->permissions->pluck('id')->all(),
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $data = $request->validated();
        $active = $role->isAdmin() ? true : $request->boolean('is_active');

        $payload = [
            'name' => $data['name'],
            'is_active' => $active,
        ];

        if (! $role->is_system) {
            $payload['slug'] = $this->uniqueSlug($data['name'], $role->id);
        }

        $role->update($payload);
        $role->permissions()->sync($data['permissions'] ?? []);

        return redirect()
            ->route('admin.roles.index')
            ->with('status', __('role.messages.updated'));
    }

    public function destroy(Role $role): RedirectResponse
    {
        $this->authorize('delete', $role);

        if ($role->isAdmin()) {
            return back()->with('error', __('role.messages.cannot_delete_admin'));
        }

        if ($role->is_system || $role->users()->exists()) {
            $role->update(['is_active' => false]);

            return redirect()
                ->route('admin.roles.index')
                ->with('status', __('role.messages.deactivated'));
        }

        $role->permissions()->detach();
        $role->delete();

        return redirect()
            ->route('admin.roles.index')
            ->with('status', __('role.messages.deleted'));
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'rol';
        $slug = $base;
        $i = 2;

        while (Role::query()
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }
}
