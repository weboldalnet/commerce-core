<?php

namespace Weboldalnet\CommerceCore\Services;

use Weboldalnet\CommerceCore\Models\ProviderLog;

class ProviderLogger
{
    protected $sensitiveKeys = [
        'token', 'secret', 'password', 'api_key', 'merchant_key',
        'signature', 'card_number', 'cvv', 'cvc', 'expiry',
        'pan', 'card_data', 'private_key', 'auth_token',
    ];

    public function __construct()
    {
        $configKeys = config('commerce-core.provider_logs.sanitize_keys', []);
        if (!empty($configKeys)) {
            $this->sensitiveKeys = array_merge($this->sensitiveKeys, $configKeys);
        }
    }

    public function log(array $data)
    {
        if (!config('commerce-core.provider_logs.enabled', true)) {
            return null;
        }

        return ProviderLog::create([
            'provider_type' => $data['provider_type'] ?? null,
            'provider' => $data['provider'] ?? null,
            'order_id' => $data['order_id'] ?? null,
            'direction' => $data['direction'] ?? null,
            'endpoint' => $data['endpoint'] ?? null,
            'request_payload' => isset($data['request_payload']) ? $this->sanitizePayload($data['request_payload']) : null,
            'response_payload' => isset($data['response_payload']) ? $this->sanitizePayload($data['response_payload']) : null,
            'status_code' => $data['status_code'] ?? null,
            'is_success' => $data['is_success'] ?? false,
            'error_message' => $data['error_message'] ?? null,
        ]);
    }

    public function logRequest($providerType, $provider, $endpoint, $requestPayload, $orderId = null)
    {
        return $this->log([
            'provider_type' => $providerType,
            'provider' => $provider,
            'order_id' => $orderId,
            'direction' => 'outgoing',
            'endpoint' => $endpoint,
            'request_payload' => $requestPayload,
        ]);
    }

    public function logResponse($providerType, $provider, $endpoint, $requestPayload, $responsePayload, $statusCode, $isSuccess, $errorMessage = null, $orderId = null)
    {
        return $this->log([
            'provider_type' => $providerType,
            'provider' => $provider,
            'order_id' => $orderId,
            'direction' => 'incoming',
            'endpoint' => $endpoint,
            'request_payload' => $requestPayload,
            'response_payload' => $responsePayload,
            'status_code' => $statusCode,
            'is_success' => $isSuccess,
            'error_message' => $errorMessage,
        ]);
    }

    public function logCallback($providerType, $provider, $callbackPayload, $isSuccess, $errorMessage = null, $orderId = null)
    {
        return $this->log([
            'provider_type' => $providerType,
            'provider' => $provider,
            'order_id' => $orderId,
            'direction' => 'callback',
            'request_payload' => $callbackPayload,
            'is_success' => $isSuccess,
            'error_message' => $errorMessage,
        ]);
    }

    public function sanitizePayload($payload)
    {
        if (is_string($payload)) {
            $decoded = json_decode($payload, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $payload = $decoded;
            } else {
                return $payload;
            }
        }

        if (!is_array($payload)) {
            return $payload;
        }

        return $this->sanitizeArray($payload);
    }

    protected function sanitizeArray(array $data)
    {
        foreach ($data as $key => $value) {
            if ($this->isSensitiveKey($key)) {
                $data[$key] = '***REDACTED***';
            } elseif (is_array($value)) {
                $data[$key] = $this->sanitizeArray($value);
            }
        }

        return $data;
    }

    protected function isSensitiveKey($key)
    {
        $lowerKey = strtolower($key);
        foreach ($this->sensitiveKeys as $sensitiveKey) {
            if (strpos($lowerKey, strtolower($sensitiveKey)) !== false) {
                return true;
            }
        }
        return false;
    }
}
