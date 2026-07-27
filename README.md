# MovieHub API

REST API сервиса просмотра фильмов: каталог, жанры, отзывы с рейтингом, избранное. Пет-проект, спроектированный по практикам продакшн backend-разработки — слоистая архитектура, асинхронная обработка, кэширование, тесты, CI.

## Стек

| Слой              | Технология                          |
|-------------------|--------------------------------------|
| Язык / фреймворк  | PHP 8.3, Laravel 12                  |
| БД                | PostgreSQL 16                        |
| Кэш / очереди     | Redis 7                              |
| Аутентификация    | JWT (tymon/jwt-auth)                 |
| Документация API  | Swagger / OpenAPI (l5-swagger)       |
| Тесты             | Pest / PHPUnit                       |
| Контейнеризация   | Docker, Docker Compose               |
| CI                | GitHub Actions                       |

## Архитектура

Проект построен по слоистой архитектуре с чётким разделением ответственности:

```
Controller → FormRequest (валидация) → DTO → Service (бизнес-логика) → Repository (доступ к данным) → Model
```

- **DTO** — типизированные неизменяемые объекты для передачи данных между слоями вместо "сырых" массивов из запроса.
- **Repository** — доступ к данным спрятан за интерфейсом (`MovieRepositoryInterface`, `ReviewRepositoryInterface`), реализация биндится в `AppServiceProvider`. Это позволяет подменять источник данных в тестах и не завязывать бизнес-логику на Eloquent напрямую.
- **Service** — бизнес-правила: кэширование списков в Redis, инвалидация кэша, проверка дублей отзывов, генерация событий.
- **Events/Listeners/Jobs** — сохранение отзыва публикует событие `ReviewSaved`, слушатель ставит в очередь джобу пересчёта среднего рейтинга фильма — тяжёлая агрегация не блокирует ответ пользователю.
- **Policies** — авторизация вынесена из контроллеров (`MoviePolicy`, `ReviewPolicy`): создавать/редактировать фильмы могут модераторы и админы, отзывы редактирует только автор.

```
moviehub-api/
├── app/
│   ├── DTO/                  # MovieData, ReviewData, MovieFilterData
│   ├── Services/              # MovieService, ReviewService
│   ├── Repositories/
│   │   ├── Contracts/         # интерфейсы
│   │   └── Eloquent/          # реализации
│   ├── Models/
│   ├── Http/
│   │   ├── Controllers/Api/V1/
│   │   ├── Requests/
│   │   └── Resources/
│   ├── Jobs/                  # RecalculateMovieRatingJob
│   ├── Events/                # ReviewSaved
│   ├── Listeners/             # RecalculateMovieRating
│   ├── Policies/
│   └── Exceptions/
├── database/
│   ├── migrations/
│   ├── factories/
│   └── seeders/
├── tests/
│   ├── Feature/
│   └── Unit/
├── docker/
├── docker-compose.yml
└── .github/workflows/ci.yml
```

## Быстрый старт

```bash
git clone <repo> moviehub-api
cd moviehub-api
cp .env.example .env

docker compose up -d --build

docker compose exec app php artisan key:generate
docker compose exec app php artisan jwt:secret
docker compose exec app php artisan migrate --seed
```

API будет доступно на `http://localhost:8000/api/v1`.
Swagger-документация: `http://localhost:8000/api/documentation`.

Тестовые учётки после сидинга:
- `admin@moviehub.test` / `password`
- `moderator@moviehub.test` / `password`

## Основные эндпоинты

| Метод  | Путь                          | Описание                          | Доступ         |
|--------|-------------------------------|------------------------------------|----------------|
| POST   | `/auth/register`              | Регистрация                        | Публичный      |
| POST   | `/auth/login`                 | Вход, выдача JWT                   | Публичный      |
| POST   | `/auth/refresh`                | Обновление токена                  | Авторизован    |
| GET    | `/movies`                     | Список фильмов (фильтры, сортировка, пагинация) | Публичный |
| GET    | `/movies/{slug}`               | Детали фильма                      | Публичный      |
| POST   | `/movies`                     | Создать фильм                      | Модератор/админ|
| PUT    | `/movies/{movie}`              | Обновить фильм                     | Модератор/админ|
| DELETE | `/movies/{movie}`              | Удалить фильм                      | Админ          |
| GET    | `/movies/{movie}/reviews`      | Отзывы на фильм                    | Публичный      |
| POST   | `/movies/{movie}/reviews`      | Оставить отзыв                     | Авторизован    |
| PUT    | `/reviews/{review}`            | Изменить свой отзыв                | Автор          |
| DELETE | `/reviews/{review}`            | Удалить отзыв                      | Автор/модератор|
| GET    | `/favorites`                   | Список избранного                  | Авторизован    |
| POST   | `/movies/{movie}/favorite`     | Добавить в избранное               | Авторизован    |

Полный список параметров фильтрации, схемы запросов/ответов — в Swagger UI.

## Тесты

```bash
docker compose exec app vendor/bin/pest
docker compose exec app vendor/bin/pest --coverage
```

Тесты покрывают: аутентификацию, CRUD и авторизацию по фильмам, создание/защиту от дублей отзывов, асинхронный пересчёт рейтинга, DTO.

## CI

`.github/workflows/ci.yml` на каждый push/PR:
1. Поднимает PostgreSQL и Redis как сервисы
2. Устанавливает зависимости, прогоняет миграции
3. Проверяет код-стайл (`laravel/pint`)
4. Запускает тесты с покрытием (порог — 70%)
5. Собирает Docker-образ

## Что дальше можно добавить

- Полнотекстовый поиск по описанию фильмов (PostgreSQL `tsvector` или Meilisearch)
- Rate limiting по пользователю, а не только по IP
- Загрузка нескольких изображений на фильм (кадры, постеры разных размеров) через очередь обработки изображений
- WebSocket-уведомления о новых отзывах через Laravel Reverb
