# Testing

Use the requirements and guarded disposable setup in [DEVELOPMENT.md](DEVELOPMENT.md).
The generated test application is `tests/Application`.

## Commands

```bash
make test.all
make test.integration
```

`make test.all` aggregates Composer validation, PHPStan, PHPMD, PHPUnit, JavaScript
tests, PHPSpec, PHP CS Fixer dry-run, YAML, schema, Twig and container validation.
Each is available separately as `make test.<name>`: `composer`, `phpstan`, `phpmd`,
`phpunit`, `javascript`, `phpspec`, `phpcs`, `yaml`, `schema`, `twig`, `container`.
PHP/container/template/schema checks require installed dependencies and, where
applicable, the configured application/database. `make test.javascript` uses
Node's test runner without external services.

`make test.integration` is separate from the aggregate. It runs
[`ApplicationTest`](tests/Integration/ApplicationTest.php) using
[`tests/bootstrap-integration.php`](tests/bootstrap-integration.php) and the
generated application's own vendor, kernel, fixtures, database and Elasticsearch.
These tests write data and indexes: use only an authorized disposable environment.

## Verified checkpoint

### Local PHPUnit expansion

After the migration checkpoint below, local PHPUnit coverage was expanded around
request boundaries, query/post filters, aggregations, response building, request
selection, mapping readers/YAML merging, documentable configuration, contexts,
index lifecycle, product events and message handlers. Fresh local verification on
PHP **8.3.30**:

| Command | Result |
| --- | --- |
| `make test.composer` | passed (`composer.json is valid`) |
| `make test.phpstan` | passed (134 files, no errors) |
| `make test.phpmd` | passed (0 violations) |
| `make test.phpunit` | passed (**206 tests, 612 assertions**) |
| `make test.javascript` | passed (**6 tests**) |
| `make test.phpspec` | passed (0 specs/examples) |
| `make test.phpcs` | passed (0 files to fix) |
| `make test.yaml` | passed (14 YAML files) |
| `make test.twig` | passed (30 Twig files) |
| `make test.container` | passed |

`make test.twig` initially used a stale generated application cache that still
referenced the removed third-party AutoMapper bundle; `make sylius.cache.clear`
with the no-debug cache cleared resolved it. `make test.schema`,
`make test.integration` and the three-minor CI matrix were not rerun locally in
this pass because they require the disposable application database/index state.

### Remote migration checkpoint

Independent QA confirmed **all six remote checks passed** for plugin commit
`428a99675f80b61b44a0496bcaf6bbf8778a6fb2`. The final audit approved the public
mapping API, three-minor runtime coverage and single-application recipe scope with
no high-confidence blocker. The existing best-effort indexing contract is unchanged.

### Application matrix

[Run 34883626305](https://github.com/monsieurbiz/SyliusSearchPlugin/actions/runs/34883626305)
tested these actual installed versions at that exact commit:

| Sylius | PHP | FrameworkBundle | Serializer | Successful job |
| --- | --- | --- | --- | --- |
| 2.0.18 | 8.2.33 | 6.4.45 | 6.4.45 | [104108720793](https://github.com/monsieurbiz/SyliusSearchPlugin/actions/runs/34883626305/job/104108720793) |
| 2.1.16 | 8.2.33 | 7.4.18 | 7.4.18 | [104108720725](https://github.com/monsieurbiz/SyliusSearchPlugin/actions/runs/34883626305/job/104108720725) |
| 2.2.9 | 8.3.33 | 7.4.18 | 7.4.18 | [104108720879](https://github.com/monsieurbiz/SyliusSearchPlugin/actions/runs/34883626305/job/104108720879) |

**Every job completed**, rather than skipped, `make install REBUILD_DATABASE=1`,
`make test.all` and `make test.integration`. Each isolated job used the same
`tests/Application` path. Identical test counts were recorded in all three:

- Root PHPUnit: **20 tests, 69 assertions**, including populated nested product
  fields/prices, DTO JSON, configured target subclasses and unrelated API groups.
- JavaScript unit tests: **5 passed**.
- Native KernelBrowser integration: **6 tests, 55 assertions**.

Integration assertions check the exact Sylius version and the requested Symfony
constraints against the application's own FrameworkBundle and Serializer. Coverage
includes rendered shop search, POST/redirect, instant search, taxon pages, filter
controls and the selected limit; a received reindex message handler; HTTP 200 on
the native shop taxon API route with channel context; admin attribute/option
weight selections saved, reloaded and restored; and Settings collection rendering
plus default limits saved, reloaded and restored.

### Independent recipe

[Run 34883626278](https://github.com/monsieurbiz/SyliusSearchPlugin/actions/runs/34883626278)
passed at the same plugin commit, using `bourtitom/symfony-recipes` source commit
`09355e1c678922f04ea61fd5d8bda276ec542fb4`. Its 3.0 manifest has no AutoMapper
registration. A separate CI job created only `tests/Application`, without `dist`,
selected recipe **3.0** and passed container lint. `tests/RecipeEndpoint` is only
the metadata helper, not another test application.

[Plugin PR #233](https://github.com/monsieurbiz/SyliusSearchPlugin/pull/233) and
[recipe PR #20](https://github.com/monsieurbiz/symfony-recipes/pull/20) remain open
and unmerged. Passing CI and audit approval do not imply publication.

## Schema validation caveat

`make test.schema` first runs `app:search:validate-mappings` over **all mappings**.
It permits exactly one diagnosed upstream error: standalone
`Sylius\Component\Shipping\Model\ShipmentUnit#shipment` points through
`App\Entity\Shipping\Shipment#units` to `App\Entity\Order\OrderItemUnit`.
The exception applies only when `ShipmentUnit` is a mapped superclass with **no
concrete descendants** and the error matches exactly. It prints a warning;
every other mapping error fails the command. Native Doctrine database schema
validation then runs with `--skip-mapping`.

This is **not an unqualified all-mappings pass**. `make test.schema.upstream`
runs the unfiltered Doctrine validator and exposes the known failure. See
[`ValidateSearchMappingsCommand`](dist/src/Command/ValidateSearchMappingsCommand.php).

The harness also has a narrow [Gedmo TypeInfo fallback](dist/src/Compatibility/LogEntryTypeExtractor.php)
for the `Loggable|object` bound; API routes and validation remain enabled. It is
not shipped plugin functionality. Consumers on the same upstream combination
may encounter this error independently; see [the upgrade guide](UPGRADE-SYLIUS-2.md).

## Verification boundaries

- These results prove the listed combinations, not all Sylius patches or all
  PHP/Symfony combinations, consumer customizations or production datasets.
- KernelBrowser does not execute browser JavaScript. Five JavaScript unit tests
  are not real-browser coverage on all three minors. Earlier local browser QA
  predates this checkpoint and is not a new cross-minor browser pass.
- A received-message handler test does not prove transport delivery,
  outer-transaction rollback/commit behavior or durable publication. A real
  Sylius 1 production-data migration remains unverified. Preserve the
  [best-effort publication and recovery gates](UPGRADE-SYLIUS-2.md#indexing-and-deployment-safety).
- Local database drift (`t0.position`) and hostname/port differences were preserved;
  the user's database was **not reset**. No new full local end-to-end pass is
  claimed. Use isolated CI results above, not historical local DB/index counts.
