<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\DeviceUser;
use App\Models\PushNotification;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()?->canPermission('dashboard.ver'), 403);

        return view('admin.dashboard', [
            'clients' => Client::query()->count(),
            'devices' => DeviceUser::query()->count(),
            'pending' => PushNotification::query()->whereIn('status', ['pending', 'scheduled', 'processing'])->count(),
            'failed' => PushNotification::query()->where('status', 'failed')->count(),
        ]);
    }
}
