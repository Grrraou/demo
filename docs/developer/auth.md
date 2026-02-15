# Authentication & Security Documentation

## Overview
The ERP system implements a comprehensive authentication and authorization system with multi-tenant support, role-based permissions, and secure API access.

## Authentication Methods

### 1. Web Authentication (Session-based)
Traditional Laravel session authentication for web interface:
```php
// Login
Route::post('/login', [LoginController::class, 'login']);

// Middleware protection
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});
```

### 2. API Authentication (Sanctum Tokens)
Token-based authentication for API access:
```php
// Token generation
$token = $user->createToken('api-access')->plainTextToken;

// Token usage
Authorization: Bearer 1|abc123def456...
```

### 3. Multi-Tenant Authentication
Company-based data isolation:
```php
// User can belong to multiple companies
$user->current_company_id = $companyId;

// All queries automatically scoped to current company
Model::addGlobalScope('company', function ($query) {
    $query->where('owned_company_id', auth()->user()->current_company_id);
});
```

## User Management

### User Model
```php
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    
    protected $fillable = [
        'name',
        'email',
        'password',
        'current_company_id',
    ];
    
    public function ownedCompanies()
    {
        return $this->belongsToMany(OwnedCompany::class, 'team_members')
                    ->withPivot(['role', 'permissions', 'is_active']);
    }
    
    public function currentCompany()
    {
        return $this->belongsTo(OwnedCompany::class, 'current_company_id');
    }
}
```

### Team Member Model
```php
class TeamMember extends Model
{
    protected $fillable = [
        'user_id',
        'owned_company_id',
        'role',
        'permissions',
        'is_active',
    ];
    
    protected $casts = [
        'permissions' => 'array',
    ];
    
    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions ?? []);
    }
}
```

## Role-Based Access Control

### Role System
```php
// Roles defined in config/roles.php
return [
    'admin' => [
        'name' => 'Administrator',
        'permissions' => ['*'], // All permissions
    ],
    'manager' => [
        'name' => 'Manager',
        'permissions' => [
            'view.*',
            'edit.customers',
            'edit.sales',
            'view.accounting',
        ],
    ],
    'user' => [
        'name' => 'User',
        'permissions' => [
            'view.customers',
            'view.sales',
            'edit.own_profile',
        ],
    ],
];
```

### Permission Middleware
```php
class EnsureTeamMemberCanEditCustomers
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->user()->currentTeamMember()->hasPermission('edit.customers')) {
            abort(403, 'Unauthorized');
        }
        
        return $next($request);
    }
}
```

### Route Protection
```php
// API Routes
Route::middleware(['auth:sanctum', 'permission:edit.customers'])
    ->group(function () {
        Route::apiResource('customers', CustomerController::class);
    });

// Web Routes
Route::middleware(['auth', 'permission:view.accounting'])
    ->prefix('accounting')
    ->group(function () {
        Route::get('/', [AccountingController::class, 'index']);
    });
```

## Security Features

### 1. Password Security
```php
// Strong password validation
'password' => 'required|string|min:12|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',

// Password hashing with bcrypt
$password = Hash::make($userPassword);

// Password verification
if (Hash::check($inputPassword, $hashedPassword)) {
    // Password is correct
}
```

### 2. Session Security
```php
// Session configuration
'session' => [
    'driver' => 'redis',
    'lifetime' => 120, // 2 hours
    'expire_on_close' => false,
    'encrypt' => true,
    'secure' => env('APP_ENV') === 'production',
    'http_only' => true,
    'same_site' => 'lax',
],
```

### 3. CSRF Protection
```php
// CSRF token validation
@csrf
// In forms
<input type="hidden" name="_token" value="{{ csrf_token() }}">
```

### 4. Rate Limiting
```php
// API rate limiting
Route::middleware('throttle:1000,1')->group(function () {
    Route::apiResource('customers', CustomerController::class);
});

// Login rate limiting
Route::post('/login', [LoginController::class])
    ->middleware('throttle:5,1'); // 5 attempts per minute
```

## API Security

### 1. Token Management
```php
// Token abilities
$token = $user->createToken('api-access', [
    'customers:read',
    'customers:write',
])->plainTextToken;

// Token expiration
$token = $user->createToken('api-access')
    ->expiresAt(now()->addDays(30))
    ->plainTextToken;
```

### 2. Token Revocation
```php
// Revoke all tokens
$user->tokens()->delete();

// Revoke specific token
$user->tokens()->where('id', $tokenId)->delete();
```

