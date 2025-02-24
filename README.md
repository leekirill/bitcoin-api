# bitcoin-api

# Инструкция по развертыванию проекта

Следуйте этим шагам для того, чтобы развернуть проект на своей машине с использованием Docker.

## 1. Клонируйте репозиторий

Сначала клонируйте репозиторий на вашу локальную машину:
```
git clone https://github.com/leekirill/bitcoin-api.git 
```
```
cd bitcoin-api
```

## 2. Создайте файл конфигурации

Скопируйте файл `.env.example` в `.env`:
```
cp app/.env.example app/.env
```


## 3. Запустите Docker контейнеры

Используйте команду ниже для того, чтобы собрать и запустить контейнеры:

```
make build
```

Для остановки контейнеров используйте `make down`

Важно: <br> Внутри Makefile я использовал команду `docker-compose`, если у вас появится ошибка, попробуйте заменить `docker-compose` на `docker compose`

## 4. Создать и накатить миграцию

Для создания миграции используйте:

```
make migration
```

Для накатки миграции используйте:

```
make migrate
```

## 5. Запустите приложение 🎉

```
make start
```

## 6. Доступ к серверу

После того как контейнеры будут запущены, вы сможете получить доступ к приложению на следующем URL:

[http://localhost:8000](http://localhost:8000/api/rates)

## 7. Эндпоинты API

Ваше приложение предоставляет следующие эндпоинты API:

### `/api/rates`

Этот эндпоинт возвращает текущие курсы валют.

### `/api/rates/history?range=1h`

Этот эндпоинт возвращает историю курсов за заданный диапазон времени. Параметр `range` может быть:

- `1h` — последние 1 час
- `24h` — последние 24 часа

### `/api/rates/history?from=YYYY-MM-DDTHH:MM:SS&to=YYYY-MM-DDTHH:MM:SS`

Этот эндпоинт позволяет получить историю курсов валют за период, определенный параметрами `from` и `to`. Параметры:
- `from` — дата и время начала периода в формате `YYYY-MM-DDTHH:MM:SS`
- `to` — дата и время окончания периода в формате `YYYY-MM-DDTHH:MM:SS`
 
Пример: `/api/rates/history?from=2025-02-20T15:00:00Z&to=2025-02-20T18:30:00Z` 

**Важно:** Все даты и времена в ответах API и запросах передаются в формате **ISO 8601**, например:
- `2025-02-20T14:30:00Z` — дата и время в формате UTC.
