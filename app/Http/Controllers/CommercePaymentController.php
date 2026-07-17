<?php

namespace Weboldalnet\CommerceCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Weboldalnet\CommerceCore\Services\PaymentCallbackProcessor;
use Weboldalnet\CommerceCore\Managers\PaymentManager;

class CommercePaymentController extends Controller
{
    protected $callbackProcessor;
    protected $paymentManager;

    public function __construct(PaymentCallbackProcessor $callbackProcessor, PaymentManager $paymentManager)
    {
        $this->callbackProcessor = $callbackProcessor;
        $this->paymentManager = $paymentManager;
    }

    public function return(Request $request, $provider)
    {
        if (!$this->paymentManager->hasProvider($provider)) {
            abort(404, "Payment provider '{$provider}' not found.");
        }

        $result = $this->paymentManager->handleReturn($provider, $request->all());

        return response()->json([
            'success' => $result->success ?? false,
            'status' => $result->status ?? null,
            'provider' => $provider,
            'message' => $result->message ?? null,
        ]);
    }

    public function callback(Request $request, $provider)
    {
        if (!$this->paymentManager->hasProvider($provider)) {
            return response()->json(['error' => "Provider '{$provider}' not found."], 404);
        }

        try {
            $processResult = $this->callbackProcessor->process($provider, $request->all());

            return response()->json([
                'success' => true,
                'skipped' => $processResult['skipped'] ?? false,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
