<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>


# News Aggregator API

A Laravel-based API for aggregating news articles from multiple sources (NewsAPI, Guardian). This API provides endpoints for fetching articles from these sources, saving them to the database, and serving personalized feeds based on user preferences.

## Features

- Fetch news articles from multiple sources.
- Cache the personalized feed for performance optimization.
- User preferences-based personalized feed.
- Scheduled commands for regular article updates from news APIs.


## Prerequisites

Before you begin, ensure you have the following installed:

- PHP >= 8.0
- Composer
- MySQL (or Docker for local setup)
- Docker (optional)
- Docker Compose (optional)



## Installation

1. **Clone the repository**:
    ```bash git clone https://github.com/elexispd/news-aggregator-api.git```
    ```cd news-aggregator-apicd news-aggregator-api```

2. **Set Up the Environment**:
    Copy the .env.example file to .env and configure it with your database and API keys:
   ```cp .env.example .env```
    Edit the .env file and configure:
    - DB_CONNECTION
    - DB_HOST
    - DB_PORT
    - DB_DATABASE
    - DB_USERNAME
    - DB_PASSWORD
    - NEWS_API_KEY
    - THE_GUARDIAN_API_KEY
    - OPENNEWS_API_KEY

3. **composer install**
    Run the following commands to install PHP dependencies:
    ```composer install```

4. **php artisan migrate --seed**
    setup database:
    ```php artisan migrate --seed```

4. **Run the Application Locally**
    Start the Laravel development server:
    ```php artisan serve```

The application should now be accessible at http://localhost:8000


## Running the Application with Docker

## Prerequisites
   Ensure you have the following installed:
   - Docker
   - Docker Compose

1. **Clone the repository**:
    ```bash git clone https://github.com/elexispd/news-aggregator-api.git```
    ```cd news-aggregator-apicd news-aggregator-api```

2. **docker-compose build**
    ```docker-compose build```

3. **start the containers**
    ```docker-compose up -d```

3. **Run migrations and seed data: To run migrations and seed the database, execute:**
    ```docker-compose exec app php artisan migrate --seed```

4. **Access the application: The application should now be accessible at http://localhost:8000**
    ```docker-compose exec app php artisan migrate --seed```

5. **Stopping the containers: To stop the containers, run:**
    ```docker-compose down```

5. **View logs: To view logs of the running application, you can use:**
    ```docker-compose logs -f```


## Additional Notes
Ensure that the .env file is properly set up.
If you need to access the application’s shell, you can use:
```docker-compose exec app bash```

## API Endpoints

### Authentication Endpoints (`/auth`)

- **POST /auth/register**
  - **Description**: Register a new user.
  - **Request Body**: 
    - `name`: string, required
    - `email`: string, required
    - `password`: string, required
    - `password_confirmation`: string, required
  - **Response**: 
    - Returns user details and a token upon successful registration.

- **POST /auth/login**
  - **Description**: Log in an existing user.
  - **Request Body**:
    - `email`: string, required
    - `password`: string, required
  - **Response**: 
    - Returns user details and a token upon successful login.

- **POST /auth/logout**
  - **Description**: Log out the authenticated user.
  - **Response**: 
    - Returns a success message.

- **POST /auth/reset-password**
  - **Description**: Request a password reset.
  - **Request Body**:
    - `email`: string, required
  - **Response**: 
    - Returns a success message indicating that a password reset link was sent.

### Articles Endpoints (`/articles`)

- **GET /articles**
  - **Description**: Fetch all articles.
  - **Response**: 
    - Returns a paginated list of articles.

- **GET /articles/{article}**
  - **Description**: Fetch a single article by its ID.
  - **Response**: 
    - Returns the article details.

- **GET /articles/search**
  - **Description**: Search articles by a query parameter.
  - **Request Parameters**:
    - `query`: string, optional (search keyword)
  - **Response**: 
    - Returns a list of articles matching the search query.

- **GET /fetchArticle**
  - **Description**: Fetch articles from external APIs (NewsAPI, Guardian, etc.).
  - **Rate Limiting**: 30 requests per minute.
  - **Response**: 
    - Returns the latest fetched articles from external news sources.

### User Preferences Endpoints (`/preferences`)

- **GET /preferences**
  - **Description**: Fetch user preferences.
  - **Response**: 
    - Returns the preferences set by the authenticated user.

- **POST /preferences**
  - **Description**: Save or update user preferences.
  - **Request Body**:
    - `categories`: array of integers (category IDs)
    - `sources`: array of integers (source IDs)
    - `authors`: array of strings (author names)
  - **Response**: 
    - Returns the updated user preferences.

- **GET /personalized-feed**
  - **Description**: Fetch a personalized feed of articles based on user preferences.
  - **Response**: 
    - Returns a paginated list of articles tailored to the user's preferences (cached for performance).

### News Sources Endpoints (`/sources`)

- **GET /sources**
  - **Description**: Fetch all available news sources.
  - **Response**: 
    - Returns a list of available sources.

- **POST /sources**
  - **Description**: Add a new news source.
  - **Request Body**:
    - `name`: string, required
    - `url`: string, required
  - **Response**: 
    - Returns the newly added source details.

### Rate Limiting

- The **`/fetchArticle`** endpoint is rate-limited to **30 requests per minute** per user using the `throttle:30,1` middleware.

### Authentication

- Most endpoints require the user to be authenticated with a **Sanctum token**. The token is sent in the `Authorization` header as a bearer token.

### Response Format

- All responses are in **JSON format**.




## Caching Strategy
To optimize API performance, we implement caching for the personalized feed. The feed is cached based on user preferences, including categories, sources, and authors. Caching ensures that repeated requests for the same personalized feed don't need to fetch data from the database repeatedly.

The cache is stored for 60 minutes, and the cache key is unique to each user's preferences.

## Rate Limiting
To prevent abuse and manage traffic, rate limiting is applied to the fetchArticle endpoint using Laravel's built-in throttle middleware. By default, the rate limit is set to 30 requests per minute.

## Scheduled Commands
We use Laravel's scheduler to fetch articles from external APIs at regular intervals. This ensures that our database is kept up-to-date with the latest news.

To schedule the commands, add the following to your server's cron job:
```* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1```

## Testing
To run the tests:
```php artisan test```


## Additional Notes
API Documentation: API documentation is available via Swagger.
Docker: This application can be run locally via Docker to provide an isolated environment for development.
Environment Variables: Ensure that all necessary environment variables are set correctly in the .env file, including API keys and database credentials.
Error Handling: Errors are handled gracefully, and users are provided with meaningful error messages.