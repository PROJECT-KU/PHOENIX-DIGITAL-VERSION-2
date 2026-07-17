<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use App\Services\WebPushService;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    public function __construct(protected WebPushService $webPush) {}

    /**
     * Simpan / perbarui langganan push milik user yang sedang login.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'endpoint' => ['required', 'string'],
            'keys.p256dh' => ['required', 'string'],
            'keys.auth' => ['required', 'string'],
            'contentEncoding' => ['nullable', 'string'],
        ]);

        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'unauthenticated'], 401);
        }

        $sub = PushSubscription::updateOrCreate(
            ['endpoint_hash' => hash('sha256', $data['endpoint'])],
            [
                'user_id' => $user->id,
                'endpoint' => $data['endpoint'],
                'public_key' => $data['keys']['p256dh'],
                'auth_token' => $data['keys']['auth'],
                'content_encoding' => $data['contentEncoding'] ?? null,
            ]
        );

        // Baru pertama kali aktif di perangkat ini → kirim push konfirmasi
        // sekaligus set badge ke jumlah unread saat ini (bukan tiap reload).
        if ($sub->wasRecentlyCreated) {
            $unread = $user->unreadNotifications()
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->count();

            $this->webPush->sendToUser($user, [
                'title' => 'Notifikasi perangkat aktif 🍋',
                'body' => $unread > 0
                    ? 'Anda punya '.$unread.' notifikasi belum dibaca.'
                    : 'Anda akan menerima notifikasi di sini.',
                'url' => '/admin/dashboard',
                'unread' => $unread,
            ]);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Hapus langganan (mis. user mematikan notifikasi di perangkat ini).
     */
    public function destroy(Request $request)
    {
        $data = $request->validate([
            'endpoint' => ['required', 'string'],
        ]);

        PushSubscription::where('endpoint_hash', hash('sha256', $data['endpoint']))
            ->when($request->user(), fn ($q, $u) => $q->where('user_id', $u->id))
            ->delete();

        return response()->json(['ok' => true]);
    }
}
