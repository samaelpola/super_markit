all: build install up

build:
	docker compose build

up:
	docker compose up

down:
	docker compose down

exec:
	docker compose run app bash

clear:
	docker compose run app php bin/console cache:clear

install:
	docker compose run app composer install
	docker compose run app php bin/console importmap:install

migration:
	docker compose run app php bin/console doctrine:migration:migrate

asset:
	docker compose run app php bin/console asset-map:compile
