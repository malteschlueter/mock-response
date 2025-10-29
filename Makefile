.PHONY: help
help: ## Shows this help
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_\-\.]+:.*?## / {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

.PHONY: init
init: composer-install-app composer-install-dev-ops ## Install composer dependencies

.PHONY: update
update: composer-update-app composer-update-dev-ops ## Update composer dependencies

.PHONY: composer-install-app
composer-install-app:
	composer install

.PHONY: composer-update-app
composer-update-app:
	composer update

.PHONY: composer-install-dev-ops
composer-install-dev-ops:
	composer install --working-dir=./dev-ops/ci

.PHONY: composer-update-dev-ops
composer-update-dev-ops:
	composer update --working-dir=./dev-ops/ci

.PHONY: cs-fix
cs-fix: ## Run php-cs-fixer
	php dev-ops/ci/vendor/bin/php-cs-fixer fix --config dev-ops/ci/config/.php-cs-fixer.dist.php

.PHONY: phpunit
phpunit: ## Run phpunit with coverage
	php bin/phpunit --configuration dev-ops/ci/config/phpunit.xml.dist --testsuite=unit,integration

.PHONY: phpstan
phpstan: ## Run phpstan
	php -d memory_limit=-1 dev-ops/ci/vendor/bin/phpstan analyse -c dev-ops/ci/config/phpstan.neon

.PHONY: tests
tests: cs-fix phpstan phpunit ## Run all tests

.PHONY: lint-container
lint-container: ## Validate container
	php bin/console lint:container

.PHONY: lint-twig
lint-twig: ## Validate twig templates
	php bin/console lint:twig templates

.PHONY: lint-config
lint-config: ## Validate config yaml files
	php bin/console lint:yaml config

.PHONY: lint
lint: lint-container lint-twig lint-config ## Validate everything
