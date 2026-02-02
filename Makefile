.PHONY: default
default: build

.PHONY: build
build: vendor/autoload.php

.PHONY: fix
fix: vendor/autoload.php
	php vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.php --diff

.PHONY: lint
lint: vendor/autoload.php
	php vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.php --diff --dry-run

.PHONY: test
test: vendor/autoload.php
	vendor/bin/phpunit --display-notices --display-phpunit-notices --bootstrap vendor/autoload.php Tests/

vendor/autoload.php: composer.json composer.lock
	composer install --prefer-dist --no-scripts --no-plugins
	touch vendor/autoload.php

.PHONY: clean
clean:
	rm -rf vendor
