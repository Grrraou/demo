# Testing Guide

## Overview
This guide covers testing strategies, tools, and best practices for the ERP system to ensure code quality, reliability, and maintainability.

## Testing Stack

### Technologies Used
- **PHPUnit**: Unit and integration testing
- **Laravel Dusk**: Browser testing (if needed)
- **Faker**: Test data generation
- **Database Migrations**: Test database setup
- **SQLite**: In-memory testing database

### Test Structure
```
tests/
├── Unit/                  # Unit tests
│   ├── Models/
│   ├── Managers/
│   ├── Repositories/
│   └── Services/
├── Feature/               # Feature tests
│   ├── Api/
│   ├── Web/
│   ├── Livewire/
│   └── Auth/
├── Browser/              # Browser tests (Dusk)
├── TestCase.php          # Base test case
└── CreatesApplication.php # Test setup
```

## Configuration

### PHPUnit Configuration
```xml
<!-- phpunit.xml -->
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="./vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true">
    <testsuites>
        <testsuite name="Unit">
            <directory suffix="Test.php">./tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory suffix="Test.php">./tests/Feature</directory>
        </testsuite>
    </testsuites>
    <coverage processUncoveredFiles="true">
        <include>
            <directory suffix=".php">./app</directory>
        </include>
        <exclude>
            <directory suffix=".php">./app/Providers</directory>
        </exclude>
    </coverage>
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="BCRYPT_ROUNDS" value="4"/>
        <env name="CACHE_DRIVER" value="array"/>
        <env name="DB_CONNECTION" value="sqlite"/>
        <env name="DB_DATABASE" value=":memory:"/>
        <env name="MAIL_MAILER" value="array"/>
        <env name="QUEUE_CONNECTION" value="sync"/>
        <env name="SESSION_DRIVER" value="array"/>
        <env name="TELESCOPE_ENABLED" value="false"/>
    </php>
</phpunit>
```

### Test Environment
```env
# .env.testing
APP_ENV=testing
APP_DEBUG=true
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
CACHE_DRIVER=array
SESSION_DRIVER=array
QUEUE_DRIVER=sync
MAIL_DRIVER=array
```

## Base Test Setup

### TestCase Class
```php
<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\OwnedCompany;
use App\Models\TeamMember;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, RefreshDatabase;

    protected User $user;
    protected User $admin;
    protected OwnedCompany $company;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->createTestUser();
        $this->createTestCompany();
        $this->createTestAdmin();
    }

    protected function createTestUser(): void
    {
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
        ]);
    }

    protected function createTestAdmin(): void
    {
        $this->admin = User::factory()->create([
            'email' => 'admin@example.com',
        ]);
    }

    protected function createTestCompany(): void
    {
        $this->company = OwnedCompany::factory()->create([
            'name' => 'Test Company',
        ]);

        TeamMember::factory()->create([
            'user_id' => $this->user->id,
            'owned_company_id' => $this->company->id,
            'role' => 'user',
        ]);

        TeamMember::factory()->create([
            'user_id' => $this->admin->id,
            'owned_company_id' => $this->company->id,
            'role' => 'admin',
        ]);

        $this->user->update(['current_company_id' => $this->company->id]);
        $this->admin->update(['current_company_id' => $this->company->id]);
    }

    protected function actingAsUser(): self
    {
        return $this->actingAs($this->user);
    }

    protected function actingAsAdmin(): self
    {
        return $this->actingAs($this->admin);
    }
}
```

## Unit Testing

### Model Tests
```php
<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Customers\Customer;
use App\Models\OwnedCompany;

class CustomerTest extends TestCase
{
    public function test_customer_can_be_created(): void
    {
        $customer = Customer::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $this->assertInstanceOf(Customer::class, $customer);
        $this->assertEquals('John Doe', $customer->name);
        $this->assertEquals('john@example.com', $customer->email);
    }

    public function test_customer_belongs_to_company(): void
    {
        $customer = Customer::factory()->create();

        $this->assertInstanceOf(OwnedCompany::class, $customer->company);
        $this->assertEquals($this->company->id, $customer->owned_company_id);
    }

    public function test_customer_has_soft_deletes(): void
    {
        $customer = Customer::factory()->create();
        $customer->delete();

        $this->assertSoftDeleted('customers', ['id' => $customer->id]);
        $this->assertNotNull($customer->deleted_at);
    }

    public function test_customer_scope_active(): void
    {
        Customer::factory()->create(['status' => 'active']);
        Customer::factory()->create(['status' => 'inactive']);

        $activeCustomers = Customer::active()->get();
        $inactiveCustomers = Customer::inactive()->get();

        $this->assertCount(1, $activeCustomers);
        $this->assertCount(1, $inactiveCustomers);
    }
}
```

