<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDeviceUserRequest;
use App\Http\Requests\Admin\UpdateDeviceUserRequest;
use App\Models\Client;
use App\Models\DeviceUser;
use App\Services\Devices\RegisterDeviceUserService;
use App\Support\JsonField;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeviceUserController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', DeviceUser::class);

        $devices = DeviceUser::query()
            ->with('client')
            ->when($request->filled('client_id'), fn ($query) => $query->where('client_id', $request->integer('client_id')))
            ->when($request->filled('external_user_id'), fn ($query) => $query->where('external_user_id', 'like', '%'.$request->string('external_user_id').'%'))
            ->when($request->filled('platform'), fn ($query) => $query->where('platform', $request->string('platform')))
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('is_active', $request->string('status') === 'active');
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.devices.index', [
            'devices' => $devices,
            'clients' => Client::query()->orderBy('name')->get(['id', 'name', 'contact_email', 'firebase_project_id', 'is_active']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', DeviceUser::class);

        return view('admin.devices.create', [
            'clients' => Client::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(StoreDeviceUserRequest $request, RegisterDeviceUserService $service): RedirectResponse
    {
        $data = $request->validated();
        $client = Client::query()->findOrFail($data['client_id']);

        $service->upsert($client, [
            'external_user_id' => $data['external_user_id'],
            'firebase_token' => $data['firebase_token'],
            'platform' => $data['platform'],
            'name' => $data['name'] ?? null,
            'meta' => JsonField::decode($data['meta_json'] ?? null, 'meta_json'),
        ]);

        $device = DeviceUser::query()
            ->where('client_id', $client->id)
            ->where('external_user_id', $data['external_user_id'])
            ->firstOrFail();

        if (! $request->boolean('is_active')) {
            $device->update(['is_active' => false]);
        }

        return redirect()
            ->route('admin.devices.index')
            ->with('status', __('device.messages.saved'));
    }

    public function edit(DeviceUser $device): View
    {
        $this->authorize('update', $device);

        return view('admin.devices.edit', [
            'device' => $device->load('client'),
        ]);
    }

    public function update(UpdateDeviceUserRequest $request, DeviceUser $device): RedirectResponse
    {
        $data = $request->validated();

        $device->update([
            'firebase_token' => $data['firebase_token'],
            'platform' => $data['platform'],
            'name' => $data['name'] ?? null,
            'meta' => JsonField::decode($data['meta_json'] ?? null, 'meta_json'),
            'is_active' => $request->boolean('is_active'),
            'invalidated_at' => $request->boolean('is_active') ? null : ($device->invalidated_at ?? now()),
            'last_registered_at' => now(),
        ]);

        return redirect()
            ->route('admin.devices.index')
            ->with('status', __('device.messages.saved'));
    }

    public function destroy(DeviceUser $device): RedirectResponse
    {
        $this->authorize('delete', $device);
        $device->delete();

        return redirect()
            ->route('admin.devices.index')
            ->with('status', __('device.messages.deleted'));
    }
}
