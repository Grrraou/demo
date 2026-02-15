# API Documentation

## Overview
The ERP system provides a RESTful API for all business operations with token-based authentication.

## Authentication

### API Token Authentication
All API endpoints require authentication using Laravel Sanctum tokens.

#### Login
```http
POST /api/login
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "password"
}
```

**Response:**
```json
{
    "token": "1|abc123...",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "user@example.com",
        "role": "user"
    }
}
```

#### Using the Token
```http
Authorization: Bearer 1|abc123...
```

#### Logout
```http
POST /api/logout
Authorization: Bearer 1|abc123...
```

## API Endpoints

### Customers

#### List Customers
```http
GET /api/customers
Authorization: Bearer {token}
```

**Query Parameters:**
- `per_page` (optional): Items per page (default: 15)
- `page` (optional): Page number
- `search` (optional): Search term

**Response:**
```json
{
    "data": [
        {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "phone": "+1234567890",
            "address": "123 Main St",
            "created_at": "2024-01-01T00:00:00Z",
            "updated_at": "2024-01-01T00:00:00Z"
        }
    ],
    "meta": {
        "current_page": 1,
        "per_page": 15,
        "total": 50
    }
}
```

#### Create Customer
```http
POST /api/customers
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "Jane Smith",
    "email": "jane@example.com",
    "phone": "+1234567890",
    "address": "456 Oak Ave"
}
```

#### Show Customer
```http
GET /api/customers/{id}
Authorization: Bearer {token}
```

#### Update Customer
```http
PUT /api/customers/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "Jane Smith Updated",
    "email": "jane.new@example.com"
}
```

#### Delete Customer
```http
DELETE /api/customers/{id}
Authorization: Bearer {token}
```

### Sales Module

#### Quotes
```http
GET /api/sales/quotes          # List quotes
POST /api/sales/quotes         # Create quote
GET /api/sales/quotes/{id}     # Show quote
PUT /api/sales/quotes/{id}     # Update quote
DELETE /api/sales/quotes/{id}  # Delete quote
```

#### Orders
```http
GET /api/sales/orders          # List orders
POST /api/sales/orders         # Create order
GET /api/sales/orders/{id}     # Show order
PUT /api/sales/orders/{id}     # Update order
DELETE /api/sales/orders/{id}  # Delete order
```

#### Invoices
```http
GET /api/sales/invoices        # List invoices
POST /api/sales/invoices       # Create invoice
GET /api/sales/invoices/{id}   # Show invoice
PUT /api/sales/invoices/{id}   # Update invoice
DELETE /api/sales/invoices/{id} # Delete invoice
```

### Inventory Module

#### Products
```http
GET /api/inventory/products        # List products
POST /api/inventory/products       # Create product
GET /api/inventory/products/{id}   # Show product
PUT /api/inventory/products/{id}   # Update product
DELETE /api/inventory/products/{id} # Delete product
```

#### Stock Movements
```http
GET /api/inventory/stock-movements           # List movements
POST /api/inventory/stock-movements          # Create movement
GET /api/inventory/stock-movements/{id}      # Show movement
```

### Accounting Module

#### Journal Entries
```http
GET /api/accounting/journal-entries        # List entries
POST /api/accounting/journal-entries       # Create entry
GET /api/accounting/journal-entries/{id}   # Show entry
PUT /api/accounting/journal-entries/{id}   # Update entry
DELETE /api/accounting/journal-entries/{id} # Delete entry
```

#### Chart of Accounts
```http
GET /api/accounting/accounts        # List accounts
POST /api/accounting/accounts       # Create account
GET /api/accounting/accounts/{id}   # Show account
PUT /api/accounting/accounts/{id}   # Update account
DELETE /api/accounting/accounts/{id} # Delete account
```

## Response Format

### Success Responses
- **200 OK**: Successful GET/PUT/PATCH
- **201 Created**: Successful POST
- **204 No Content**: Successful DELETE

