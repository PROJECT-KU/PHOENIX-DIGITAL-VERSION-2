<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class PaymentService
{
    protected $serverKey;

    protected $clientKey;

    protected $isProduction;

    public function __construct()
    {
        $this->serverKey = config('midtrans.server_key');
        $this->clientKey = config('midtrans.client_key');
        $this->isProduction = config('midtrans.is_production', false);

        // Set Midtrans Config
        \Midtrans\Config::$serverKey = $this->serverKey;
        \Midtrans\Config::$isProduction = $this->isProduction;
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;
    }

    public function createPayment(Order $order)
    {
        try {
            $transactionDetails = [
                'order_id' => $order->order_number,
                'gross_amount' => (int) $order->total,
            ];

            $customerDetails = [
                'first_name' => $order->customer->nama,
                'email' => $order->customer->email,
                'phone' => $order->customer->no_hp,
            ];

            // Item details
            $itemDetails = [];
            foreach ($order->items as $item) {
                $itemDetails[] = [
                    'id' => $item->product_id,
                    'price' => (int) $item->price,
                    'quantity' => $item->quantity,
                    'name' => $item->product_name . ' - ' . $item->getDurationLabel(),
                ];
            }

            $params = [
                'transaction_details' => $transactionDetails,
                'customer_details' => $customerDetails,
                'item_details' => $itemDetails,
                'enabled_payments' => [
                    'qris',
                    'gopay',
                    'shopeepay',
                    'bca_va',
                    'bni_va',
                    'bri_va',
                    'permata_va',
                    'other_va',
                ],
                'expiry' => [
                    'unit' => 'hours',
                    'duration' => 24,
                ],
            ];

            // Create Snap Token
            $snapToken = \Midtrans\Snap::getSnapToken($params);

            // Get Snap URL
            $snapUrl = $this->isProduction
                ? "https://app.midtrans.com/snap/v2/vtweb/{$snapToken}"
                : "https://app.sandbox.midtrans.com/snap/v2/vtweb/{$snapToken}";

            // Save payment record
            $payment = Payment::create([
                'id' => Str::uuid(),
                'order_id' => $order->id,
                'payment_gateway' => 'midtrans',
                'transaction_id' => $order->order_number,
                'payment_method' => 'snap',
                'amount' => $order->total,
                'status' => 'pending',
                'payment_url' => $snapUrl,
                'expired_at' => now()->addHours(24),
            ]);

            // Update order
            $order->update([
                'payment_gateway' => 'midtrans',
                'payment_reference' => $order->order_number,
                'payment_url' => $snapUrl,
            ]);

            return [
                'success' => true,
                'snap_token' => $snapToken,
                'snap_url' => $snapUrl,
                'payment' => $payment,
            ];
        } catch (\Exception $e) {
            Log::error('Payment creation failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function handleCallback($notification)
    {
        try {
            // Verify notification
            $notif = new \Midtrans\Notification;

            $orderNumber = $notif->order_id;
            $transactionStatus = $notif->transaction_status;
            $fraudStatus = $notif->fraud_status;

            // Find order
            $order = Order::where('order_number', $orderNumber)->firstOrFail();
            $payment = Payment::where('transaction_id', $orderNumber)->firstOrFail();

            // Update payment gateway response
            $payment->update([
                'gateway_response' => json_decode(json_encode($notif), true),
            ]);

            // Handle transaction status
            if ($transactionStatus == 'capture') {
                if ($fraudStatus == 'accept') {
                    $this->markAsPaid($order, $payment);
                }
            } elseif ($transactionStatus == 'settlement') {
                $this->markAsPaid($order, $payment);
            } elseif ($transactionStatus == 'pending') {
                // Do nothing, wait for settlement
                $payment->update(['status' => 'pending']);
            } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
                $payment->update(['status' => $transactionStatus]);
                $order->update(['status' => 'cancelled']);
            }

            return ['success' => true];
        } catch (\Exception $e) {
            Log::error('Payment callback error: ' . $e->getMessage());

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function markAsPaid(Order $order, Payment $payment)
    {
        $payment->update([
            'status' => 'settlement',
            'paid_at' => now(),
        ]);

        $order->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        // TODO: Send notification & deliver products
        $this->sendNotification($order);
    }

    private function sendNotification(Order $order)
    {
        // Send email
        // \Mail::to($order->customer->email)->send(new OrderPaidMail($order));

        // Send WhatsApp (optional)
        // Implement WhatsApp API here
    }

    public function createQrisPayment(Order $order)
    {
        try {

            $response = Http::connectTimeout(10)
                ->timeout(20)
                ->retry(2, 300, throw: false)
                ->get(
                    'https://qris.interactive.co.id/restapi/qris/show_qris.php',
                    [
                        'do' => 'create-invoice',
                        'apikey' => config('services.qris.apikey'),
                        'mID' => config('services.qris.mid'),
                        'cliTrxNumber' => $order->order_number,
                        'cliTrxAmount' => (int) $order->total,
                        'useTip' => 'no',
                    ]
                );

            $result = $response->json();

            if (($result['status'] ?? null) !== 'success') {
                throw new \Exception(
                    $result['data']['qris_status'] ?? 'Gagal membuat QRIS'
                );
            }

            $payment = Payment::create([
                'id' => Str::uuid(),
                'order_id' => $order->id,
                'payment_gateway' => 'interactive',
                'transaction_id' => $result['data']['qris_invoiceid'],
                'payment_method' => 'qris',
                'amount' => $order->total,
                'status' => 'pending',
                'gateway_response' => $result,
                'expired_at' => now()->addMinutes(30),
            ]);

            $order->update([
                'payment_gateway' => 'interactive',
                'payment_reference' => $result['data']['qris_invoiceid'],
            ]);

            return [
                'success' => true,
                'payment' => $payment,
                'qris_content' => $result['data']['qris_content'],
                'invoice_id' => $result['data']['qris_invoiceid'],
                'nmid' => $result['data']['qris_nmid'],
                'request_date' => $result['data']['qris_request_date'],
            ];
        } catch (\Exception $e) {

            Log::error('QRIS Create Error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function checkQrisPayment(Payment $payment)
    {
        try {
            $response = Http::timeout(20)->get(
                'https://qris.interactive.co.id/restapi/qris/checkpaid_qris.php',
                [
                    'do' => 'checkStatus',
                    'apikey' => config('services.qris.apikey'),
                    'mID' => config('services.qris.mid'),
                    'invid' => $payment->transaction_id,
                    'trxvalue' => (int) $payment->amount,
                    'trxdate' => $payment->created_at->format('Y-m-d'),
                ]
            );

            $result = $response->json();

            if (
                ($result['status'] ?? null) === 'success'
                && ($result['data']['qris_status'] ?? null) === 'paid'
            ) {

                $order = $payment->order;

                $payment->update([
                    'gateway_response' => $result,
                ]);

                $this->markAsPaid($order, $payment);

                return true;
            }

            return false;
        } catch (\Exception $e) {

            Log::error('QRIS Check Error: ' . $e->getMessage());

            return false;
        }
    }
}
