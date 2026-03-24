# Ekapak — API сервиса управления заказами

Backend-сервис для интернет-магазина автозапчастей.

**Стек:** PHP 8.4, Laravel 12, PostgreSQL 16, Redis 7, Docker (php-fpm + nginx)

---

## Быстрый старт

### 1. Клонировать репозиторий и настроить окружение

```bash
git clone <repo-url>
cd ekapak
cp .env.example .env
```

### 2. Поднять контейнеры

```bash
docker compose up -d
```

После первого запуска все 4 контейнера будут доступны:
- **app** (php-fpm 8.4) — порт 9000 (внутренний)
- **nginx** — порт 80
- **postgres** — порт 5432
- **redis** — порт 6379

### 3. Сгенерировать APP_KEY и накатить миграции

```bash
# Сгенерировать APP_KEY
docker compose exec app php artisan key:generate

# Накатить миграции и заполнить БД тестовыми данными
docker compose exec app php artisan migrate --seed
```

### 4. Запустить обработчик очереди

```bash
docker compose exec app php artisan queue:work redis
```

### 5. Запустить тесты

```bash
docker compose exec app php artisan test
```

### 6. Открыть Swagger UI

```
http://localhost/api/documentation
```

---

## API эндпоинты

### GET /api/v1/products

Список товаров с фильтрацией и пагинацией.

```bash
# Все товары
curl http://localhost/api/v1/products -H "Accept: application/json"

# Фильтр по категории
curl "http://localhost/api/v1/products?category=engine" -H "Accept: application/json"

# Поиск по названию/артикулу
curl "http://localhost/api/v1/products?search=filter" -H "Accept: application/json"

# Пагинация
curl "http://localhost/api/v1/products?per_page=10&page=2" -H "Accept: application/json"
```

**Query параметры:** `category`, `search`, `per_page` (max: 100), `page`

---

### POST /api/v1/orders

Создание заказа. Rate limit: 10 запросов в минуту по IP.

```bash
curl -X POST http://localhost/api/v1/orders \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "customer_id": 1,
    "items": [
      {"product_id": 1, "quantity": 2},
      {"product_id": 9, "quantity": 1}
    ]
  }'
```

---

### GET /api/v1/orders

Список заказов с фильтрами.

```bash
# Все заказы
curl http://localhost/api/v1/orders -H "Accept: application/json"

# Фильтр по статусу
curl "http://localhost/api/v1/orders?status=confirmed" -H "Accept: application/json"

# Фильтр по клиенту и диапазону дат
curl "http://localhost/api/v1/orders?customer_id=1&date_from=2026-01-01&date_to=2026-12-31" \
  -H "Accept: application/json"
```

**Query параметры:** `status`, `customer_id`, `date_from`, `date_to`, `per_page` (max: 100), `page`

---

### GET /api/v1/orders/{id}

Детали заказа.

```bash
curl http://localhost/api/v1/orders/1 -H "Accept: application/json"
```

---

### PATCH /api/v1/orders/{id}/status

Смена статуса заказа.

```bash
curl -X PATCH http://localhost/api/v1/orders/1/status \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"status": "confirmed"}'
```

**Допустимые переходы статусов:**
```
new -> confirmed -> processing -> shipped -> completed
new -> cancelled
confirmed -> cancelled
```

---

## Архитектура

### Service Layer
Вся бизнес-логика в `App\Services\OrderService` и `App\Services\ProductService`. Контроллеры тонкие — принимают validated data через Form Requests, передают в сервис.

### DTO
`CreateOrderDTO`, `OrderItemDTO`, `UpdateOrderStatusDTO` — строгая типизация передаваемых данных между слоями. PHP 8.4 `readonly` properties.

### Транзакции
Создание заказа выполняется в DB-транзакции с `SELECT FOR UPDATE` на продукты. Предотвращает race condition при конкурентных заказах.

### Event/Listener
При переводе заказа в `confirmed` запускается событие `OrderConfirmed`. Listener `DispatchExportOrderJob` ставит в очередь джобу `ExportOrderJob`.

### Redis-кеширование
`ProductService::getList()` кеширует результаты в Redis с TTL 1 час. Cache Tags `['products']` позволяют инвалидировать всё при обновлении каталога.

### Очередь экспорта
`ExportOrderJob` отправляет данные заказа на внешний URL (`EXPORT_URL`). Настроено 3 retry с backoff [5, 10, 15] секунд. Статус отслеживается в таблице `order_exports`.

---

## Тестовые данные

После `php artisan migrate --seed`:
- **30 товаров** в 5 категориях: `engine`, `brakes`, `suspension`, `electrical`, `body`
- **10 клиентов** с реалистичными данными

---

## Полезные команды

```bash
# Пересоздать БД с нуля
docker compose exec app php artisan migrate:fresh --seed

# Очистить кеш
docker compose exec app php artisan cache:clear

# Сгенерировать Swagger документацию
docker compose exec app php artisan l5-swagger:generate

# Открыть интерактивную консоль
docker compose exec app php artisan tinker

# Посмотреть список маршрутов
docker compose exec app php artisan route:list
```

---

## Структура проекта

```
app/
  DTOs/               # Data Transfer Objects (readonly, PHP 8.4)
  Enums/              # OrderStatus, ExportStatus
  Events/             # OrderConfirmed
  Exceptions/         # InsufficientStockException, InvalidStatusTransitionException
  Http/
    Controllers/Api/V1/  # ProductController, OrderController
    Requests/            # Form Request валидация входящих данных
    Resources/           # API Resources (JSON трансформация ответов)
  Jobs/               # ExportOrderJob (tries=3, backoff=[5,10,15])
  Listeners/          # DispatchExportOrderJob
  Models/             # Product, Customer, Order, OrderItem, OrderExport
  Services/           # OrderService, ProductService
config/
  export.php          # EXPORT_URL настройка
database/
  factories/          # Фабрики для тестов
  migrations/         # Схема БД (products, customers, orders, order_items, order_exports)
  seeders/            # Тестовые данные (30 товаров, 10 клиентов)
docker/
  nginx/              # Nginx конфигурация (проксирует на php-fpm:9000)
  php/                # PHP 8.4 Dockerfile + php.ini
  postgres/           # init.sql (создаёт тестовую БД ekapak_test)
tests/Feature/        # Feature тесты (PostgreSQL, RefreshDatabase)
```
