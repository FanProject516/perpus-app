# Library Management System API Documentation

## Base URL
```
http://localhost:8000/api
```

## Authentication
This API uses Laravel Sanctum for authentication. Include the Bearer token in the Authorization header:
```
Authorization: Bearer {your-token}
```

## Default Users
After running seeders, you can use these accounts:

### Admin Account
- **Email**: admin@perpus.local
- **Password**: password123
- **Role**: admin (full access)

### Librarian Account
- **Email**: librarian@perpus.local
- **Password**: password123
- **Role**: librarian (manage books, loans)

### Member Account
- **Email**: john@example.com
- **Password**: password123
- **Role**: member (borrow books)

## API Endpoints

### Authentication

#### Register
```http
POST /api/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "phone": "08123456789",
    "address": "Jl. Contoh No. 1",
    "student_id": "STD001"
}
```

#### Login
```http
POST /api/login
Content-Type: application/json

{
    "email": "admin@perpus.local",
    "password": "password123"
}
```

#### Logout
```http
POST /api/logout
Authorization: Bearer {token}
```

#### Get Profile
```http
GET /api/profile
Authorization: Bearer {token}
```

#### Update Profile
```http
PUT /api/profile
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "Updated Name",
    "phone": "08987654321",
    "address": "New Address"
}
```

### Categories

#### List Categories
```http
GET /api/categories
Authorization: Bearer {token}

# Optional query parameters:
# ?active=1 - only active categories
# ?root=1 - only root categories
# ?with_books_count=1 - include books count
```

#### Create Category
```http
POST /api/categories
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "Programming",
    "description": "Books about programming",
    "parent_id": 3,
    "is_active": true
}
```

#### Get Category Tree
```http
GET /api/categories-tree
Authorization: Bearer {token}
```

#### Get Category Details
```http
GET /api/categories/{id}
Authorization: Bearer {token}
```

#### Update Category
```http
PUT /api/categories/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "Updated Category Name",
    "description": "Updated description"
}
```

#### Delete Category
```http
DELETE /api/categories/{id}
Authorization: Bearer {token}
```

### Books

#### List Books
```http
GET /api/books
Authorization: Bearer {token}

# Optional query parameters:
# ?search=laravel - search in title, author, ISBN
# ?category_id=1 - filter by category
# ?available=1 - only available books
# ?language=english - filter by language
# ?sort_by=title&sort_order=asc - sorting
# ?per_page=20 - pagination
```

#### Create Book
```http
POST /api/books
Authorization: Bearer {token}
Content-Type: multipart/form-data

{
    "title": "Laravel Programming Guide",
    "author": "John Developer",
    "isbn": "978-1234567890",
    "publisher": "Tech Publications",
    "year": 2023,
    "category_id": 1,
    "summary": "Comprehensive guide to Laravel framework",
    "total_copies": 5,
    "price": 250000,
    "language": "English",
    "pages": 450,
    "condition": "new",
    "location": "Shelf A-1",
    "cover": "(image file)"
}
```

#### Get Book Details
```http
GET /api/books/{id}
Authorization: Bearer {token}
```

#### Update Book
```http
PUT /api/books/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
    "title": "Updated Book Title",
    "total_copies": 10,
    "price": 300000
}
```

#### Delete Book
```http
DELETE /api/books/{id}
Authorization: Bearer {token}
```

#### Get Available Books
```http
GET /api/books-available
Authorization: Bearer {token}

# Same query parameters as list books
```

#### Get Book Statistics
```http
GET /api/books-statistics
Authorization: Bearer {token}
```

### Loans

#### List Loans
```http
GET /api/loans
Authorization: Bearer {token}

# Optional query parameters:
# ?user_id=1 - filter by user
# ?status=borrowed - filter by status (borrowed, overdue, returned)
# ?overdue=1 - only overdue loans
# ?from_date=2023-01-01 - from date filter
# ?to_date=2023-12-31 - to date filter
# ?sort_by=borrowed_at&sort_order=desc - sorting
# ?per_page=20 - pagination
```

#### Borrow Book
```http
POST /api/loans
Authorization: Bearer {token}
Content-Type: application/json

{
    "book_id": 1,
    "due_date": "2023-12-31"  // optional, defaults to 2 weeks
}
```

#### Get Loan Details
```http
GET /api/loans/{id}
Authorization: Bearer {token}
```

#### Return Book
```http
POST /api/loans/{id}/return
Authorization: Bearer {token}
```

#### Extend Loan
```http
POST /api/loans/{id}/extend
Authorization: Bearer {token}
Content-Type: application/json

{
    "days": 7
}
```

#### Get My Loans
```http
GET /api/my-loans
Authorization: Bearer {token}

# Optional query parameters:
# ?status=borrowed - filter by status
```

#### Get Overdue Loans (Admin/Librarian only)
```http
GET /api/loans-overdue
Authorization: Bearer {token}
```

#### Mark Overdue Loans (Admin/Librarian only)
```http
POST /api/loans-mark-overdue
Authorization: Bearer {token}
```

#### Get Loan Statistics (Admin/Librarian only)
```http
GET /api/loans-statistics
Authorization: Bearer {token}
```

### Health Check

#### Health Check
```http
GET /api/health
```

## Response Format

All API responses follow this format:

### Success Response
```json
{
    "success": true,
    "message": "Operation completed successfully",
    "data": {
        // Response data here
    }
}
```

### Error Response
```json
{
    "success": false,
    "message": "Error description",
    "errors": {
        "field_name": ["Validation error message"]
    }
}
```

### Pagination Response
```json
{
    "success": true,
    "data": {
        "data": [
            // Array of items
        ],
        "current_page": 1,
        "last_page": 5,
        "per_page": 15,
        "total": 75,
        "from": 1,
        "to": 15
    }
}
```

## Status Codes

- **200**: Success
- **201**: Created
- **401**: Unauthorized
- **403**: Forbidden
- **404**: Not Found
- **422**: Validation Error
- **500**: Server Error

## Testing the API

### Using cURL

1. **Login to get token:**
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@perpus.local","password":"password123"}'
```

2. **List categories:**
```bash
curl -X GET http://localhost:8000/api/categories \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

3. **Create a book:**
```bash
curl -X POST http://localhost:8000/api/books \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Test Book",
    "author": "Test Author",
    "category_id": 1,
    "total_copies": 3
  }'
```

### Using Postman

1. Import the collection using the endpoints above
2. Set up environment variables for base URL and token
3. Use the authentication endpoints to get a token
4. Set the token in the Authorization header for protected routes

## Rate Limiting

API requests are rate-limited to prevent abuse. Default limits:
- 60 requests per minute for authenticated users
- 5 requests per minute for unauthenticated users

## Notes

- All datetime fields are returned in `Y-m-d H:i:s` format
- File uploads (book covers) support JPEG, PNG, JPG, GIF formats up to 2MB
- ISBN must be unique across all books
- Users can have maximum 3 active loans (10 for librarians)
- Default loan period is 14 days
- Fines are calculated at 1000 IDR per day for overdue books