### 3. API Request Validation
```php
class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->tokenCan('customers:write');
    }
    
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers',
            'phone' => 'nullable|string|max:20',
        ];
    }
}
```

## Data Encryption

### 1. Sensitive Data Encryption
```php
// Encrypt sensitive fields
class Customer extends Model
{
    protected $casts = [
        'email' => 'encrypted',
        'phone' => 'encrypted',
        'address' => 'encrypted',
    ];
}
```

### 2. Environment Variables
```php
// .env file (never commit)
DB_PASSWORD=secure_database_password
MAIL_PASSWORD=secure_mail_password
API_SECRET_KEY=your_secret_key

// Access in code
$encrypted = encrypt($sensitiveData);
$decrypted = decrypt($encrypted);
```

## Audit Logging

### 1. User Activity Logging
```php
class LogUserActivity
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        ActivityLog::create([
            'user_id' => auth()->id(),
            'company_id' => auth()->user()->current_company_id,
            'action' => $request->method() . ' ' . $request->path(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'data' => $request->except(['password', 'token']),
        ]);
        
        return $response;
    }
}
```

### 2. Data Change Tracking
```php
class Customer extends Model
{
    protected static function booted()
    {
        static::updated(function ($customer) {
            $changes = $customer->getDirty();
            
            foreach ($changes as $field => $newValue) {
                AuditLog::create([
                    'model_type' => Customer::class,
                    'model_id' => $customer->id,
                    'field' => $field,
                    'old_value' => $customer->getOriginal($field),
                    'new_value' => $newValue,
                    'user_id' => auth()->id(),
                ]);
            }
        });
    }
}
```

## Security Headers

### 1. HTTP Security Headers
```php
// In middleware
$response->headers->set('X-Frame-Options', 'DENY');
$response->headers->set('X-Content-Type-Options', 'nosniff');
$response->headers->set('X-XSS-Protection', '1; mode=block');
$response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
$response->headers->set('Content-Security-Policy', "default-src 'self'");
```

### 2. CORS Configuration
```php
// config/cors.php
return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['https://yourapp.com'],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
```

## Multi-Tenant Security

### 1. Company Data Isolation
```php
class CompanyScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        if (auth()->check()) {
            $builder->where('owned_company_id', auth()->user()->current_company_id);
        }
    }
}
```

### 2. Cross-Company Access Prevention
```php
class EnsureCurrentOwnedCompany
{
    public function handle(Request $request, Closure $next)
    {
        $companyId = $request->route('company');
        
        if ($companyId && !auth()->user()->ownedCompanies()->contains($companyId)) {
            abort(403, 'Access to this company is not allowed');
        }
        
        return $next($request);
    }
}
```

## Testing Security

### 1. Authentication Tests
```php
class AuthTest extends TestCase
{
    public function test_login_with_valid_credentials()
    {
        $response = $this->post('/api/login', [
            'email' => $this->user->email,
            'password' => 'password',
        ]);
        
        $response->assertStatus(200)
                ->assertJsonStructure(['token', 'user']);
    }
    
    public function test_login_with_invalid_credentials()
    {
        $response = $this->post('/api/login', [
            'email' => 'invalid@example.com',
            'password' => 'wrong-password',
        ]);
        
        $response->assertStatus(401);
    }
}
```

### 2. Authorization Tests
```php
class AuthorizationTest extends TestCase
{
    public function test_user_cannot_access_admin_routes()
    {
        $this->actingAs($this->user)
             ->get('/admin/users')
             ->assertStatus(403);
    }
    
    public function test_admin_can_access_admin_routes()
    {
        $this->actingAs($this->admin)
             ->get('/admin/users')
             ->assertStatus(200);
    }
}
```

## Security Best Practices

### 1. Development Practices
- Never commit sensitive data
- Use environment variables for secrets
- Implement proper input validation
- Use parameterized queries
- Enable HTTPS in production

### 2. Regular Security Tasks
- Update dependencies regularly
- Review and audit permissions
- Monitor security advisories
- Perform security testing
- Backup and recovery testing

### 3. Monitoring and Alerting
- Failed login attempts
- Unusual API usage patterns
- Permission changes
- Data access patterns
- Security events logging

## Compliance Considerations

### 1. Data Protection (GDPR)
- Right to be forgotten
- Data portability
- Consent management
- Data breach notifications

### 2. Industry Standards
- SOC 2 compliance
- ISO 27001 standards
- Payment card industry (PCI DSS) if applicable

This comprehensive authentication and security system ensures that your ERP data remains protected while providing flexible access controls for different user roles and requirements.
