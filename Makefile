.DEFAULT_GOAL := help
SHELL=/bin/bash
APP_DIR=tests/Application
export SYLIUS_VERSION?=2.2.9
SYLIUS_STANDARD_VERSION?=~2.2.0
export SYMFONY_VERSION?=~7.4.0
SYMFONY=cd ${APP_DIR} && symfony
COMPOSER=symfony composer
CONSOLE=${SYMFONY} console
export COMPOSE_PROJECT_NAME=search
export COMPOSE_FILE=$(abspath ${APP_DIR}/docker-compose.yaml):$(abspath ${APP_DIR}/docker-compose.override.yaml)
export MIGRATIONS_NAMESPACE=DoctrineMigrations
export USER_UID=$(shell id -u)
PLUGIN_NAME=sylius-${COMPOSE_PROJECT_NAME}-plugin
COMPOSE=docker compose --project-name search -f docker-compose.yaml -f docker-compose.override.yaml
YARN=yarn

###
### DEVELOPMENT
### ¯¯¯¯¯¯¯¯¯¯¯

install: ## Install the plugin in the disposable test application
	${MAKE} application
	${MAKE} platform
	${MAKE} sylius
	${MAKE} es.reindex
.PHONY: install

up: docker.up server.start ## Up the project (start docker, start symfony server)
stop: server.stop docker.stop ## Stop the project (stop docker, stop symfony server)
down: server.stop docker.down ## Down the project (removes docker containers, stop symfony server)

reset: ## Stop docker and remove dependencies
	${MAKE} docker.down || true
	rm -rf ${APP_DIR}/node_modules ${APP_DIR}/yarn.lock
	rm -rf ${APP_DIR}
	rm -rf vendor composer.lock
.PHONY: reset

dependencies: ## Setup the dependencies
	${COMPOSER} update --with sylius/sylius="${SYLIUS_VERSION}" --no-interaction --no-scripts --no-plugins --prefer-dist
	${MAKE} yarn.install
.PHONY: dependencies

.php-version: .php-version.dist
	rm -f .php-version
	ln -s .php-version.dist .php-version

php.ini: php.ini.dist
	rm -f php.ini
	ln -s php.ini.dist php.ini

composer.lock: composer.json
	${COMPOSER} install --no-scripts --no-plugins

yarn.install: ## Build independent application and plugin assets
	${YARN} --cwd ${APP_DIR} install --non-interactive
	${YARN} --cwd ${APP_DIR} build:prod
	${MAKE} plugin.assets
.PHONY: yarn.install

plugin.assets: ## Build distributable plugin assets
	${YARN} install --non-interactive
	${YARN} build
.PHONY: plugin.assets

###
### TEST APPLICATION
### ¯¯¯¯¯

application: .php-version php.ini ${APP_DIR}
	${MAKE} setup_application
.PHONY: application

${APP_DIR}:
	${COMPOSER} create-project --no-interaction --prefer-dist --no-scripts --no-progress --no-install sylius/sylius-standard="${SYLIUS_STANDARD_VERSION}" ${APP_DIR}

setup_application:
	(cd ${APP_DIR} && ${COMPOSER} config repositories.plugin '{"type": "path", "url": "../../", "options": {"versions": {"monsieurbiz/sylius-search-plugin": "dev-upgrade-2.x"}}}')
	(cd ${APP_DIR} && ${COMPOSER} config extra.symfony.allow-contrib true)
	(cd ${APP_DIR} && ${COMPOSER} config extra.symfony.docker false)
	(cd ${APP_DIR} && ${COMPOSER} config --no-plugins allow-plugins.rector/extension-installer false)
	(cd ${APP_DIR} && ${COMPOSER} config extra.symfony.require "${SYMFONY_VERSION}")
	(cd ${APP_DIR} && ${COMPOSER} require --no-update --no-plugins --no-scripts sylius/sylius="${SYLIUS_VERSION}")
	(cd ${APP_DIR} && ${COMPOSER} config --no-plugins --json extra.symfony.endpoint '["https://api.github.com/repos/Sylius/SyliusRecipes/contents/index.json?ref=flex/main","https://api.github.com/repos/monsieurbiz/symfony-recipes/contents/index.json?ref=flex/master","flex://defaults"]')
	$(MAKE) ${APP_DIR}/.php-version
	$(MAKE) ${APP_DIR}/php.ini
	@if ! php -r '$$config = json_decode(file_get_contents("${APP_DIR}/composer.json"), true, 512, JSON_THROW_ON_ERROR); exit(isset($$config["require"]["monsieurbiz/${PLUGIN_NAME}"]) ? 0 : 1);'; then \
		(cd ${APP_DIR} && ${COMPOSER} install --no-scripts --no-interaction); \
	fi
	# Bootstrap the path plugin from dist, never from the published Sylius 1 recipe.
	(cd ${APP_DIR} && ${COMPOSER} require --no-plugins --no-scripts --no-interaction --with-all-dependencies --minimal-changes sylius/sylius="${SYLIUS_VERSION}" monsieurbiz/${PLUGIN_NAME}="dev-upgrade-2.x")
	$(MAKE) apply_dist
	${CONSOLE} cache:clear
.PHONY: setup_application


${APP_DIR}/docker-compose.yaml:
	rm -f ${APP_DIR}/docker-compose.yml
	rm -f ${APP_DIR}/docker-compose.yaml
	rm -f ${APP_DIR}/compose.yml # Remove Sylius file about Docker
	rm -f ${APP_DIR}/compose.override.dist.yml # Remove Sylius file about Docker
	ln -s ../../docker-compose.yaml.dist ${APP_DIR}/docker-compose.yaml
.PHONY: ${APP_DIR}/docker-compose.yaml

