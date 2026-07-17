# weboldalnet/commerce-core

Moduláris commerce absztrakciós réteg online rendelési, fizetési, számlázási és szállítási folyamatokhoz. Laravel 11+ kompatibilis, PHP 8.2+ szükséges.

---

## Rövid cél

A `commerce-core` package egy szolgáltatófüggetlen, moduláris absztrakciós réteget biztosít az alábbi területekre:

- Fizetési provider-ek (payment)
- Számlázási provider-ek (invoice)
- Szállítási provider-ek (shipping)
- Provider registry / manager
- Config alapú provider engedélyezés
- Payment transaction logolás
- Invoice document nyilvántartás
- Shipment nyilvántartás
- Provider API logolás
- Webhook / callback feldolgozás (idempotens)
- Rendelés státusz mapping objektumok
- Event-driven működés

A konkrét külső szolgáltató implementációk (SimplePay, Számlázz.hu, GLS, MPL stb.) külön package-ekben készülnek el.

---

## Telepítés

```bash
composer require weboldalnet/commerce-core
```

### Config publikálása

```bash
php artisan vendor:publish --tag=commerce-core-config
```

### Migrációk futtatása

```bash
php artisan migrate
```

Vagy ha manuálisan szeretnéd publikálni a migrációkat:

```bash
php artisan vendor:publish --tag=commerce-core-migrations
php artisan migrate
```

---

## Config példa

A `config/commerce-core.php` fájlban:

```php
return [
    'payments' => [
        'default' => 'cod',
        'enabled' => ['cod', 'bank_transfer', 'manual'],
        'providers' => [
            'cod' => [
                'driver' => \Weboldalnet\CommerceCore\Providers\Payment\CashOnDeliveryPaymentProvider::class,
                'name' => 'Utánvét',
                'enabled' => true,
            ],
            // Külső provider (csak ha a package telepítve van):
            // 'simplepay' => [
            //     'driver' => \Weboldalnet\SimplePayProvider\SimplePayPaymentProvider::class,
            //     'name' => 'SimplePay',
            //     'enabled' => false,
            // ],
        ],
    ],
    'invoice' => [
        'default' => 'manual',
        'providers' => [
            'manual' => [
                'driver' => \Weboldalnet\CommerceCore\Providers\Invoice\ManualInvoiceProvider::class,
                'enabled' => true,
            ],
        ],
    ],
    'shipping' => [
        'default' => 'flat_rate',
        'enabled' => ['flat_rate', 'pickup'],
        'providers' => [
            'flat_rate' => [
                'driver' => \Weboldalnet\CommerceCore\Providers\Shipping\FlatRateShippingProvider::class,
                'enabled' => true,
                'rate' => 1490,
                'currency' => 'HUF',
                'free_above' => 20000,
            ],
        ],
    ],
    'provider_logs' => ['enabled' => true],
    'auto_invoice' => ['enabled' => false],
    'auto_shipping' => ['enabled' => false],
];
```

---

## Provider Interface-ek

### PaymentProviderInterface

```php
interface PaymentProviderInterface {
    public function getCode();
    public function getName();
    public function isOnline();
    public function createPayment(PaymentRequestData $data);
    public function handleReturn(array $payload);
    public function handleCallback(array $payload);
    public function refund(PaymentRefundData $data);
}
```

### InvoiceProviderInterface

```php
interface InvoiceProviderInterface {
    public function getCode();
    public function getName();
    public function createInvoice(InvoiceRequestData $data);
    public function voidInvoice($invoiceNumber);
    public function downloadPdf($invoiceNumber);
}
```

### ShippingProviderInterface

```php
interface ShippingProviderInterface {
    public function getCode();
    public function getName();
    public function calculate(ShippingRateRequestData $data);
    public function createShipment(ShipmentRequestData $data);
    public function getTrackingUrl($trackingNumber);
}
```

---

## Payment flow példa

```php
use Weboldalnet\CommerceCore\Services\CommerceOrderProcessor;

$processor = app(CommerceOrderProcessor::class);

$result = $processor->process([
    'order_id' => $order->id,
    'payment_method' => 'cod',
    'amount' => $order->total_price,
    'currency' => 'HUF',
    'customer_name' => $order->customer_name,
    'customer_email' => $order->customer_email,
]);

if ($result['requiresRedirect']) {
    return redirect($result['redirectUrl']);
}

// Rendelés státusz frissítés
$order->update(['status' => $result['orderStatusSuggestion']]);
```

---

## Invoice flow példa

```php
use Weboldalnet\CommerceCore\Managers\InvoiceManager;
use Weboldalnet\CommerceCore\Data\InvoiceRequestData;

$manager = app(InvoiceManager::class);

$data = InvoiceRequestData::fromArray([
    'order_id' => $order->id,
    'order_number' => $order->order_number,
    'customer_name' => $order->customer_name,
    'gross_total' => $order->total_price,
    'currency' => 'HUF',
    'items' => $items,
]);

$result = $manager->createInvoice('manual', $data);

if ($result->success) {
    // InvoiceDocument mentve
}
```

