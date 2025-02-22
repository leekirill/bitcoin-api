bash:
	docker exec -it php_fpm /bin/bash

up:
	docker-compose up -d

down:
	docker-compose down

build:
	docker-compose build

migration:
	docker exec -it php_fpm /bin/bash -c "bin/console make:migration"

migrate:
	docker exec -it php_fpm /bin/bash -c "bin/console doctrine:migrations:migrate"
