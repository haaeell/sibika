<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function markRead(Request $request, Notification $notification): RedirectResponse|JsonResponse
    {
        abort_unless($notification->user_id === $request->user()->id, 403);

        $notification->update(['read_at' => now()]);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'unread' => $this->unreadCount($request)]);
        }

        return redirect($notification->url ?? route('dashboard'));
    }

    public function markAllRead(Request $request): RedirectResponse|JsonResponse
    {
        Notification::where('user_id', $request->user()->id)->unread()->update(['read_at' => now()]);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'unread' => 0]);
        }

        return back()->with('success', 'Semua notifikasi ditandai dibaca.');
    }

    private function unreadCount(Request $request): int
    {
        return Notification::where('user_id', $request->user()->id)->unread()->count();
    }
}
