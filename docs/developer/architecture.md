# Architecture Overview

## System Architecture

This ERP system follows a clean architecture pattern with clear separation of concerns:

```
┌─────────────────┐
│   Controllers   │ ← HTTP Request Handling
├─────────────────┤
│    Managers     │ ← Business Logic
├─────────────────┤
│  Repositories   │ ← Data Access
├─────────────────┤
│     Models      │ ← Eloquent ORM
└─────────────────┘
```

## Core Principles

### 1. Separation of Concerns
- **Controllers**: Handle HTTP requests/responses only
- **Managers**: Contain business logic and orchestration
- **Repositories**: Handle all data access operations
- **Models**: Eloquent models with relationships only

### 2. Single Responsibility
Each class has one clear purpose:
- Controllers don't contain business logic
- Managers don't access database directly
- Repositories don't contain business rules
- Models don't handle business operations

### 3. Dependency Injection
All dependencies are injected through constructors:
```php
class CustomerController extends Controller
{
    public function __construct(
        private CustomerManager $customerManager
    ) {}
}
```

## Directory Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/          # API endpoints (JSON responses)
│   │   └── Web/          # Web controllers (View responses)
│   └── Middleware/       # Request filtering
├── Managers/             # Business logic layer
├── Repositories/         # Data access layer
├── Models/               # Eloquent models
├── Services/             # External services
├── Livewire/             # Interactive components
└── Enums/                # System enums
```

## Request Flow

### API Requests
```
HTTP Request → Route → Api Controller → Manager → Repository → Model → Database
                ↓
           JSON Response ← Manager ← Repository ← Model ← Database
```

### Web Requests
```
HTTP Request → Route → Web Controller → Manager → Repository → Model → Database
                ↓
           View Response ← Manager ← Repository ← Model ← Database
```

### Livewire Requests
```
Livewire Component → Manager → Repository → Model → Database
         ↓
   Component Update ← Manager ← Repository ← Model ← Database
```

## Module Structure

Each business module follows this pattern:

### Models (`app/Models/ModuleName/`)
```php
class Product extends Model
{
    protected $fillable = ['name', 'sku', 'category_id'];
    
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
```

### Repositories (`app/Repositories/ModuleName/`)
```php
class ProductRepository
{
    public function create(array $data): Product
    {
        return Product::create($data);
    }
    
    public function findBySku(string $sku): ?Product
    {
        return Product::where('sku', $sku)->first();
    }
}
```

### Managers (`app/Managers/ModuleName/`)
```php
class ProductManager
{
    public function __construct(
        private ProductRepository $repository,
        private StockManager $stockManager
    ) {}
    
    public function createProduct(array $data): Product
    {
        $product = $this->repository->create($data);
        $this->stockManager->initializeStock($product);
        return $product;
    }
}
```

### Controllers (`app/Http/Controllers/Api/ModuleName/`)
```php
class ProductController extends Controller
{
    public function __construct(
        private ProductManager $productManager
    ) {}
    
    public function store(StoreProductRequest $request)
    {
        $product = $this->productManager->createProduct($request->validated());
        return response()->json($product, 201);
    }
}
```

## Key Patterns

### 1. Repository Pattern
Encapsulates data access logic:
- Abstracts database operations
- Enables easy testing with mocks
- Provides consistent data interface

### 2. Manager Pattern
Orchestrates business operations:
- Coordinates multiple repositories
- Implements business rules
- Handles complex workflows

### 3. Request Validation
Form requests for input validation:
```php
class StoreProductRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products',
        ];
    }
}
```

### 4. Resource Controllers
Standard CRUD operations:
- `index()` - List resources
- `store()` - Create resource
- `show()` - Show single resource
- `update()` - Update resource
- `destroy()` - Delete resource

## Authentication & Authorization

### Authentication
- Laravel Sanctum for API tokens
- Session-based for web routes
- Multi-tenant company isolation

### Authorization
- Role-based permissions
- Middleware for route protection
- Policy classes for model authorization

## Database Design

### Relationships
- One-to-Many: Company → Users
- Many-to-Many: Products → Orders
- Polymorphic: Activities (any model)

### Constraints
- Foreign key constraints
- Unique constraints
- Check constraints

### Migrations
- Schema structure only
- No seed data in migrations
- Rollback support

## Frontend Architecture

### Blade Templates
- Component-based structure
- Livewire for interactivity
- Tailwind CSS for styling

### Livewire Components
- Real-time updates
- Form validation
- State management

### JavaScript
- Alpine.js for interactions
- HTMX for dynamic content
- Minimal custom JS

## Testing Strategy

### Unit Tests
- Repository tests
- Manager tests
- Model tests

### Feature Tests
- API endpoint tests
- Web route tests
- Livewire component tests

### Integration Tests
- Workflow tests
- Multi-module tests

## Performance Considerations

### Database Optimization
- Eager loading relationships
- Database indexes
- Query optimization

### Caching
- Redis for application cache
- Model caching
- Query result caching

### Queue System
- Background job processing
- Email notifications
- Report generation

## Security

### Input Validation
- Form request validation
- SQL injection prevention
- XSS protection

### Authentication Security
- Token-based API auth
- Session security
- Password policies

### Data Protection
- Company data isolation
- Permission checks
- Audit logging

This architecture provides a solid foundation for scalable, maintainable ERP development.
