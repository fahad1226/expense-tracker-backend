# Expense Tracker API

A Laravel REST API for tracking personal expenses. Record spending, organize by categories, and understand where your money goes.

## The Problem

Managing finances is hard when you don't know where your money is going. Scattered receipts, forgotten subscriptions, and impulse purchases make it difficult to build a clear picture of spending habits.

This API solves that by providing a structured way to:

- **Log expenses** with amount, date, category, payment method, and notes
- **Organize spending** with custom categories (e.g., Food, Transport, Subscriptions)
- **Track over time** with user-scoped data that stays private

## Features

- **Authentication** — Register, login, and logout via Laravel Sanctum (token-based)
- **Expenses** — Create, read, update, and delete expenses
- **Categories** — Define custom categories with name, description, and icon
- **User isolation** — Each user only sees their own expenses and categories

## Tech Stack

- PHP 8.4
- Laravel 12
- Laravel Sanctum (API authentication)
- MySQL / SQLite

## Getting Started

### Prerequisites

- PHP 8.4+
- Composer
- MySQL or SQLite

### Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

### Running the Server

```bash
php artisan serve
```

The API will be available at `http://localhost:8000`.

## API Endpoints

### Authentication (Public)

| Method | Endpoint        | Description                |
| ------ | --------------- | -------------------------- |
| POST   | `/api/register` | Register a new user        |
| POST   | `/api/login`    | Login and receive a token  |
| POST   | `/api/logout`   | Logout (invalidates token) |
| GET    | `/api/user`     | Get current user           |

### Expenses (Protected)

| Method | Endpoint             | Description          |
| ------ | -------------------- | -------------------- |
| GET    | `/api/expenses`      | List user's expenses |
| POST   | `/api/expenses`      | Create an expense    |
| PUT    | `/api/expenses/{id}` | Update an expense    |
| DELETE | `/api/expenses/{id}` | Delete an expense    |

### Categories (Protected)

| Method | Endpoint          | Description            |
| ------ | ----------------- | ---------------------- |
| GET    | `/api/categories` | List user's categories |
| POST   | `/api/categories` | Create a category      |

### Example: Create an Expense

```bash
curl -X POST http://localhost:8000/api/expenses \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 24.99,
    "category_id": 1,
    "date": "2025-02-16",
    "note": "Weekly groceries",
    "payment_method": "card"
  }'
```

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
