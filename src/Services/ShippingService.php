<?php

namespace Weboldalnet\CommerceCore\Services;

use Weboldalnet\CommerceCore\Managers\ShippingManager;
use Weboldalnet\CommerceCore\Data\ShipmentRequestData;
use Weboldalnet\CommerceCore\Data\ShipmentCreateResult;
use Weboldalnet\CommerceCore\Data\ShippingRateRequestData;
use Weboldalnet\CommerceCore\Models\Shipment;
use Weboldalnet\CommerceCore\Events\ShipmentCreated;
use Weboldalnet\CommerceCore\Events\ShipmentFailed;
use Weboldalnet\CommerceCore\Status\ShippingStatus;

/**
 * A szállítmány létrehozás perzisztálással és eseményekkel.
 * A számlázási oldal InvoiceService-ének párja – korábban ilyen nem volt,
 * ezért a commerce_shipments táblába soha nem került adat.
 */
class ShippingService
{
    protected $manager;

    public function __construct(ShippingManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Szállítási díj kalkuláció. Hibát nem dob: ha a provider nem elérhető
     * vagy elszáll, null-t adunk vissza, hogy a pénztár működőképes maradjon.
     */
    public function calculate($code, ShippingRateRequestData $data)
    {
        try {
            return $this->manager->calculate($code, $data);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('ShippingService: díjkalkuláció sikertelen.', [
                'provider' => $code,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Szállítmány létrehozása a providernél, majd perzisztálás és event.
     */
    public function createShipment($code, ShipmentRequestData $data): ShipmentCreateResult
    {
        // Idempotencia: ha a rendeléshez már van élő szállítmány, nem hozunk létre újat.
        $existing = Shipment::where('order_id', $data->orderId)
            ->whereIn('status', [ShippingStatus::PREPARED, ShippingStatus::SHIPPED, ShippingStatus::DELIVERED])
            ->first();

        if ($existing) {
            return new ShipmentCreateResult([
                'success' => true,
                'status' => $existing->status,
                'provider' => $existing->provider,
                'tracking_number' => $existing->tracking_number,
                'tracking_url' => $existing->tracking_url,
                'label_path' => $existing->label_path,
                'shipment_id' => $existing->id,
                'message' => 'A szállítmány már korábban létrejött.',
            ]);
        }

        try {
            $result = $this->manager->createShipment($code, $data);
        } catch (\Throwable $e) {
            event(new ShipmentFailed($data->orderId, $code, null, null));

            return new ShipmentCreateResult([
                'success' => false,
                'status' => ShippingStatus::FAILED,
                'provider' => $code,
                'message' => $e->getMessage(),
            ]);
        }

        $shipment = Shipment::updateOrCreate(
            ['order_id' => $data->orderId, 'provider' => $code],
            [
                'shipping_method' => $data->shippingMethod,
                'tracking_number' => $result->trackingNumber,
                'tracking_url' => $result->trackingUrl,
                'label_path' => $result->labelPath,
                'status' => $result->success ? ($result->status ?: ShippingStatus::PREPARED) : ShippingStatus::FAILED,
                'request_payload' => $data->toArray(),
                'response_payload' => $result->rawResponse,
            ]
        );

        // A helyi sor azonosítója visszakerül az eredménybe, hogy a hívó ezt
        // (és ne a futárszolgálati csomagszámot) kösse a rendeléshez.
        $result->shipmentId = $shipment->id;

        if ($result->success) {
            event(new ShipmentCreated($data->orderId, $code, $shipment, $result));
        } else {
            event(new ShipmentFailed($data->orderId, $code, $shipment, $result));
        }

        return $result;
    }

    /**
     * Nyomkövetési URL egy csomagszámhoz.
     */
    public function getTrackingUrl($code, $trackingNumber)
    {
        try {
            return $this->manager->getTrackingUrl($code, $trackingNumber);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