${APP_DIR}/.php-version: .php-version
	(cd ${APP_DIR} && ln -sf ../../.php-version)

${APP_DIR}/php.ini: php.ini
	(cd ${APP_DIR} && ln -sf ../../php.ini)

apply_dist:
	php tests/setup-application.php
.PHONY: apply_dist

###
### TESTS
### ¯¯¯¯¯

test.all: test.composer test.phpstan test.phpmd test.phpunit test.javascript test.phpspec test.phpcs test.yaml test.schema test.twig test.container ## Run all tests in once

test.javascript: ## Test instant search without external services
	node --test tests/javascript/*.test.cjs
.PHONY: test.javascript

test.integration: ## Exercise the installed application, its routes, forms and index
	${SYMFONY} php vendor/bin/phpunit -c ../../phpunit.integration.xml.dist
.PHONY: test.integration

recipe.endpoint: ## Prepare an unpublished endpoint from the sibling recipes checkout
	php tests/recipe-endpoint.php
.PHONY: recipe.endpoint

test.composer: ## Validate composer.json
	${COMPOSER} validate --strict

test.phpstan: ## Run PHPStan
	${COMPOSER} phpstan

test.phpmd: ## Run PHPMD
	${COMPOSER} phpmd

test.phpunit: ## Run PHPUnit
	${COMPOSER} phpunit

test.phpspec: ## Run PHPSpec
	${COMPOSER} phpspec

test.phpcs: ## Run PHP CS Fixer in dry-run
	${COMPOSER} run -- phpcs --dry-run -v

test.phpcs.fix: ## Run PHP CS Fixer and fix issues if possible
	${COMPOSER} run -- phpcs -v

test.container: ## Lint the symfony container
	${CONSOLE} lint:container

sylius.cache.clear: ## Rebuild the test application's cache and generated mappers
	${CONSOLE} cache:clear
.PHONY: sylius.cache.clear

test.yaml: ## Lint the symfony Yaml files
	${CONSOLE} lint:yaml ../../config --parse-tags

test.schema: ## Validate MySQL Schema
	${CONSOLE} app:search:validate-mappings
	${CONSOLE} doctrine:schema:validate --skip-mapping

test.schema.upstream: ## Run Doctrine's unfiltered validator (includes known Sylius superclass diagnostic)
	${CONSOLE} doctrine:schema:validate

test.twig: ## Validate Twig templates
	${CONSOLE} lint:twig --no-debug templates/ ../../templates/

###
### SYLIUS
### ¯¯¯¯¯¯

sylius: ## Install Sylius
	${MAKE} dependencies
	${MAKE} sylius.database
	${MAKE} messenger.setup
	${MAKE} sylius.fixtures
	${MAKE} sylius.assets
.PHONY: sylius

sylius.database: ## Setup the database
	@test "$(REBUILD_DATABASE)" = "1" || (printf 'Set REBUILD_DATABASE=1 only for the disposable search test database.\n'; exit 1)
	${CONSOLE} doctrine:database:drop --if-exists --force
	${CONSOLE} doctrine:database:create --if-not-exists
	${CONSOLE} doctrine:migration:migrate -n

sylius.fixtures: ## Run the fixtures
	${CONSOLE} sylius:fixtures:load -n default

sylius.assets: ## Install all assets with symlinks
	${CONSOLE} assets:install --symlink

messenger.setup: ## Setup Messenger transports
	${CONSOLE} messenger:setup-transports

###
### PLATFORM
### ¯¯¯¯¯¯¯¯

platform: .php-version up ## Setup the platform tools
.PHONY: platform

docker.pull: ## Pull the docker images
	cd ${APP_DIR} && ${COMPOSE} pull

docker.up: ## Start the docker containers
	cd ${APP_DIR} && ${COMPOSE} up -d --wait --wait-timeout 180
.PHONY: docker.up

docker.stop: ## Stop the docker containers
	cd ${APP_DIR} && ${COMPOSE} stop
.PHONY: docker.stop

docker.down: ## Stop and remove the docker containers
	cd ${APP_DIR} && ${COMPOSE} down
.PHONY: docker.down

docker.logs: ## Logs the docker containers
	cd ${APP_DIR} && ${COMPOSE} logs -f
.PHONY: docker.logs

docker.dc: ARGS=ps
docker.dc: ## Run docker-compose command. Use ARGS="" to pass parameters to docker-compose.
	cd ${APP_DIR} && ${COMPOSE} ${ARGS}
.PHONY: docker.dc

server.start: ## Run the local webserver using Symfony
	${SYMFONY} local:server:start -d

server.stop: ## Stop the local webserver
	${SYMFONY} local:server:stop

es.reindex: ## Reindex elasticsearch
	${CONSOLE} monsieurbiz:search:populate

consume.reindex: ## Consume reindex messages during 10min
	${CONSOLE} messenger:consume async_search --time-limit=600 -vv

doctrine.diff: ## Doctrine diff
	${CONSOLE} doctrine:migration:diff --namespace="${MIGRATIONS_NAMESPACE}"

doctrine.migrate: ## Doctrine diff
	${CONSOLE} doctrine:migration:migrate

###
### HELP
### ¯¯¯¯

help: SHELL=/bin/bash
help: ## Dislay this help
	@IFS=$$'\n'; for line in `grep -h -E '^[a-zA-Z_#-]+:?.*?##.*$$' $(MAKEFILE_LIST)`; do if [ "$${line:0:2}" = "##" ]; then \
	echo $$line | awk 'BEGIN {FS = "## "}; {printf "\033[33m    %s\033[0m\n", $$2}'; else \
	echo $$line | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m%s\n", $$1, $$2}'; fi; \
	done; unset IFS;
.PHONY: help
