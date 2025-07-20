# CodeIgniter 4 RESTful API Documentation

## Overview

This is a comprehensive RESTful API built with CodeIgniter 4 framework, featuring user management, posts management, and authentication endpoints.

## Base URL
```
http://localhost:8080/api
```

## Authentication

All API endpoints require authentication using an API key.

**Header Required:**
```
X-API-Key: your-secret-api-key-here
```

Set your API key in the `.env` file:
```
API_KEY = your-secret-api-key-here
```

## Response Format

All API responses follow this standardized format:

### Success Response
```json
{
    "status": "success",
    "message": "Operation completed successfully",
    "data": {
        // Response data here
    }
}
```

### Error Response
```json
{
    "status": "error",
    "message": "Error description",
    "errors": {
        // Validation errors (if applicable)
    }
}
```

## API Endpoints

### 1. API Information

#### GET /api/
Get API information and health check.

**Response:**
```json
{
    "status": "success",
    "message": "API is running successfully",
    "data": {
        "name": "CodeIgniter 4 API",
        "version": "1.0.0",
        "status": "active",
        "endpoints": { ... }
    }
}
```

### 2. Users API

#### GET /api/users
Get all users with pagination.

**Query Parameters:**
- `page` (optional): Page number (default: 1)
- `limit` (optional): Items per page (default: 10)
- `search` (optional): Search by name or email

**Example Request:**
```bash
curl -X GET "http://localhost:8080/api/users?page=1&limit=5" \
  -H "X-API-Key: your-secret-api-key-here"
```

#### GET /api/users/{id}
Get a specific user by ID.

**Example Request:**
```bash
curl -X GET "http://localhost:8080/api/users/1" \
  -H "X-API-Key: your-secret-api-key-here"
```

#### POST /api/users
Create a new user.

**Request Body:**
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "1234567890",
    "status": "active"
}
```

**Example Request:**
```bash
curl -X POST "http://localhost:8080/api/users" \
  -H "Content-Type: application/json" \
  -H "X-API-Key: your-secret-api-key-here" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "1234567890"
  }'
```

#### PUT /api/users/{id}
Update an existing user.

**Request Body:**
```json
{
    "name": "John Smith",
    "phone": "0987654321",
    "status": "inactive"
}
```

#### DELETE /api/users/{id}
Delete a user (soft delete).

#### GET /api/users/active
Get only active users.

### 3. Posts API

#### GET /api/posts
Get all posts with pagination.

**Query Parameters:**
- `page` (optional): Page number
- `limit` (optional): Items per page
- `search` (optional): Search by title or content
- `status` (optional): Filter by status (draft, published, archived)

#### GET /api/posts/{id}
Get a specific post by ID.

#### POST /api/posts
Create a new post.

**Request Body:**
```json
{
    "title": "My First Post",
    "content": "This is the content of my first post...",
    "status": "published",
    "user_id": 1,
    "featured_image": "image.jpg"
}
```

#### PUT /api/posts/{id}
Update an existing post.

#### DELETE /api/posts/{id}
Delete a post (soft delete).

### 4. Authentication API

#### POST /api/auth/login
User login.

**Request Body:**
```json
{
    "email": "user@example.com",
    "password": "password123"
}
```

**Response:**
```json
{
    "status": "success",
    "message": "Login successful",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com"
        },
        "token": "generated-token-here",
        "expires_in": 3600
    }
}
```

#### POST /api/auth/register
User registration.

**Request Body:**
```json
{
    "name": "Jane Doe",
    "email": "jane@example.com",
    "password": "password123"
}
```

#### GET /api/auth/me
Get current user information.

## Database Setup

1. **Configure your database** in `.env`:
```
DB_HOST = localhost
DB_PORT = 3306
DB_NAME = ci4_database
DB_USERNAME = root
DB_PASSWORD = your_password
```

2. **Run migrations** to create tables:
```bash
php spark migrate
```

3. **Seed sample data** (optional):
```bash
php spark db:seed UserSeeder
php spark db:seed PostSeeder
```

## Testing the API

### Using cURL

1. **Test API health:**
```bash
curl -X GET "http://localhost:8080/api/" \
  -H "X-API-Key: your-secret-api-key-here"
```

2. **Create a user:**
```bash
curl -X POST "http://localhost:8080/api/users" \
  -H "Content-Type: application/json" \
  -H "X-API-Key: your-secret-api-key-here" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "phone": "1234567890"
  }'
```

3. **Get all users:**
```bash
curl -X GET "http://localhost:8080/api/users" \
  -H "X-API-Key: your-secret-api-key-here"
```

### Using Postman

1. Import the API endpoints into Postman
2. Set the base URL: `http://localhost:8080/api`
3. Add the header: `X-API-Key: your-secret-api-key-here`
4. Test each endpoint with sample data

## Error Codes

- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized (Invalid API Key)
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Internal Server Error

## Features

- ✅ RESTful API design
- ✅ Standardized JSON responses
- ✅ API key authentication
- ✅ Input validation
- ✅ Pagination support
- ✅ Search functionality
- ✅ Soft deletes
- ✅ Error handling
- ✅ Database migrations
- ✅ Model relationships
- ✅ CORS support (can be configured)

## Next Steps

1. **Implement JWT authentication** for better security
2. **Add rate limiting** to prevent abuse
3. **Add API versioning** (v1, v2, etc.)
4. **Implement file upload** for featured images
5. **Add comprehensive logging**
6. **Create API tests** with PHPUnit
7. **Add API documentation** with Swagger/OpenAPI