# MovieHub API hui

A REST API for a movie discovery and management platform featuring a movie catalog, genres, user reviews with ratings, and favorites. This pet project is built using production-oriented backend development practices, including a layered architecture, asynchronous processing, caching, automated testing, and continuous integration.

## Tech Stack

| Layer | Technology |
|--------|------------|
| Language / Framework | PHP 8.3, Laravel 12 |
| Database | PostgreSQL 16 |
| Cache / Queues | Redis 7 |
| Authentication | JWT (`tymon/jwt-auth`) |
| API Documentation | Swagger / OpenAPI (`l5-swagger`) |
| Testing | Pest / PHPUnit |
| Containerization | Docker, Docker Compose |
| CI | GitHub Actions |

## Architecture

The project follows a layered architecture with clear separation of responsibilities:

```
Controller → FormRequest (validation) → DTO → Service (business logic) → Repository (data access) → Model
```

- **DTO** — strongly typed immutable objects used to transfer data between layers instead of passing raw request arrays.
- **Repository** — data access is abstracted behind interfaces (`MovieRepositoryInterface`, `ReviewRepositoryInterface`), with concrete implementations registered in `AppServiceProvider`. This allows repositories to be easily mocked during testing and keeps business logic independent of Eloquent.
- **Service** — contains business rules such as Redis caching, cache invalidation, duplicate review prevention, and event dispatching.
- **Events / Listeners / Jobs** — when a review is created or updated, a `ReviewSaved` event is dispatched. Its listener queues a `RecalculateMovieRatingJob`, ensuring expensive rating aggregation is performed asynchronously without delaying the API response.
- **Policies** — authorization is separated from controllers (`MoviePolicy`, `ReviewPolicy`). Only moderators and administrators can create or edit movies, while reviews can only be edited by their authors.

```
moviehub-api/
├── app/
│   ├── DTO/                  # MovieData, ReviewData, MovieFilterData
│   ├── Services/             # MovieService, ReviewService
│   ├── Repositories/
│   │   ├── Contracts/        # Repository interfaces
│   │   └── Eloquent/         # Eloquent implementations
│   ├── Models/
│   ├── Http/
│   │   ├── Controllers/Api/V1/
│   │   ├── Requests/
│   │   └── Resources/
│   ├── Jobs/                 # RecalculateMovieRatingJob
│   ├── Events/               # ReviewSaved
│   ├── Listeners/            # RecalculateMovieRating
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

## Quick Start

```bash
git clone <repository-url> moviehub-api
cd moviehub-api
cp .env.example .env

docker compose up -d --build

docker compose exec app php artisan key:generate
docker compose exec app php artisan jwt:secret
docker compose exec app php artisan migrate --seed
```

The API will be available at:

```
http://localhost:8000/api/v1
```

Swagger documentation:

```
http://localhost:8000/api/documentation
```

### Seeded Test Accounts

- **Administrator**
  - Email: `admin@moviehub.test`
  - Password: `password`

- **Moderator**
  - Email: `moderator@moviehub.test`
  - Password: `password`

## Main Endpoints

| Method | Endpoint | Description | Access |
|--------|----------|-------------|--------|
| POST | `/auth/register` | Register a new account | Public |
| POST | `/auth/login` | Authenticate and receive a JWT | Public |
| POST | `/auth/refresh` | Refresh JWT token | Authenticated |
| GET | `/movies` | List movies (filtering, sorting, pagination) | Public |
| GET | `/movies/{slug}` | Retrieve movie details | Public |
| POST | `/movies` | Create a new movie | Moderator/Admin |
| PUT | `/movies/{movie}` | Update a movie | Moderator/Admin |
| DELETE | `/movies/{movie}` | Delete a movie | Admin |
| GET | `/movies/{movie}/reviews` | List movie reviews | Public |
| POST | `/movies/{movie}/reviews` | Submit a review | Authenticated |
| PUT | `/reviews/{review}` | Update your review | Author |
| DELETE | `/reviews/{review}` | Delete a review | Author/Moderator |
| GET | `/favorites` | List favorite movies | Authenticated |
| POST | `/movies/{movie}/favorite` | Add a movie to favorites | Authenticated |

Complete request/response schemas and filtering options are available in the Swagger UI.

## Testing

Run the test suite:

```bash
docker compose exec app vendor/bin/pest
```

Run tests with code coverage:

```bash
docker compose exec app vendor/bin/pest --coverage
```

The test suite covers:

- Authentication
- Movie CRUD operations
- Authorization policies
- Duplicate review prevention
- Asynchronous movie rating recalculation
- DTO validation and behavior

## Continuous Integration

The GitHub Actions workflow (`.github/workflows/ci.yml`) runs on every push and pull request and performs the following steps:

1. Starts PostgreSQL and Redis service containers.
2. Installs project dependencies and runs database migrations.
3. Checks code style using `laravel/pint`.
4. Executes the test suite with code coverage (minimum threshold: **70%**).
5. Builds the Docker image.

## Future Improvements

- Full-text movie search using PostgreSQL `tsvector` or Meilisearch.
- User-based rate limiting instead of IP-based limits.
- Support for multiple movie images (screenshots and posters) with asynchronous image processing.
- Real-time notifications for new reviews using Laravel Reverb WebSockets.
