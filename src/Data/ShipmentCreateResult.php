<?php

namespace Weboldalnet\CommerceCore\Data;

class ShipmentCreateResult
{
    public $success;
    public $status;
    public $provider;
    public $trackingNumber;
    public $trackingUrl;
    public $labelPath;
    public $message;
    public $rawResponse;

    public function __construct(array $data = [])
    {
        $this->success = $data['success'] ?? false;
        $this->status = $data['status'] ?? null;
        $this->provider = $data['provider'] ?? null;
        $this->trackingNumber = $data['tracking_number'] ?? null;
        $this->trackingUrl = $data['tracking_url'] ?? null;
        $this->labelPath = $data['label_path'] ?? null;
        $this->message = $data['message'] ?? null;
        $this->rawResponse = $data['raw_response'] ?? null;
    }

    public static function fromArray(array $data)
    {
        return new static($data);
    }

    public static function success(array $data = [])
    {
        $data['success'] = true;
        return new static($data);
    }

    public static function failure(array $data = [])
    {
        $data['success'] = false;
        return new static($data);
    }

    public function toArray()
    {
        return [
            'success' => $this->success,
            'status' => $this->status,
            'provider' => $this->provider,
            'tracking_number' => $this->trackingNumber,
            'tracking_url' => $this->trackingUrl,
            'label_path' => $this->labelPath,
            'message' => $this->message,
            'raw_response' => $this->rawResponse,
        ];
    }
}
