<?php

namespace App\Http\Controllers\Api;

use App\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMessageRequest;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class MessageController extends Controller
{
    use ApiResponse;

    public function store(StoreMessageRequest $request): JsonResponse
    {
        try {
            $contact = Message::create([
                'name' => $request->name,
                'email' => $request->email,
                'message' => $request->message,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Optional: Send notification email
            // Mail::to(config('mail.admin_email'))->send(new ContactReceived($contact));

            Log::info('Contact form submitted', [
                'contact_id' => $contact->id,
                'email' => $contact->email,
            ]);

            return $this->created(
                [
                    'id' => $contact->id,
                    'name' => $contact->name,
                    'email' => $contact->email,
                    'submitted_at' => $contact->created_at->toISOString(),
                ],
                'Pesan Anda berhasil dikirim. Kami akan menghubungi Anda segera.'
            );
        } catch (\Exception $e) {
            Log::error('Contact form error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->fail(
                'Terjadi kesalahan saat mengirim pesan. Silakan coba lagi.',
                null,
                500
            );
        }
    }
}