### Error Responses
```json
{
    "message": "Validation error",
    "errors": {
        "email": ["The email field is required."],
        "phone": ["The phone must be a valid number."]
    }
}
```

### HTTP Status Codes
- **200**: Success
- **201**: Created
- **400**: Bad Request
- **401**: Unauthorized
- **403**: Forbidden
- **404**: Not Found
- **422**: Validation Error
- **500**: Server Error

## Pagination

### Paginated Responses
```json
{
    "data": [...],
    "links": {
        "first": "https://api.example.com/customers?page=1",
        "last": "https://api.example.com/customers?page=5",
        "prev": null,
        "next": "https://api.example.com/customers?page=2"
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 5,
        "per_page": 15,
        "to": 15,
        "total": 75
    }
}
```

### Query Parameters
- `page`: Page number (default: 1)
- `per_page`: Items per page (default: 15, max: 100)

## Filtering and Sorting

### Filtering
```http
GET /api/customers?status=active&created_after=2024-01-01
```

### Sorting
```http
GET /api/customers?sort=name&order=desc
```

### Searching
```http
GET /api/customers?search=john
```

## Rate Limiting

API requests are rate-limited to prevent abuse:
- **Standard**: 1000 requests per hour
- **Burst**: 100 requests per minute

Rate limit headers are included in responses:
```http
X-RateLimit-Limit: 1000
X-RateLimit-Remaining: 999
X-RateLimit-Reset: 1640995200
```

## Webhooks

### Configure Webhooks
Webhooks can be configured to receive real-time notifications:

```http
POST /api/webhooks
Authorization: Bearer {token}
Content-Type: application/json

{
    "url": "https://your-app.com/webhook",
    "events": ["customer.created", "order.updated"],
    "secret": "your-webhook-secret"
}
```

### Webhook Events
- `customer.created`
- `customer.updated`
- `customer.deleted`
- `order.created`
- `order.updated`
- `order.deleted`
- `invoice.created`
- `invoice.updated`

### Webhook Payload
```json
{
    "event": "customer.created",
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
    },
    "timestamp": "2024-01-01T12:00:00Z"
}
```

## SDK Examples

### JavaScript (Axios)
```javascript
const api = axios.create({
    baseURL: 'https://your-erp.com/api',
    headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
    }
});

// Get customers
const customers = await api.get('/customers');

// Create customer
const customer = await api.post('/customers', {
    name: 'John Doe',
    email: 'john@example.com'
});
```

### PHP (Guzzle)
```php
$client = new GuzzleHttp\Client([
    'base_uri' => 'https://your-erp.com/api/',
    'headers' => [
        'Authorization' => 'Bearer ' . $token,
        'Content-Type' => 'application/json'
    ]
]);

// Get customers
$response = $client->get('customers');
$customers = json_decode($response->getBody(), true);

// Create customer
$response = $client->post('customers', [
    'json' => [
        'name' => 'John Doe',
        'email' => 'john@example.com'
    ]
]);
```

### Python (Requests)
```python
import requests

headers = {
    'Authorization': f'Bearer {token}',
    'Content-Type': 'application/json'
}

# Get customers
response = requests.get('https://your-erp.com/api/customers', headers=headers)
customers = response.json()

# Create customer
customer_data = {
    'name': 'John Doe',
    'email': 'john@example.com'
}
response = requests.post('https://your-erp.com/api/customers', 
                        json=customer_data, headers=headers)
```

## Testing the API

### Using cURL
```bash
# Login
curl -X POST https://your-erp.com/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password"}'

# Get customers
curl -X GET https://your-erp.com/api/customers \
  -H "Authorization: Bearer YOUR_TOKEN"

# Create customer
curl -X POST https://your-erp.com/api/customers \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"John Doe","email":"john@example.com"}'
```

### Postman Collection
Import the provided Postman collection for easy API testing with pre-configured endpoints and authentication.

## API Versioning

The API uses URL versioning:
- Current version: `/api/v1/`
- Previous versions: `/api/v1/` (maintained for backward compatibility)

Version-specific breaking changes will increment the version number.
