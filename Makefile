dev development: # Install dev dependencies
	@composer install --no-interaction

prod production: # Install non-dev dependencies only
	@composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

test: # Run coding standards/static analysis checks and tests
	@vendor/bin/php-cs-fixer fix --diff --dry-run \
		&& vendor/bin/psalm --show-info=true\
		&& vendor/bin/phpstan analyze

coverage: # Generate an HTML coverage report
	@XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-html .coverage