### Repository Tests
```php
<?php

namespace Tests\Unit\Repositories;

use Tests\TestCase;
use App\Repositories\Customers\CustomerRepository;
use App\Models\Customers\Customer;

class CustomerRepositoryTest extends TestCase
{
    private CustomerRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new CustomerRepository();
    }

    public function test_can_create_customer(): void
    {
        $data = [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'phone' => '+1234567890',
        ];

        $customer = $this->repository->create($data);

        $this->assertInstanceOf(Customer::class, $customer);
        $this->assertDatabaseHas('customers', [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
        ]);
    }

    public function test_can_find_customer_by_id(): void
    {
        $customer = Customer::factory()->create();

        $found = $this->repository->findById($customer->id);

        $this->assertInstanceOf(Customer::class, $found);
        $this->assertEquals($customer->id, $found->id);
    }

    public function test_can_update_customer(): void
    {
        $customer = Customer::factory()->create();
        $updateData = ['name' => 'Updated Name'];

        $updated = $this->repository->update($customer->id, $updateData);

        $this->assertEquals('Updated Name', $updated->name);
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_can_delete_customer(): void
    {
        $customer = Customer::factory()->create();

        $result = $this->repository->delete($customer->id);

        $this->assertTrue($result);
        $this->assertSoftDeleted('customers', ['id' => $customer->id]);
    }

    public function test_can_paginate_customers(): void
    {
        Customer::factory()->count(25)->create();

        $paginated = $this->repository->paginate(10);

        $this->assertEquals(10, $paginated->perPage());
        $this->assertEquals(25, $paginated->total());
        $this->assertEquals(3, $paginated->lastPage());
    }
}
```

### Manager Tests
```php
<?php

namespace Tests\Unit\Managers;

use Tests\TestCase;
use App\Managers\Customers\CustomerManager;
use App\Repositories\Customers\CustomerRepositoryInterface;
use Mockery;

class CustomerManagerTest extends TestCase
{
    private CustomerManager $manager;
    private CustomerRepositoryInterface $repositoryMock;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->repositoryMock = Mockery::mock(CustomerRepositoryInterface::class);
        $this->manager = new CustomerManager($this->repositoryMock);
    }

    public function test_can_create_customer(): void
    {
        $data = [
            'name' => 'Test Customer',
            'email' => 'test@example.com',
        ];

        $expectedCustomer = new \stdClass();
        $expectedCustomer->id = 'uuid';
        $expectedCustomer->name = 'Test Customer';

        $this->repositoryMock
            ->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($expectedCustomer);

        $result = $this->manager->createCustomer($data);

        $this->assertEquals($expectedCustomer, $result);
    }

    public function test_throws_exception_on_create_failure(): void
    {
        $data = ['name' => 'Test Customer'];

        $this->repositoryMock
            ->shouldReceive('create')
            ->once()
            ->andThrow(new \Exception('Database error'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to create customer: Database error');

        $this->manager->createCustomer($data);
    }

    public function test_can_update_customer(): void
    {
        $customerId = 'customer-uuid';
        $updateData = ['name' => 'Updated Name'];

        $this->repositoryMock
            ->shouldReceive('update')
            ->once()
            ->with($customerId, $updateData)
            ->andReturn((object) ['name' => 'Updated Name']);

        $result = $this->manager->updateCustomer($customerId, $updateData);

        $this->assertEquals('Updated Name', $result->name);
    }
}
```

## Feature Testing

