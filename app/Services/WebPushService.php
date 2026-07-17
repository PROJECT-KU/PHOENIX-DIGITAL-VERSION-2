<?php

namespace App\Services;

use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class WebPushService
{
    /**
     * Kirim notifikasi push ke semua langganan (perangkat) milik user.
     * Langganan yang sudah kedaluwarsa (404/410) otomatis dihapus.
     *
     * @param  array  $payload  ['title' => ..., 'body' => ..., 'url' => ..., 'unread' => int]
     */
    public function sendToUser(User $user, array $payload): void
    {
        $subs = $user->pushSubscriptions()->get();
        if ($subs->isEmpty()) {
            return;
        }

        $webPush = $this->makeClient();
        if (! $webPush) {
            return;
        }

        $json = json_encode($payload);

        foreach ($subs as $sub) {
            $webPush->queueNotification(
                Subscription::create([
                    'endpoint' => $sub->endpoint,
                    'publicKey' => $sub->public_key,
                    'authToken' => $sub->auth_token,
                    'contentEncoding' => $sub->content_encoding ?: 'aesgcm',
                ]),
                $json
            );
        }

        foreach ($webPush->flush() as $report) {
            $endpoint = $report->getRequest()->getUri()->__toString();

            if (! $report->isSuccess()) {
                // 404/410 = langganan sudah mati → bersihkan.
                if ($report->isSubscriptionExpired()) {
                    PushSubscription::where('endpoint_hash', hash('sha256', $endpoint))->delete();
                } else {
                    Log::warning('WebPush gagal', [
                        'endpoint' => $endpoint,
                        'reason' => $report->getReason(),
                    ]);
                }
            }
        }
    }

    protected function makeClient(): ?WebPush
    {
        $public = config('services.webpush.public_key');
        $private = config('services.webpush.private_key');
        if (! $public || ! $private) {
            return null;
        }

        return new WebPush([
            'VAPID' => [
                'subject' => config('services.webpush.subject'),
                'publicKey' => $public,
                'privateKey' => $private,
            ],
        ]);
    }
}
