# Search plugin 3.0 — Sylius 2 migration

This is the owner-approved Sylius **1.14.9 → 2** migration, not the historical
plugin 2.0 release documented in [UPGRADE-2.0.md](UPGRADE-2.0.md). The owner chose
**3.0** as the next plugin release. Implementation is on `upgrade-2.x`; the
external recipe is prepared under `monsieurbiz/sylius-search-plugin/3.0` on
`upgrade-sylius-search-plugin-2.x`. Independent final QA approved the public-mapper,
three-minor application and single-application recipe scope at `428a996`, with no
high-confidence blocker. Plugin PR #233 and recipe PR #20 remain open and unmerged;
publication is pending.

## Platform

The current root lock resolves Sylius **2.2.9**, Symfony **7.4**, Doctrine ORM
**3.7.1** and Settings **2.0.4**. Search no longer requires JoliCode AutoMapper.
Composer permits Sylius `~2.0` and PHP `^8.2`, but Sylius 2.2 requires **PHP 8.3+**.
Application CI passed on 2.0.18 / PHP 8.2.33 / Symfony 6.4.45, 2.1.16 / PHP 8.2.33 /
Symfony 7.4.18 and 2.2.9 / PHP 8.3.33 / Symfony 7.4.18 (FrameworkBundle and Serializer).
All jobs use `tests/Application` and completed installation, the aggregate checks
and native integration tests. See [the exact evidence and limits](TESTING.md);
this does not establish compatibility for every dependency combination.

## Consumer migration checklist