### API Endpoint Tests
```php
<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\Customers\Customer;

class CustomerApiTest extends TestCase
{
    public function test_can_list_customers(): void
    {
        Customer::factory()->count(5)->create();

        $response = $this->actingAsUser()
                        ->getJson('/api/customers');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'email',
                            'phone',
                            'created_at',
                        ]
                    ],
                    'meta' => [
                        'current_page',
                        'per_page',
                        'total',
                    ]
                ]);
    }

    public function test_can_create_customer(): void
    {
        $data = [
            'name' => 'New Customer',
            'email' => 'new@example.com',
            'phone' => '+1234567890',
            'address' => '123 Test St',
        ];

        $response = $this->actingAsUser()
                        ->postJson('/api/customers', $data);

        $response->assertStatus(201)
                ->assertJsonFragment(['name' => 'New Customer'])
                ->assertJsonFragment(['email' => 'new@example.com']);

        $this->assertDatabaseHas('customers', [
            'name' => 'New Customer',
            'email' => 'new@example.com',
        ]);
    }

    public function test_cannot_create_customer_with_invalid_data(): void
    {
        $data = [
            'name' => '',
            'email' => 'invalid-email',
        ];

        $response = $this->actingAsUser()
                        ->postJson('/api/customers', $data);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name', 'email']);
    }

    public function test_can_show_customer(): void
    {
        $customer = Customer::factory()->create();

        $response = $this->actingAsUser()
                        ->getJson("/api/customers/{$customer->id}");

        $response->assertStatus(200)
                ->assertJsonFragment(['id' => $customer->id]);
    }

    public function test_can_update_customer(): void
    {
        $customer = Customer::factory()->create();
        $updateData = ['name' => 'Updated Name'];

        $response = $this->actingAsUser()
                        ->putJson("/api/customers/{$customer->id}", $updateData);

        $response->assertStatus(200)
                ->assertJsonFragment(['name' => 'Updated Name']);
    }

    public function test_can_delete_customer(): void
    {
        $customer = Customer::factory()->create();

        $response = $this->actingAsUser()
                        ->deleteJson("/api/customers/{$customer->id}");

        $response->assertStatus(204);
        $this->assertSoftDeleted('customers', ['id' => $customer->id]);
    }

    public function test_unauthenticated_user_cannot_access_api(): void
    {
        $response = $this->getJson('/api/customers');

        $response->assertStatus(401);
    }
}
```

### Web Route Tests
```php
<?php

namespace Tests\Feature\Web;

use Tests\TestCase;
use App\Models\Customers\Customer;

class CustomerWebTest extends TestCase
{
    public function test_can_view_customers_page(): void
    {
        Customer::factory()->count(3)->create();

        $response = $this->actingAsUser()
                        ->get('/customers');

        $response->assertStatus(200)
                ->assertViewIs('customers.index')
                ->assertViewHas('customers');
    }

    public function test_can_view_create_customer_page(): void
    {
        $response = $this->actingAsUser()
                        ->get('/customers/create');

        $response->assertStatus(200)
                ->assertViewIs('customers.create');
    }

    public function test_can_store_customer(): void
    {
        $data = [
            'name' => 'Web Customer',
            'email' => 'web@example.com',
        ];

        $response = $this->actingAsUser()
                        ->post('/customers', $data);

        $response->assertRedirect('/customers')
                ->assertSessionHas('success');

        $this->assertDatabaseHas('customers', [
            'name' => 'Web Customer',
            'email' => 'web@example.com',
        ]);
    }

    public function test_can_view_customer_details(): void
    {
        $customer = Customer::factory()->create();

        $response = $this->actingAsUser()
                        ->get("/customers/{$customer->id}");

        $response->assertStatus(200)
                ->assertViewIs('customers.show')
                ->assertViewHas('customer', function ($viewCustomer) use ($customer) {
                    return $viewCustomer->id === $customer->id;
                });
    }
}
```

### Livewire Component Tests
```php
<?php

namespace Tests\Feature\Livewire;

use Tests\TestCase;
use App\Livewire\Customers\CustomerList;
use App\Models\Customers\Customer;
use Livewire\Livewire;

class CustomerListTest extends TestCase
{
    public function test_can_render_component(): void
    {
        Livewire::test(CustomerList::class)
            ->assertStatus(200);
    }

    public function test_can_list_customers(): void
    {
        Customer::factory()->count(5)->create();

        Livewire::test(CustomerList::class)
            ->assertViewHas('customers')
            ->assertSee('Customer 1')
            ->assertSee('Customer 2');
    }

    public function test_can_search_customers(): void
    {
        Customer::factory()->create(['name' => 'John Doe']);
        Customer::factory()->create(['name' => 'Jane Smith']);

        Livewire::test(CustomerList::class)
            ->set('search', 'John')
            ->assertSee('John Doe')
            ->assertDontSee('Jane Smith');
    }

    public function test_can_delete_customer(): void
    {
        $customer = Customer::factory()->create();

        Livewire::test(CustomerList::class)
            ->call('deleteCustomer', $customer->id)
            ->assertDispatched('customer-deleted');

        $this->assertSoftDeleted('customers', ['id' => $customer->id]);
    }

    public function test_can_paginate_customers(): void
    {
        Customer::factory()->count(25)->create();

        Livewire::test(CustomerList::class)
            ->assertViewHas('customers', function ($customers) {
                return $customers->count() === 15; // Default per page
            });
    }
}
```

