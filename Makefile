.PHONY: test
test: vendor/autoload.php
	vendor/bin/phpunit --bootstrap vendor/autoload.php Tests/
