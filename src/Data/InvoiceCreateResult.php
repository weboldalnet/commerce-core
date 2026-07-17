<?php

namespace Weboldalnet\CommerceCore\Data;

class InvoiceCreateResult
{
    public $success;
    public $status;
    public $provider;
    public $invoiceNumber;
    public $invoiceId;
    public $pdfPath;
    public $message;
    public $rawResponse;

    public function __construct(array $data = [])
    {
        $this->success = $data['success'] ?? false;
        $this->status = $data['status'] ?? null;
        $this->provider = $data['provider'] ?? null;
        $this->invoiceNumber = $data['invoice_number'] ?? null;
        $this->invoiceId = $data['invoice_id'] ?? null;
        $this->pdfPath = $data['pdf_path'] ?? null;
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
            'invoice_number' => $this->invoiceNumber,
            'invoice_id' => $this->invoiceId,
            'pdf_path' => $this->pdfPath,
            'message' => $this->message,
            'raw_response' => $this->rawResponse,
        ];
    }
}
