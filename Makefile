.PHONY: test phpcs phpstan

test:
	./vendor/bin/phpunit --configuration phpunit.xml

phpcs:
	./vendor/bin/phpcs --standard=ruleset.xml --encoding=utf-8 --extensions=php --warning-severity=8 --report=checkstyle .

phpstan:
	./vendor/bin/phpstan --configuration=phpstan.neon --error-format=raw --no-progress --no-interaction analyse

