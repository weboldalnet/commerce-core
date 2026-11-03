<?php

namespace Weboldalnet\CommerceCore\Models\Concerns;

use Weboldalnet\CommerceCore\Support\CommerceConnection;

/**
 * A commerce-modell mindig a commerce közös (alapnyelvi) adatbázisát használja, nem a kérés nyelvéét
 * — lásd CommerceConnection. A provider-csomagok (Barion, SimplePay, Stripe, GLS, Foxpost, Számlázz.hu,
 * kuponok, vásárlói fiókok) modelljei is ezt használják. Egy kifejezetten beállított `$connection`
 * (pl. `Model::on()`) elsőbbséget kap.
 */
trait UsesCommerceConnection
{
    public function getConnectionName()
    {
        return $this->connection ?? CommerceConnection::name();
    }
}