1. Back up the application database and indexes. Review existing customizations
   before updating dependencies. For a local preview require
   `monsieurbiz/sylius-search-plugin:dev-upgrade-2.x` from the checkout; use `^3.0`
   only after release. The new recipe is **not published**: use the
   [manual installation](README.md#installation) for now.
2. Remove Jane/AutoMapper bundle registrations installed solely for Search,
   including `AutoMapper\Symfony\Bundle\AutoMapperBundle`. Retain dependencies
   needed by unrelated application features. Jane still generates the DTOs and
   normalizers in `generated/`; the plugin now owns runtime document mapping.
3. Change Search imports to `@MonsieurBizSyliusSearchPlugin/config/config.yaml`
   and `@MonsieurBizSyliusSearchPlugin/config/routing.yaml`. Search's resource
   directories are now root `config/`, `templates/`, `translations/`, `public/`;
   PHP stays in `src/`, with unchanged PHP/DTO namespaces.
4. **Do not apply that path rewrite to Settings 2.0.4**: it still uses
   `Resources/config`. Its published 2.0 recipe matches that installed version;
   the separate Settings 2.1 recipe targets another layout.
5. Port old shop UI-event integrations and recipe-copied admin attribute/option
   full-form overrides to the [Twig Hooks](config/twig_hooks.yaml). Remove stale
   overrides only after preserving application customizations; install bundle
   assets rather than copying all plugin templates into the application.
6. Keep `SearchableInterface` and `SearchableTrait` on application product
   attributes/options. Generate and review migrations against your own schema.
   Released `dist/src/Migrations` were not changed; the new
   `dist/migrations/Version20260914091137.php` is a **test-application delta**,
   not a migration to copy into existing shops.
7. Port custom mappings as below, clear the application cache,
   migrate, repopulate indexes and verify documents and UI before
   restarting workers.

### Custom mappings

Use the public `Mapper\DocumentMapperInterface::map(object $source, string $targetClass): object`.
Strategies implement `DocumentMappingInterface` (adding `supports()`) and use the
`monsieurbiz.search.document_mapping` tag. The dispatcher selects the first match.
Replace old Jane configurations and intermediate AutoMapper event listeners with
the [product decorator](docs/add_custom_values.md) or [custom entity mapping](docs/add_custom_entities.md).

**Breaking in 3.0:** extra reflected/matching-name fields now require explicit
mapping. `AutoMapper\ConfigurationInterface`, `Configuration` and
`automapper_classes` remain for BC and preserve configured source/target selection,
not automatic property discovery. Product domain logic stays in `ProductMapper`;
its explicitly injected `NestedProductMapper` writes nested fields through public
setters, preserving Jane initialization flags and omitting null optional values.
Search mapping ignores API serializer metadata entirely, without changing API
groups. No vendor-internal API, private mapper registry or self-referencing
dispatcher is required. DTO JSON and unrelated API-group regressions have unit coverage.

## Indexing and deployment safety

Route names/methods, query parameters, document IDs/fields, index prefixes and
locale aliases, settings, extension tags and Messenger message/transport names
are intended to remain stable. This is a preservation target, not proof of every
consumer integration; [TESTING.md](TESTING.md) distinguishes tested and pending work.

Taxon-related messages are collected in memory during `onFlush` and dispatched
in `postFlush` after clearing the pending list. Numeric IDs and serialized
payloads are retained. **`postFlush` does not guarantee that an outer transaction
committed.** Production upgrade data and outer-transaction behavior remain untested.

Publication deliberately retains the existing **best-effort, non-durable
contract**, not an outbox or transactional guarantee. A transport failure after
DB commit can leave changed data without a queued reindex message. On dispatch
failure the subscriber logs at ERROR with the batch's affected taxon IDs, the
exception and the explicit `monsieurbiz:search:populate` reconciliation command,
then rethrows. A mid-batch failure may leave earlier messages published; the
pending list stays cleared, with no implicit replay/duplicate on another
`postFlush`. Regression coverage proves that reset behavior, not durable delivery.

Full rebuild mapping failures now abort rather than publish a partially mapped
index. Drain/stop workers, snapshot data/indexes, migrate, rebuild and verify,
then resume workers. Preserve snapshots for rollback: the rebuild lifecycle
purges old physical indexes. Use Elasticsearch 7.x with ICU and phonetic
analysis plugins; CI pins 7.17.29. Earlier local 7.16.3 checks do not verify the
latest mapper changes.

### Recovery after a publication failure

1. Stop search workers and restore the transport. Review the ERROR log and the
   affected batch; do not assume rethrowing rolled back committed database data.
2. Snapshot the database and indexes before rebuilding, and ensure application
   writes are controlled so the reconciliation can be checked consistently.
3. Run a full `php bin/console monsieurbiz:search:populate` in the consumer
   (`make es.reindex` in this repository's test harness).
4. Compare DB/index document IDs and counts for the relevant channels/locales,
   then verify search results and sorting before resuming `async_search` workers.

The rebuild can be rerun idempotently to reconcile current database content; it
does not preserve physical index identities or provide rollback. Keep snapshots:
the existing rebuild lifecycle purges old indexes. This recovery procedure does
not close the non-durable publication gap.

## Known upstream/harness limitations

- Symfony TypeInfo 7.4 rejects Gedmo's `Loggable|object` bound in the installed
  combination. The test-only `dist/src/Compatibility/LogEntryTypeExtractor.php`
  falls back to PHPDoc extraction only for the `AbstractLogEntry` hierarchy and
  the exact union exception. API routes and validation remain enabled. Consumers
  may hit this independently: diagnose their installed versions and review an
  application-local workaround or upstream fix; the plugin does not ship this
  decorator or silently disable validation.
- `make test.schema` explicitly warns about one exact unused standalone Shipping
  `ShipmentUnit` superclass diagnostic, only with no concrete descendants. It
  validates all other mappings and then the native database schema. The
  unfiltered `make test.schema.upstream` still exposes the known failure. This
  is **not** an all-mappings pass; see [the exact scope](TESTING.md#schema-validation-caveat).
- The test harness uses native `assets:install`, avoiding ThemeBundle's legacy
  installer dependency on the removed Sylius UI placeholder.

See [TESTING.md](TESTING.md) for the verified commit and remaining validation
boundaries, and [DEVELOPMENT.md](DEVELOPMENT.md#testing-the-unpublished-recipe)
for independent Flex testing. Neither application nor recipe CI success is a
published-release guarantee.
