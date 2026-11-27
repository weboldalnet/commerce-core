## [1.0.0] - 2026-01-06
### Alap teszt - Commit #2

## [1.0.1] - 2026-01-06
### Fájlok feltöltésének tesztelése

## [1.0.2 – 1.0.9] - 2026-11-03
A korábban [Unreleased] alatt gyűjtött, az 1.0.2–1.0.9 kiadásokban már megjelent tételek.

### Ütemezett fizetés-egyeztetés
- Új parancs: `commerce:reconcile-payments` — a függő online tranzakciók (Barion, SimplePay, Stripe: minden provider,
  amelynek van `queryStatus()`-a) állapotát lekérdezi, és a `PaymentCallbackProcessor`-ral feldolgozza (elmaradt
  IPN / webhook, lejárt vagy megszakított fizetés).
- A csomag maga ütemezi a Laravel ütemezőjében (alapból 15 percenként, `withoutOverlapping`); a 10 percnél frissebb
  és a 2 napnál régebbi tranzakciókat kihagyja. Config: `commerce-core.reconcile` (`COMMERCE_RECONCILE_SCHEDULE`,
  `_DAYS`, `_MIN_AGE`); a publikált configba nem kell felvenni (a csomag alapértéke érvényes).

## [1.0.10] - 2026-11-27
### Javítva: friss telepítés PostgreSQL 9.4-en
- A `2026_05_29_COMMERCECORE_create_commerce_tables` migráció `CREATE INDEX IF NOT EXISTS`-et használt, amit a
  PostgreSQL csak 9.5-től ismer: friss adatbázison (új projekt, teszt-adatbázis) a 9.4-es szervereken itt állt meg
  a teljes `migrate` („syntax error at or near NOT”). Az indexek most DO-blokkal, létezés-ellenőrzéssel jönnek létre.
  A már migrált adatbázisokat nem érinti.
