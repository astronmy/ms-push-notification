<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PushNotificationStatus;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\PushNotification;
use App\Services\Notifications\CancelPushNotificationService;
use App\Services\Notifications\RetryPushNotificationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QueueController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->canPermission('cola.ver'), 403);

        $tab = $request->string('tab')->toString() ?: 'pending';
        $statuses = match ($tab) {
            'processing' => [PushNotificationStatus::Processing->value],
            'sent' => [PushNotificationStatus::Sent->value],
            'failed' => [PushNotificationStatus::Failed->value],
            'cancelled' => [PushNotificationStatus::Cancelled->value],
            default => [PushNotificationStatus::Pending->value, PushNotificationStatus::Scheduled->value],
        };

        $notifications = PushNotification::query()
            ->with('client')
            ->withCount('recipients')
            ->whereIn('status', $statuses)
            ->when($request->filled('client_id'), fn ($query) => $query->where('client_id', $request->integer('client_id')))
            ->when($request->filled('from'), fn ($query) => $query->where('created_at', '>=', Carbon::parse($request->string('from'))->startOfDay()))
            ->when($request->filled('to'), fn ($query) => $query->where('created_at', '<=', Carbon::parse($request->string('to'))->endOfDay()))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.queue.index', [
            'notifications' => $notifications,
            'clients' => Client::query()->orderBy('name')->get(['id', 'name']),
            'tab' => $tab,
        ]);
    }

    public function show(PushNotification $notification): View
    {
        $this->authorize('view', $notification);
        $notification->load(['client', 'creator', 'recipients.deviceUser']);

        return view('admin.notifications.show', [
            'notification' => $notification,
            'fromQueue' => true,
        ]);
    }

    public function cancel(PushNotification $notification, CancelPushNotificationService $service): RedirectResponse
    {
        $this->authorize('cancel', $notification);
        $service->cancel($notification);

        return back()->with('status', __('queue.messages.cancelled'));
    }

    public function retry(PushNotification $notification, RetryPushNotificationService $service): RedirectResponse
    {
        $this->authorize('retry', $notification);
        $service->retry($notification);

        return back()->with('status', __('queue.messages.retried'));
    }
}
