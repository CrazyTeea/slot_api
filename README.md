# Slot API

API для управления бронированием слотов с горячим кешем и защитой от оверсела.

## Требования

- PHP 8.2+
- Laravel 12
- MySQL 8+

## Установка

```bash
composer install
php artisan migrate
php artisan optimize:clear
```

## Тестирование
```bash
php artisan test

# Проверить доступные слоты
curl "http://localhost/slots/availability"

# Создать холд
curl -X POST "http://localhost/slots/1/hold" -H "Idempotency-Key: test-uuid-123"

# Подтвердить холд
curl -X POST "http://localhost/holds/1/confirm"

# Отменить холд
curl -X DELETE "http://localhost/holds/1"


