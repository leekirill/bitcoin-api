bash:
	docker exec -it php_fpm /bin/bash

up:
	docker-compose up -d

down:
	docker-compose down

start:
	make up
	make consume

build:
	docker-compose up -d --build

migration:
	docker exec -it php_fpm /bin/bash -c "bin/console make:migration"

migrate:
	docker exec -it php_fpm /bin/bash -c "bin/console doctrine:migrations:migrate"

consume:
	docker exec -it php_fpm /bin/bash -c "bin/console messenger:consume"
