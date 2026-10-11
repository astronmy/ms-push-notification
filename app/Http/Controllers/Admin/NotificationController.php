<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PushOrigin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePushNotificationRequest;
use App\Http\Requests\Admin\UpdatePushNotificationRequest;
use App\Models\Client;
use App\Models\PushNotification;
use App\Services\Notifications\CancelPushNotificationService;
use App\Services\Notifications\QueuePushNotificationService;
use App\Support\JsonField;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', PushNotification::class);

        $notifications = $this->filtered($request)
            ->paginate(15)
            ->withQueryString();

        return view('admin.notifications.index', [
            'notifications' => $notifications,
            'clients' => Client::query()->orderBy('name')->get(['id', 'name', 'contact_email', 'firebase_project_id', 'is_active']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', PushNotification::class);

        return view('admin.notifications.create', [
            'clients' => Client::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(StorePushNotificationRequest $request, QueuePushNotificationService $service): RedirectResponse
    {
        $data = $request->validated();
        $client = Client::query()->findOrFail($data['client_id']);

        $notification = $service->queue(
            client: $client,
            externalUserIds: $data['external_user_ids'],
            title: $data['title'],
            body: $data['body'],
            imageUrl: $data['image_url'] ?? null,
            data: JsonField::decode($data['data_json'] ?? null, 'data_json'),
            scheduledAt: isset($data['scheduled_at']) ? Carbon::parse($data['scheduled_at']) : null,
            origin: PushOrigin::Backoffice,
            createdBy: $request->user()->id,
        );

        return redirect()
            ->route('admin.notifications.show', $notification)
            ->with('status', __('notification.messages.queued'));
    }

    public function show(PushNotification $notification): View
    {
        $this->authorize('view', $notification);

        $notification->load(['client', 'creator', 'recipients.deviceUser']);

        return view('admin.notifications.show', compact('notification'));
    }

    public function edit(PushNotification $notification): View
    {
        $this->authorize('update', $notification);
        abort_unless($notification->status->isEditable(), 403);

        $notification->load('recipients.deviceUser');

        return view('admin.notifications.edit', [
            'notification' => $notification,
            'clients' => Client::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(
        UpdatePushNotificationRequest $request,
        PushNotification $notification,
        QueuePushNotificationService $service,
    ): RedirectResponse {
        $data = $request->validated();

        $service->update(
            notification: $notification,
            externalUserIds: $data['external_user_ids'],
            title: $data['title'],
            body: $data['body'],
            imageUrl: $data['image_url'] ?? null,
            data: JsonField::decode($data['data_json'] ?? null, 'data_json'),
            scheduledAt: isset($data['scheduled_at']) ? Carbon::parse($data['scheduled_at']) : null,
        );

        return redirect()
            ->route('admin.notifications.show', $notification)
            ->with('status', __('notification.messages.updated'));
    }

    public function destroy(PushNotification $notification, CancelPushNotificationService $cancel): RedirectResponse
    {
        $this->authorize('delete', $notification);

        if ($notification->status->isCancellable()) {
            $cancel->cancel($notification);
        }

        if ($notification->fresh()->status->value === 'processing') {
            return back()->with('error', __('notification.messages.cannot_delete_processing'));
        }

        $notification->delete();

        return redirect()
            ->route('admin.notifications.index')
            ->with('status', __('notification.messages.deleted'));
    }

    private function filtered(Request $request): Builder
    {
        return PushNotification::query()
            ->with('client')
            ->withCount('recipients')
            ->when($request->filled('client_id'), fn ($query) => $query->where('client_id', $request->integer('client_id')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('from'), fn ($query) => $query->where('created_at', '>=', Carbon::parse($request->string('from'))->startOfDay()))
            ->when($request->filled('to'), fn ($query) => $query->where('created_at', '<=', Carbon::parse($request->string('to'))->endOfDay()))
            ->orderByDesc('id');
    }
}
