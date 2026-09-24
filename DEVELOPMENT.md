# Development

## Local application

Use PHP 8.3+ for the current Sylius 2.2.9 application, Composer, Symfony CLI,
Docker and Docker Compose. Asset builds were verified with Node **20.20.0** and
Yarn **1.22.22** (not Node 14).

`make help` lists the project commands. `make install REBUILD_DATABASE=1` runs
application setup, platform startup, Sylius setup and index population sequentially.
**Only run it with authorization against a disposable database**: it drops and
recreates that database, runs migrations, then loads fixtures. Confirm database
and Elasticsearch targets with the environment owner first. The guard does not
make the earlier setup steps read-only.

The generated consumer is `tests/Application`. `tests/setup-application.php` applies `dist/`,
excluding `.env*` and historical `dist/src/Migrations`. The repository setup and
recipe-endpoint scripts do not read or write `.env*`; provide environment values
through your normal local setup. Root and application `node_modules` are independent.

Useful non-resetting commands:

```bash
make up
make plugin.assets
make yarn.install
make sylius.assets
make sylius.cache.clear
make test.all
make stop
```

`make yarn.install` builds both application and plugin assets. `make es.reindex`
rebuilds indexes; use only against the intended test service. Do not use
`make reset` as a routine update: it removes the generated app and dependencies.
See [TESTING.md](TESTING.md) for verification scope and harness exceptions.

## Application compatibility matrix

The application workflow uses the same `tests/Application` path in each isolated
job, with these inputs:

| `SYLIUS_VERSION` | `SYLIUS_STANDARD_VERSION` | PHP | `SYMFONY_VERSION` |
| --- | --- | --- | --- |
| `2.0.18` | `~2.0.0` | 8.2 | `~6.4.0` |
| `2.1.16` | `~2.1.0` | 8.2 | `~7.4.0` |
| `2.2.9` | `~2.2.0` | 8.3 | `~7.4.0` |

`make install REBUILD_DATABASE=1`, `make test.all` and `make test.integration`
run in each job. The latter uses the application's own vendor and kernel, not
the root dependency set. CI uses Node 22 and Yarn 1.22.22. The Make setup selects
Symfony during the initial core Flex phase; the subsequent path-plugin require
uses `--no-plugins --minimal-changes` to preserve the locked Symfony versions.
Integration assertions check the installed Sylius, FrameworkBundle and Serializer
versions. All three jobs passed at `428a996`; see [TESTING.md](TESTING.md) for
the exact resolved versions, run links and verification boundaries.

The harness disables `allow-plugins.rector/extension-installer` before core
Composer installation for Standard 2.0.7/2.1.4. Integration tests use the native
weight `select` and a nonmatching search token rather than fuzzy ordinary words.
These are test-harness fixes, not changes to production search semantics.

Do not switch minors by resetting a user's existing database. Use isolated CI or
an authorized disposable environment; all local targets must be checked first.

## Testing the unpublished recipe

The independent recipe is `monsieurbiz/sylius-search-plugin/3.0` on the external
recipes checkout's `upgrade-sylius-search-plugin-2.x` branch. It is not published.
Do not use the `dist` overlay to prove recipe installation.

1. From the plugin root, generate the endpoint with `make recipe.endpoint` if
   `symfony-recipes` is a sibling checkout. Otherwise run
   `php tests/recipe-endpoint.php /path/to/symfony-recipes/monsieurbiz/sylius-search-plugin/3.0`.
2. Serve the generated `tests/RecipeEndpoint` directory with Symfony CLI on an
   available port, binding only to loopback:
   `symfony server:start --dir=tests/RecipeEndpoint --listen-ip=127.0.0.1 --port=8002 --no-tls -d`.
   Replace `8002` with your available port and use that same port below. Flex
   requires HTTP(S); a `file://` endpoint does not work.
3. In a fresh isolated checkout, create a **throwaway Sylius 2 consumer at
   `tests/Application`**, without running the `dist` setup. Do not overwrite an
   existing local application. In that consumer,
   configure the plugin checkout as a Composer path repository and put the local
   endpoint first, preserving the Sylius, Monsieur Biz and default endpoints:

   ```bash
   composer config repositories.search '{"type":"path","url":"../.."}'
   composer config extra.symfony.allow-contrib true
   composer config --no-plugins --json extra.symfony.endpoint '["http://127.0.0.1:8002/index.json","https://api.github.com/repos/Sylius/SyliusRecipes/contents/index.json?ref=flex/main","https://api.github.com/repos/monsieurbiz/symfony-recipes/contents/index.json?ref=flex/master","flex://defaults"]'
   composer config secure-http false
   composer require monsieurbiz/sylius-search-plugin:dev-upgrade-2.x --no-scripts
   composer recipes:install monsieurbiz/sylius-search-plugin --force -v
   composer config --unset secure-http
   php bin/console lint:container
   ```

   The relative path above assumes `tests/Application`. The temporary
   project-level `secure-http` exception is solely for this localhost endpoint;
   never set it globally or disable TLS verification. Restore it even if install
   fails, and remove the local endpoint when finished. `--force` is appropriate
   only for this disposable consumer.
4. Inspect `symfony.lock`: confirm recipe **3.0**, its artifact reference and
   modern Search imports. The updated manifest must not register an AutoMapper
   bundle. If an old recipe was applied first, review/remove Jane/AutoMapper
   registrations used only for Search and stale Search wiring before linting.
   Do not copy `dist` to make the check pass.
5. Stop the endpoint with `symfony server:stop --dir=tests/RecipeEndpoint`.

Recipe CI also uses only `tests/Application`, in its own job without `dist`.
`tests/RecipeEndpoint` is a small generated recipe-metadata helper, not another
consumer. The former local `RecipeApplication` is preserved outside this workspace
and is no longer part of the harness.

[Recipe CI passed](https://github.com/monsieurbiz/SyliusSearchPlugin/actions/runs/34883626278)
at plugin commit `428a99675f80b61b44a0496bcaf6bbf8778a6fb2`, using
`bourtitom/symfony-recipes` commit `09355e1c678922f04ea61fd5d8bda276ec542fb4`.
The 3.0 manifest has no AutoMapper registration; the independent `tests/Application`
consumer selected recipe 3.0 and passed container lint without `dist`.
The endpoint generator hashes recipe content, so the artifact ref changes with it.
[Recipe PR #20](https://github.com/monsieurbiz/symfony-recipes/pull/20) remains open
and unmerged. Switch the pin to reviewed upstream content after merge; successful
CI is not recipe publication.
