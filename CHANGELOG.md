## [1.0.0] - 2026-01-06
### Alap teszt - Commit #2

## [1.0.1] - 2026-01-06
### Fájlok feltöltésének tesztelése

## [Unreleased]
### Ütemezett fizetés-egyeztetés
- Új parancs: `commerce:reconcile-payments` — a függő online tranzakciók (Barion, SimplePay, Stripe: minden provider,
  amelynek van `queryStatus()`-a) állapotát lekérdezi, és a `PaymentCallbackProcessor`-ral feldolgozza (elmaradt
  IPN / webhook, lejárt vagy megszakított fizetés).
- A csomag maga ütemezi a Laravel ütemezőjében (alapból 15 percenként, `withoutOverlapping`); a 10 percnél frissebb
  és a 2 napnál régebbi tranzakciókat kihagyja. Config: `commerce-core.reconcile` (`COMMERCE_RECONCILE_SCHEDULE`,
  `_DAYS`, `_MIN_AGE`); a publikált configba nem kell felvenni (a csomag alapértéke érvényes).
