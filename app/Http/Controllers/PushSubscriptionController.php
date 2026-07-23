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
            'deviceId' => ['nullable', 'string', 'max:64'],
        ]);

        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'unauthenticated'], 401);
        }

        $hash = hash('sha256', $data['endpoint']);
        $did = $data['deviceId'] ?? null;
        $attrs = [
            'user_id' => $user->id,
            'device_id' => $did,
            'endpoint' => $data['endpoint'],
            'endpoint_hash' => $hash,
            'public_key' => $data['keys']['p256dh'],
            'auth_token' => $data['keys']['auth'],
            'content_encoding' => $data['contentEncoding'] ?? null,
        ];

        if ($did) {
            // Buang baris lain dgn endpoint yg sama persis (hindari bentrok
            // unique endpoint_hash saat baris device ini di-update).
            PushSubscription::where('endpoint_hash', $hash)
                ->where(fn ($q) => $q->where('user_id', '!=', $user->id)->orWhere('device_id', '!=', $did))
                ->delete();

            // Satu PERANGKAT (device_id) = SATU langganan. Endpoint yg ter-rotasi
            // di perangkat yg sama menimpa baris ini → tidak menumpuk duplikat.
            $sub = PushSubscription::updateOrCreate(
                ['user_id' => $user->id, 'device_id' => $did],
                $attrs
            );
        } else {
            // Klien lama tanpa deviceId → jatuh ke pencocokan endpoint.
            $sub = PushSubscription::updateOrCreate(['endpoint_hash' => $hash], $attrs);
        }

        // PENGAMAN dobel: satu perangkat FISIK bisa terdaftar >1 kali bila
        // localStorage (tempat device_id) terhapus/reinstall → device_id baru →
        // baris baru, yg lama tetap aktif → notif DOBEL di perangkat yg sama.
        // Karena itu simpan HANYA langganan TERBARU per LAYANAN PUSH (Apple/FCM)
        // milik user ini. Trade-off: 2 perangkat pada layanan push yang sama
        // (mis. 2 iPhone / laptop+Android FCM) akan menyisakan yang terbaru saja —
        // jarang untuk admin, dan jauh lebih baik daripada notif dobel/ganda.
        $host = parse_url($data['endpoint'], PHP_URL_HOST);
        if ($host) {
            PushSubscription::where('user_id', $user->id)
                ->where('id', '!=', $sub->id)
                ->where('endpoint', 'like', 'https://'.$host.'%')
                ->delete();
        }

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