## Database Testing

### Factory Definitions
```php
<?php

namespace Database\Factories\Customers;

use App\Models\Customers\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'owned_company_id' => OwnedCompany::factory(),
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => $this->faker->phoneNumber,
            'address' => $this->faker->address,
            'notes' => $this->faker->sentence,
            'tags' => $this->faker->words(3),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    public function withCompany(OwnedCompany $company): static
    {
        return $this->state(fn (array $attributes) => [
            'owned_company_id' => $company->id,
        ]);
    }
}
```

### Seeder Testing
```php
<?php

namespace Tests\Unit\Seeders;

use Tests\TestCase;
use Database\Seeders\CustomerSeeder;
use App\Models\Customers\Customer;

class CustomerSeederTest extends TestCase
{
    public function test_customer_seeder_creates_expected_data(): void
    {
        $this->seed(CustomerSeeder::class);

        $this->assertDatabaseCount('customers', 10);
        $this->assertDatabaseHas('customers', [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
        ]);
    }

    public function test_seeder_data_is_valid(): void
    {
        $this->seed(CustomerSeeder::class);

        $customers = Customer::all();
        
        foreach ($customers as $customer) {
            $this->assertNotEmpty($customer->name);
            $this->assertNotEmpty($customer->email);
            $this->assertValidEmail($customer->email);
        }
    }
}
```

## Testing Best Practices

### 1. Test Organization
- **Arrange, Act, Assert** pattern
- Descriptive test method names
- One assertion per test when possible
- Use setup methods for common code

### 2. Test Data Management
- Use factories for test data
- Clean up after each test
- Use in-memory database for speed
- Avoid external dependencies

### 3. Assertions
- Use specific assertions
- Test both success and failure cases
- Verify database state changes
- Check response formats

### 4. Mocking
- Mock external services
- Use interfaces for testability
- Avoid over-mocking
- Test real interactions when possible

## Running Tests

### Command Line
```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Unit/Models/CustomerTest.php

# Run specific test method
php artisan test --filter test_customer_can_be_created

# Run with coverage
php artisan test --coverage

# Run in parallel
php artisan test --parallel

# Generate coverage report
php artisan test --coverage --coverage-html=coverage
```

### Continuous Integration
```yaml
# .github/workflows/tests.yml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    services:
      postgres:
        image: postgres:15
        env:
          POSTGRES_DB: test_db
          POSTGRES_USER: test
          POSTGRES_PASSWORD: test
        options: >-
          --health-cmd pg_isready
          --health-interval 10s
          --health-timeout 5s
          --health-retries 5

    steps:
    - uses: actions/checkout@v3
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.2'
        extensions: pdo, pdo_pgsql, pgsql
        
    - name: Install dependencies
      run: composer install --no-progress --no-interaction
      
    - name: Copy environment file
      run: cp .env.example .env
      
    - name: Run tests
      run: php artisan test
      
    - name: Upload coverage
      uses: codecov/codecov-action@v3
      with:
        file: ./coverage.xml
```

## Test Coverage

### Coverage Goals
- **Models**: 90%+ coverage
- **Repositories**: 85%+ coverage
- **Managers**: 90%+ coverage
- **Controllers**: 80%+ coverage
- **Overall**: 80%+ coverage

### Coverage Reports
```bash
# Generate HTML coverage report
php artisan test --coverage --coverage-html=coverage

# Generate XML coverage report
php artisan test --coverage --coverage-clover=coverage.xml

# Show coverage summary
php artisan test --coverage
```

This comprehensive testing guide ensures that your ERP system maintains high code quality and reliability through systematic testing practices.
