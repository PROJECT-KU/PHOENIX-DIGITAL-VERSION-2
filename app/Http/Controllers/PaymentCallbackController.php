<?php

namespace App\Http\Controllers;

use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentCallbackController extends Controller
{
    public function midtrans(Request $request)
    {
        $paymentService = new PaymentService();
        $result = $paymentService->handleCallback($request->all());

        return response()->json($result);
    }
}