---

## Shipping flow példa

```php
use Weboldalnet\CommerceCore\Managers\ShippingManager;
use Weboldalnet\CommerceCore\Data\ShipmentRequestData;
use Weboldalnet\CommerceCore\Data\ShippingRateRequestData;

$manager = app(ShippingManager::class);

// Díj kalkuláció
$rateResult = $manager->calculate('flat_rate', ShippingRateRequestData::fromArray([
    'cart_total' => $order->total_price,
    'currency' => 'HUF',
]));

// Szállítmány létrehozása
$shipmentResult = $manager->createShipment('flat_rate', ShipmentRequestData::fromArray([
    'order_id' => $order->id,
    'order_number' => $order->order_number,
    'customer_name' => $order->customer_name,
    'shipping_data' => $order->shipping_data,
]));
```

---

## Új provider írása

### Payment provider

```php
use Weboldalnet\CommerceCore\Contracts\PaymentProviderInterface;
use Weboldalnet\CommerceCore\Data\PaymentRequestData;
use Weboldalnet\CommerceCore\Data\PaymentCreateResult;
use Weboldalnet\CommerceCore\Data\PaymentRefundData;
use Weboldalnet\CommerceCore\Data\PaymentRefundResult;
use Weboldalnet\CommerceCore\Data\PaymentCallbackResult;

class MyPaymentProvider implements PaymentProviderInterface
{
    public function getCode() { return 'my_provider'; }
    public function getName() { return 'My Payment Provider'; }
    public function isOnline() { return true; }

    public function createPayment(PaymentRequestData $data)
    {
        // API hívás, majd:
        return PaymentCreateResult::success([
            'provider' => $this->getCode(),
            'redirectUrl' => 'https://payment-gateway.com/pay/...',
            'providerTransactionId' => 'TXN_123',
        ]);
    }

    public function handleReturn(array $payload) { /* ... */ }
    public function handleCallback(array $payload) { /* ... */ }
    public function refund(PaymentRefundData $data) { /* ... */ }
}
```

Majd a configban:

```php
'my_provider' => [
    'driver' => MyPaymentProvider::class,
    'name' => 'My Payment Provider',
    'enabled' => true,
],
```

---

## Webshop modul csatlakoztatása

A `webshop-ai-default` (vagy saját) webshop modul így csatlakozhat a commerce-core-hoz:

1. A checkout után hívd meg a `CommerceOrderProcessor::process()` metódust.
2. Hallgass a `PaymentSucceeded`, `PaymentFailed`, `PaymentCancelled` eventekre és frissítsd a rendelés státuszt.
3. A `PaymentSucceeded` event után indítsd el a számlázási és szállítási folyamatot (queue-ban).

```php
// EventServiceProvider-ban:
use Weboldalnet\CommerceCore\Events\PaymentSucceeded;

protected $listen = [
    PaymentSucceeded::class => [
        \App\Listeners\UpdateOrderStatus::class,
        \App\Listeners\TriggerInvoice::class,
        \App\Listeners\TriggerShipping::class,
    ],
];
```

---

## Biztonsági megjegyzések

- **Az összeget szerver oldalon számold**, soha ne fogadd el a klienstől.
- **Callback signature validáció** a provider feladata - az online providerek implementálják a `handleCallback`-ban.
- **Idempotencia**: a `PaymentCallbackProcessor` gondoskodik arról, hogy egy callback csak egyszer kerüljön feldolgozásra.
- **Érzékeny adatok logolás**: a `ProviderLogger` automatikusan szűri a `token`, `secret`, `password`, `api_key`, `card_number` stb. mezőket.
- **Számlázás és szállítás queue-ban fusson**: a `PaymentSucceeded` event listenerei queue-d jobok legyenek, hogy ne lassítsák a callback választ.
- **DB lock**: ha a callback feldolgozásnál versenyhelyzet lehetséges, a webshop integráció `DB::transaction()` + `lockForUpdate()` mellett hívja a `PaymentCallbackProcessor::process()`-t.

---

## Státusz konstansok

```php
use Weboldalnet\CommerceCore\Status\PaymentStatus;
use Weboldalnet\CommerceCore\Status\OrderStatus;
use Weboldalnet\CommerceCore\Status\InvoiceStatus;
use Weboldalnet\CommerceCore\Status\ShippingStatus;

PaymentStatus::PAID    // 'paid'
PaymentStatus::PENDING // 'pending'
OrderStatus::PROCESSING // 'processing'
InvoiceStatus::INVOICED // 'invoiced'
ShippingStatus::SHIPPED // 'shipped'
```

---

## Adatbázis táblák

- `commerce_payment_transactions` - fizetési tranzakciók
- `commerce_invoice_documents` - számla dokumentumok
- `commerce_shipments` - szállítmányok
- `commerce_provider_logs` - provider API logok
