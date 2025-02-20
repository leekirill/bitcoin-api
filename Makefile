bash:
	docker exec -it php_fpm /bin/bash

up:
	docker-compose up -d

down:
	docker-compose down

build:
	docker-compose up --build -d