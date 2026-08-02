.PHONY: help install dev prod test stan psalm cs fix security all
help: @echo "Available commands: install dev prod test stan psalm cs fix security all"
install: composer install && npm install
dev: npm run dev
prod: npm run production
test: ./vendor/bin/phpunit
stan: ./vendor/bin/phpstan analyse
psalm: ./vendor/bin/psalm
cs: ./vendor/bin/phpcs
fix: ./vendor/bin/phpcbf && ./vendor/bin/rector
security: composer audit
all: test stan psalm cs security
