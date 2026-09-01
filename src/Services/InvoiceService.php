<?php

namespace Weboldalnet\CommerceCore\Services;

use Weboldalnet\CommerceCore\Managers\InvoiceManager;
use Weboldalnet\CommerceCore\Data\InvoiceRequestData;
use Weboldalnet\CommerceCore\Data\InvoiceCreateResult;
use Weboldalnet\CommerceCore\Models\InvoiceDocument;
use Weboldalnet\CommerceCore\Events\InvoiceCreated;
use Weboldalnet\CommerceCore\Events\InvoiceFailed;
use Weboldalnet\CommerceCore\Status\InvoiceStatus;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    protected $manager;

    public function __construct(InvoiceManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Számla létrehozása perzisztálással és eseményekkel
     */
    public function createInvoice($code, InvoiceRequestData $data): InvoiceCreateResult
    {
        // Idempotencia ellenőrzés
        $existing = InvoiceDocument::where('order_id', $data->orderId)
            ->whereIn('status', [InvoiceStatus::INVOICED, InvoiceStatus::PENDING])
            ->first();

        if ($existing && $existing->status === InvoiceStatus::INVOICED) {
            return new InvoiceCreateResult([
                'success' => true,
                'status' => $existing->status,
                'provider' => $existing->provider,
                'invoice_number' => $existing->invoice_number,
                'invoice_id' => $existing->invoice_id,
                'document_id' => $existing->id,
                'pdf_path' => $existing->pdf_path,
                'message' => 'A számla már elkészült korábban.'
            ]);
        }

        try {
            $result = $this->manager->createInvoice($code, $data);

            // Rekord mentése vagy frissítése
            $document = InvoiceDocument::updateOrCreate(
                ['order_id' => $data->orderId, 'provider' => $code],
                [
                    'invoice_number' => $result->invoiceNumber,
                    'invoice_id' => $result->invoiceId,
                    'status' => $result->success ? $result->status : InvoiceStatus::FAILED,
                    'gross_total' => $data->grossTotal,
                    'currency' => $data->currency,
                    'pdf_path' => $result->pdfPath,
                    'request_payload' => $data->toArray(),
                    'response_payload' => $result->rawResponse,
                    'error_code' => !$result->success ? ($result->extra['error_code'] ?? null) : null,
                    'error_message' => !$result->success ? $result->message : null,
                    'issued_at' => $result->success ? now() : null,
                ]
            );

            // A helyi bizonylat-sor azonosítója visszakerül az eredménybe, hogy a hívó
            // a rendeléshez ezt (és ne a szolgáltatói számlaszámot) tudja hozzákötni.
            $result->documentId = $document->id;

            if ($result->success) {
                event(new InvoiceCreated($document, $result));
            } else {
                event(new InvoiceFailed($data->orderId, $code, $document, $result));
            }

            return $result;
        } catch (\Throwable $e) {
            event(new InvoiceFailed($data->orderId, $code, null, null, $e->getMessage()));
            
            return new InvoiceCreateResult([
                'success' => false,
                'message' => $e->getMessage(),
                'status' => InvoiceStatus::FAILED,
                'provider' => $code
            ]);
        }
    }
}
