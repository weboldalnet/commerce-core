<?php

namespace Weboldalnet\CommerceCore\Support;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A commerce-csomagok KÖZÖS adatbázis-kapcsolata.
 *
 * Többnyelvű core-projektben minden nyelvnek saját adatbázisa van, és a `database.default` kérésenként a
 * látogató (adminban a kiválasztott) nyelvére vált. A fizetési tranzakciók, számlák, szállítmányok, kuponok,
 * vásárlói fiókok és a szolgáltatói beállítások viszont nyelvtől függetlenek: mindig az alapnyelvi adatbázisban
 * élnek — különben egy angol rendelés fizetése a magyar adatbázisba érkező visszahíváskor nem lenne megtalálható.
 * Ezt a kapcsolatot használja minden commerce-modell (`UsesCommerceConnection`, a provider-csomagokban is), és
 * ezen kell nyitni minden nyers lekérdezést és tranzakciót (`db()`).
 *
 * Forrás: `config('commerce-core.connection')`, különben a core `App\Helpers\Lang::DEFAULT_DB` (ugyanígy rögzíti a
 * core a saját közös adatait). Ha egyik sincs (pl. csomagteszt), az alapértelmezett kapcsolat. Egynyelvű
 * projektben ez ugyanaz az adatbázis, mint eddig — a működés nem változik.
 */
final class CommerceConnection
{
    private static bool $resolved = false;

    private static ?string $name = null;

    public static function name(): ?string
    {
        if (!self::$resolved) {
            $name = config('commerce-core.connection');

            if (!$name && class_exists(\App\Helpers\Lang::class) && defined(\App\Helpers\Lang::class . '::DEFAULT_DB')) {
                $name = \App\Helpers\Lang::DEFAULT_DB;
            }

            // Csak létező kapcsolatra rögzítünk; ismeretlen névnél marad az alapértelmezett.
            self::$name = $name && config('database.connections.' . $name) ? $name : null;
            self::$resolved = true;
        }

        return self::$name;
    }

    public static function db(): ConnectionInterface
    {
        return DB::connection(self::name());
    }

    public static function schema(): Builder
    {
        return Schema::connection(self::name());
    }

    /** Tesztekhez: a kapcsolatnév újraolvasása a configból. */
    public static function flush(): void
    {
        self::$resolved = false;
        self::$name = null;
    }
}
