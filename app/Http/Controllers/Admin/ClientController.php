<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreClientRequest;
use App\Http\Requests\Admin\UpdateClientRequest;
use App\Models\Client;
use App\Services\Clients\ClientCredentialService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Client::class);

        $clients = Client::query()
            ->when($request->filled('name'), fn ($query) => $query->where('name', 'like', '%'.$request->string('name').'%'))
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('is_active', $request->string('status') === 'active');
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.clients.index', compact('clients'));
    }

    public function create(): View
    {
        $this->authorize('create', Client::class);

        return view('admin.clients.create');
    }

    public function store(StoreClientRequest $request, ClientCredentialService $credentials): RedirectResponse
    {
        $data = $request->validated();

        [$client, $plain] = DB::transaction(function () use ($request, $credentials, $data) {
            $placeholder = hash('sha256', Str::random(40));

            $client = Client::query()->create([
                'name' => $data['name'],
                'is_active' => $request->boolean('is_active'),
                'contact_name' => $data['contact_name'] ?? null,
                'contact_email' => $data['contact_email'] ?? null,
                'notes' => $data['notes'] ?? null,
                'rate_limit_per_minute' => $data['rate_limit_per_minute'] ?? null,
                'firebase_project_id' => $data['firebase_project_id'] ?? null,
                'android_notification_icon' => $data['android_notification_icon'] ?? null,
                'api_key_hash' => $placeholder,
                'api_key_prefix' => substr($placeholder, 0, 12),
                'api_key_last_four' => substr($placeholder, -4),
            ]);

            $plain = $credentials->issueKey($client);
            $credentials->storeServiceAccount(
                $client,
                $request->file('service_account')?->get(),
                $data['firebase_project_id'] ?? null,
            );

            return [$client, $plain];
        });

        return redirect()
            ->route('admin.clients.edit', $client)
            ->with('api_key', $plain)
            ->with('status', __('client.messages.created'));
    }

    public function edit(Client $client): View
    {
        $this->authorize('update', $client);

        return view('admin.clients.edit', compact('client'));
    }

    public function update(UpdateClientRequest $request, Client $client, ClientCredentialService $credentials): RedirectResponse
    {
        $data = $request->validated();

        $client->update([
            'name' => $data['name'],
            'is_active' => $request->boolean('is_active'),
            'contact_name' => $data['contact_name'] ?? null,
            'contact_email' => $data['contact_email'] ?? null,
            'notes' => $data['notes'] ?? null,
            'rate_limit_per_minute' => $data['rate_limit_per_minute'] ?? null,
            'android_notification_icon' => $data['android_notification_icon'] ?? null,
        ]);

        $credentials->storeServiceAccount(
            $client,
            $request->file('service_account')?->get(),
            $data['firebase_project_id'] ?? $client->firebase_project_id,
        );

        return redirect()
            ->route('admin.clients.edit', $client)
            ->with('status', __('client.messages.updated'));
    }

    public function destroy(Client $client): RedirectResponse
    {
        $this->authorize('delete', $client);
        $client->delete();

        return redirect()
            ->route('admin.clients.index')
            ->with('status', __('client.messages.deleted'));
    }

    public function regenerateKey(Client $client, ClientCredentialService $credentials): RedirectResponse
    {
        $this->authorize('update', $client);

        $plain = $credentials->issueKey($client);

        return redirect()
            ->route('admin.clients.edit', $client)
            ->with('api_key', $plain)
            ->with('status', __('client.messages.key_regenerated'));
    }

    public function devices(Client $client): JsonResponse
    {
        abort_unless(auth()->user()?->canPermission('notificaciones.crear') || auth()->user()?->canPermission('dispositivos.ver'), 403);

        $devices = $client->deviceUsers()
            ->where('is_active', true)
            ->orderBy('external_user_id')
            ->get(['id', 'external_user_id', 'name', 'platform']);

        return response()->json([
            'data' => $devices->map(fn ($device) => [
                'id' => $device->id,
                'external_user_id' => $device->external_user_id,
                'name' => $device->name,
                'platform' => $device->platform->value,
            ]),
        ]);
    }
}
