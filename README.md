<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

-   [Simple, fast routing engine](https://laravel.com/docs/routing).
-   [Powerful dependency injection container](https://laravel.com/docs/container).
-   Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
-   Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
-   Database agnostic [schema migrations](https://laravel.com/docs/migrations).
-   [Robust background job processing](https://laravel.com/docs/queues).
-   [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

-   **[Vehikl](https://vehikl.com/)**
-   **[Tighten Co.](https://tighten.co)**
-   **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
-   **[64 Robots](https://64robots.com)**
-   **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
-   **[DevSquad](https://devsquad.com/hire-laravel-developers)**
-   **[Redberry](https://redberry.international/laravel-development/)**
-   **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

# TasteCraft API Documentation

## Overview

TasteCraft adalah platform berbagi resep masakan yang memungkinkan pengguna untuk membuat, berbagi, dan menemukan resep-resep baru.

## Base URL

```
http://your-domain.com/api
```

## Authentication

API ini menggunakan Bearer Token untuk autentikasi. Tambahkan header berikut untuk endpoint yang memerlukan autentikasi:

```
Authorization: Bearer <your_token>
```

## Endpoints

### Authentication

#### Register User

```http
POST /auth/register
Content-Type: application/json

{
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com",
    "password": "your_password",
    "password_confirmation": "your_password"
}

Response (201 Created):
{
    "status": "success",
    "message": "Registration successful",
    "data": {
        "user": {
            "id": "uuid",
            "first_name": "John",
            "last_name": "Doe",
            "email": "john@example.com"
        },
        "token": "your_access_token"
    }
}
```

#### Login

```http
POST /auth/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "your_password"
}

Response (200 OK):
{
    "status": "success",
    "data": {
        "user": {
            "id": "uuid",
            "first_name": "John",
            "last_name": "Doe",
            "email": "john@example.com"
        },
        "token": "your_access_token"
    }
}
```

#### Logout

```http
DELETE /auth/logout
Authorization: Bearer <token>

Response (200 OK):
{
    "status": "success",
    "message": "Successfully logged out"
}
```

### Recipe Management

#### Get All Recipes (Admin)

```http
GET /admin/recipes
Authorization: Bearer <token>

Query Parameters:
- search: string (optional) - Search by title or description
- category: string (optional) - Filter by category

Response (200 OK):
{
    "status": "success",
    "data": [
        {
            "id": "uuid",
            "title": "Nasi Goreng Spesial",
            "description": "Resep nasi goreng dengan bumbu rahasia",
            "cooking_time": 30,
            "category": "main_course",
            "image_url": "recipe_images/nasigoreng.jpg",
            "user": {
                "id": "uuid",
                "first_name": "John"
            },
            "ingredients": [...],
            "steps": [...]
        }
    ]
}
```

#### Create Recipe (Admin)

```http
POST /admin/recipes
Authorization: Bearer <token>
Content-Type: multipart/form-data

Form Data:
- title: string (required) - Judul resep
- description: string (required) - Deskripsi resep
- cooking_time: integer (required) - Waktu memasak dalam menit
- category: string (required) - Kategori (appetizer/main_course/dessert/beverage)
- image_url: file/base64 (optional) - Gambar resep
- ingredients: array (required) - Minimal 1 bahan
  - ingredient_name: string (required)
  - quantity: string (required)
  - unit: string (required)
- steps: array (required) - Minimal 1 langkah
  - step_number: integer (required)
  - description: string (required)

Response (201 Created):
{
    "status": "success",
    "message": "Recipe created successfully",
    "data": {
        "id": "uuid",
        "title": "Nasi Goreng Spesial",
        ...
    }
}
```

#### Get Recipe Detail

```http
GET /recipes/{recipeId}
Authorization: Bearer <token>

Response (200 OK):
{
    "status": "success",
    "data": {
        "recipe": {
            "id": "uuid",
            "title": "Nasi Goreng Spesial",
            "description": "...",
            "cooking_time": 30,
            "category": "main_course",
            "image_url": "...",
            "user": {...},
            "ingredients": [...],
            "steps": [...],
            "comments": [...]
        },
        "comments_count": 5
    }
}
```

#### Update Recipe (Admin)

```http
PUT /admin/recipes/{id}
Authorization: Bearer <token>
Content-Type: multipart/form-data

Form Data: (semua field optional)
- title: string
- description: string
- cooking_time: integer
- category: string
- image_url: file
- ingredients: array
- steps: array

Response (200 OK):
{
    "status": "success",
    "message": "Recipe updated successfully",
    "data": {...}
}
```

#### Delete Recipe (Admin)

```http
DELETE /admin/recipes/{id}
Authorization: Bearer <token>

Response (200 OK):
{
    "status": "success",
    "message": "Recipe deleted successfully"
}
```

### Comment Management

#### Get Recipe Comments

```http
GET /recipes/{recipeId}/comments

Response (200 OK):
{
    "status": "success",
    "data": [
        {
            "id": "uuid",
            "content": "Resepnya enak!",
            "created_at": "2024-03-21T15:30:00.000000Z",
            "user": {
                "id": "uuid",
                "first_name": "John",
                "last_name": "Doe",
                "profile_image": "..."
            }
        }
    ],
    "total": 1
}
```

#### Create Comment

```http
POST /comments
Authorization: Bearer <token>
Content-Type: application/json

{
    "recipe_id": "uuid",
    "content": "Resepnya sangat mudah diikuti!"
}

Response (201 Created):
{
    "status": "success",
    "message": "Comment created successfully",
    "data": {...}
}
```

#### Update Comment

```http
PUT /comments/{id}
Authorization: Bearer <token>
Content-Type: application/json

{
    "content": "Update: Sudah saya coba dan hasilnya enak!"
}

Response (200 OK):
{
    "status": "success",
    "message": "Comment updated successfully",
    "data": {...}
}
```

#### Delete Comment

```http
DELETE /comments/{id}
Authorization: Bearer <token>

Response (200 OK):
{
    "status": "success",
    "message": "Comment deleted successfully"
}
```

### User Profile

#### Get User Profile

```http
GET /user
Authorization: Bearer <token>

Response (200 OK):
{
    "id": "uuid",
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com",
    "profile_image": "..."
}
```

#### Get User Profile Image

```http
GET /user/profile-image
Authorization: Bearer <token>

Response (200 OK):
{
    "status": "success",
    "data": {
        "profile_image": "..."
    }
}
```

#### Update Profile Image

```http
PUT /user/profile-image
Authorization: Bearer <token>
Content-Type: multipart/form-data

Form Data:
- profile_image: file (required)

Response (200 OK):
{
    "status": "success",
    "message": "Profile image updated successfully",
    "data": {
        "profile_image": "..."
    }
}
```

#### Get User Recipes

```http
GET /user/recipes
Authorization: Bearer <token>

Response (200 OK):
{
    "status": "success",
    "data": [
        {
            "id": "uuid",
            "title": "Nasi Goreng Spesial",
            ...
        }
    ],
    "total": 1
}
```

## Error Responses

### Validation Error (422)

```json
{
    "status": "error",
    "message": "Validation failed",
    "errors": {
        "field_name": ["The field_name field is required"]
    }
}
```

### Unauthorized (401)

```json
{
    "status": "error",
    "message": "Unauthenticated"
}
```

### Forbidden (403)

```json
{
    "status": "error",
    "message": "Unauthorized. You don't have permission to perform this action."
}
```

### Not Found (404)

```json
{
    "status": "error",
    "message": "Resource not found"
}
```

### Server Error (500)

```json
{
    "status": "error",
    "message": "Internal server error",
    "error": "Error details..."
}
```

## Catatan Penting

1. Semua request yang memerlukan autentikasi harus menyertakan token dalam header
2. Format gambar yang didukung: jpeg, png, jpg (max: 2MB)
3. Semua ID menggunakan format UUID
4. Response selalu dalam format JSON
5. Timestamps menggunakan format ISO 8601
6. Pagination default: 10 items per page
