# Database Schema Documentation

## Overview
The ERP system uses PostgreSQL as the primary database with a well-structured schema supporting multi-tenant architecture.

## Database Configuration

### Connection Settings
```env
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=erp_system
DB_USERNAME=erp_user
DB_PASSWORD=secure_password
```

### Key Features
- **Multi-tenant**: Company-based data isolation
- **Soft Deletes**: Most models use soft deletes
- **Timestamps**: created_at, updated_at on all tables
- **UUID Primary Keys**: For security and scalability

## Core Tables

### Users & Authentication

#### users
```sql
CREATE TABLE users (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100),
    current_company_id UUID NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
);
```

#### owned_companies
```sql
CREATE TABLE owned_companies (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(255) NOT NULL,
    tax_id VARCHAR(50),
    address TEXT,
    phone VARCHAR(50),
    email VARCHAR(255),
    logo_url VARCHAR(500),
    settings JSONB DEFAULT '{}',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
);
```

#### team_members
```sql
CREATE TABLE team_members (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID NOT NULL REFERENCES users(id),
    owned_company_id UUID NOT NULL REFERENCES owned_companies(id),
    role VARCHAR(50) NOT NULL DEFAULT 'user',
    permissions JSONB DEFAULT '{}',
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    UNIQUE(user_id, owned_company_id)
);
```

### Customer Management

#### customers
```sql
CREATE TABLE customers (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    owned_company_id UUID NOT NULL REFERENCES owned_companies(id),
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(50),
    address TEXT,
    notes TEXT,
    tags JSONB DEFAULT '[]',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
);
```

#### customer_companies
```sql
CREATE TABLE customer_companies (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    owned_company_id UUID NOT NULL REFERENCES owned_companies(id),
    name VARCHAR(255) NOT NULL,
    tax_id VARCHAR(50),
    industry VARCHAR(100),
    website VARCHAR(255),
    address TEXT,
    phone VARCHAR(50),
    email VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
);
```

#### customer_contacts
```sql
CREATE TABLE customer_contacts (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    customer_company_id UUID REFERENCES customer_companies(id),
    customer_id UUID REFERENCES customers(id),
    name VARCHAR(255) NOT NULL,
    title VARCHAR(100),
    email VARCHAR(255),
    phone VARCHAR(50),
    department VARCHAR(100),
    is_primary BOOLEAN DEFAULT false,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
);
```

### Inventory Management

#### inventory_categories
```sql
CREATE TABLE inventory_categories (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    owned_company_id UUID NOT NULL REFERENCES owned_companies(id),
    name VARCHAR(255) NOT NULL,
    description TEXT,
    parent_id UUID REFERENCES inventory_categories(id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
);
```

#### inventory_products
```sql
CREATE TABLE inventory_products (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    owned_company_id UUID NOT NULL REFERENCES owned_companies(id),
    category_id UUID REFERENCES inventory_categories(id),
    sku VARCHAR(100) NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    unit VARCHAR(50) NOT NULL,
    price DECIMAL(10,2),
    cost DECIMAL(10,2),
    min_stock_level INTEGER DEFAULT 0,
    max_stock_level INTEGER DEFAULT NULL,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    UNIQUE(owned_company_id, sku)
);
```

#### inventory_stock_locations
```sql
CREATE TABLE inventory_stock_locations (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    owned_company_id UUID NOT NULL REFERENCES owned_companies(id),
    name VARCHAR(255) NOT NULL,
    address TEXT,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
);
```

#### inventory_stock
```sql
CREATE TABLE inventory_stock (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    product_id UUID NOT NULL REFERENCES inventory_products(id),
    location_id UUID NOT NULL REFERENCES inventory_stock_locations(id),
    quantity INTEGER NOT NULL DEFAULT 0,
    reserved_quantity INTEGER NOT NULL DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(product_id, location_id)
);
```

#### inventory_stock_movements
```sql
CREATE TABLE inventory_stock_movements (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    product_id UUID NOT NULL REFERENCES inventory_products(id),
    location_id UUID NOT NULL REFERENCES inventory_stock_locations(id),
    movement_type VARCHAR(20) NOT NULL, -- 'in', 'out', 'transfer'
    quantity INTEGER NOT NULL,
    reference_type VARCHAR(50), -- 'order', 'adjustment', 'purchase'
    reference_id UUID,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Sales Management

#### sales_quotes
```sql
CREATE TABLE sales_quotes (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    owned_company_id UUID NOT NULL REFERENCES owned_companies(id),
    customer_id UUID REFERENCES customers(id),
    customer_company_id UUID REFERENCES customer_companies(id),
    quote_number VARCHAR(50) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'draft',
    issue_date DATE NOT NULL,
    valid_until DATE,
    subtotal DECIMAL(10,2) NOT NULL,
    tax_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    total_amount DECIMAL(10,2) NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    UNIQUE(owned_company_id, quote_number)
);
```

#### sales_orders
```sql
CREATE TABLE sales_orders (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    owned_company_id UUID NOT NULL REFERENCES owned_companies(id),
    quote_id UUID REFERENCES sales_quotes(id),
    customer_id UUID REFERENCES customers(id),
    customer_company_id UUID REFERENCES customer_companies(id),
    order_number VARCHAR(50) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    order_date DATE NOT NULL,
    delivery_date DATE,
    subtotal DECIMAL(10,2) NOT NULL,
    tax_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    total_amount DECIMAL(10,2) NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    UNIQUE(owned_company_id, order_number)
);
```

#### sales_invoices
```sql
CREATE TABLE sales_invoices (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    owned_company_id UUID NOT NULL REFERENCES owned_companies(id),
    order_id UUID REFERENCES sales_orders(id),
    customer_id UUID REFERENCES customers(id),
    customer_company_id UUID REFERENCES customer_companies(id),
    invoice_number VARCHAR(50) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'draft',
    invoice_date DATE NOT NULL,
    due_date DATE,
    subtotal DECIMAL(10,2) NOT NULL,
    tax_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    total_amount DECIMAL(10,2) NOT NULL,
    paid_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    UNIQUE(owned_company_id, invoice_number)
);
```

#### sales_deliveries
```sql
CREATE TABLE sales_deliveries (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    owned_company_id UUID NOT NULL REFERENCES owned_companies(id),
    order_id UUID REFERENCES sales_orders(id),
    delivery_number VARCHAR(50) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    delivery_date DATE,
    tracking_number VARCHAR(100),
    carrier VARCHAR(100),
    address TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    UNIQUE(owned_company_id, delivery_number)
);
```

### Sales Line Items

#### sales_quote_items
```sql
CREATE TABLE sales_quote_items (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    quote_id UUID NOT NULL REFERENCES sales_quotes(id),
    product_id UUID REFERENCES inventory_products(id),
    description TEXT,
    quantity DECIMAL(10,2) NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    tax_rate DECIMAL(5,2) NOT NULL DEFAULT 0,
    total_amount DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### sales_order_items
```sql
CREATE TABLE sales_order_items (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    order_id UUID NOT NULL REFERENCES sales_orders(id),
    product_id UUID REFERENCES inventory_products(id),
    description TEXT,
    quantity DECIMAL(10,2) NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    tax_rate DECIMAL(5,2) NOT NULL DEFAULT 0,
    total_amount DECIMAL(10,2) NOT NULL,
    delivered_quantity DECIMAL(10,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Accounting

#### accounting_accounts
```sql
CREATE TABLE accounting_accounts (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    owned_company_id UUID NOT NULL REFERENCES owned_companies(id),
    code VARCHAR(20) NOT NULL,
    name VARCHAR(255) NOT NULL,
    type VARCHAR(20) NOT NULL, -- 'asset', 'liability', 'equity', 'revenue', 'expense'
    parent_id UUID REFERENCES accounting_accounts(id),
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    UNIQUE(owned_company_id, code)
);
```

#### accounting_journal_entries
```sql
CREATE TABLE accounting_journal_entries (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    owned_company_id UUID NOT NULL REFERENCES owned_companies(id),
    entry_number VARCHAR(50) NOT NULL,
    date DATE NOT NULL,
    description TEXT,
    reference_type VARCHAR(50),
    reference_id UUID,
    status VARCHAR(20) NOT NULL DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    UNIQUE(owned_company_id, entry_number)
);
```

#### accounting_journal_entry_lines
```sql
CREATE TABLE accounting_journal_entry_lines (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    journal_entry_id UUID NOT NULL REFERENCES accounting_journal_entries(id),
    account_id UUID NOT NULL REFERENCES accounting_accounts(id),
    debit_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    credit_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Chat & Communication

#### chat_conversations
```sql
CREATE TABLE chat_conversations (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    owned_company_id UUID NOT NULL REFERENCES owned_companies(id),
    type VARCHAR(20) NOT NULL DEFAULT 'direct', -- 'direct', 'group', 'channel'
    name VARCHAR(255),
    created_by UUID REFERENCES users(id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
);
```

#### chat_participants
```sql
CREATE TABLE chat_participants (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    conversation_id UUID NOT NULL REFERENCES chat_conversations(id),
    user_id UUID NOT NULL REFERENCES users(id),
    role VARCHAR(20) NOT NULL DEFAULT 'member', -- 'member', 'admin'
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_read_at TIMESTAMP NULL,
    UNIQUE(conversation_id, user_id)
);
```

#### chat_messages
```sql
CREATE TABLE chat_messages (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    conversation_id UUID NOT NULL REFERENCES chat_conversations(id),
    user_id UUID NOT NULL REFERENCES users(id),
    content TEXT NOT NULL,
    message_type VARCHAR(20) NOT NULL DEFAULT 'text', -- 'text', 'file', 'system'
    attachment_url VARCHAR(500),
    reply_to_id UUID REFERENCES chat_messages(id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
);
```

## Indexes

### Performance Indexes
```sql
-- User authentication
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_team_members_company_user ON team_members(owned_company_id, user_id);

-- Foreign keys
CREATE INDEX idx_customers_company ON customers(owned_company_id);
CREATE INDEX idx_products_company ON inventory_products(owned_company_id);
CREATE INDEX idx_orders_company ON sales_orders(owned_company_id);

-- Search indexes
CREATE INDEX idx_customers_search ON customers USING gin(to_tsvector('english', name || ' ' || email));
CREATE INDEX idx_products_search ON inventory_products USING gin(to_tsvector('english', name || ' ' || sku));

-- Date ranges
CREATE INDEX idx_orders_date ON sales_orders(order_date);
CREATE INDEX idx_entries_date ON accounting_journal_entries(date);
```

## Constraints

### Business Rules
```sql
-- Stock cannot be negative
ALTER TABLE inventory_stock ADD CONSTRAINT check_quantity_positive 
CHECK (quantity >= 0);

-- Journal entries must balance
ALTER TABLE accounting_journal_entries ADD CONSTRAINT check_entries_balanced 
CHECK ((SELECT SUM(debit_amount) - SUM(credit_amount) 
       FROM accounting_journal_entry_lines 
       WHERE journal_entry_id = accounting_journal_entries.id) = 0);

-- Quote items must have positive quantities
ALTER TABLE sales_quote_items ADD CONSTRAINT check_quantity_positive 
CHECK (quantity > 0);
```

## Data Relationships

### Entity Relationship Diagram
```
Users ←→ TeamMembers → OwnedCompanies
                     ↓
Customers ← CustomerCompanies ← CustomerContacts
                     ↓
SalesQuotes → SalesOrders → SalesInvoices → SalesDeliveries
     ↓              ↓              ↓
QuoteItems    OrderItems    DeliveryItems
     ↓              ↓              ↓
Products ← Categories
     ↓
Stock ← StockLocations
     ↓
StockMovements

Users ←→ ChatConversations ←→ ChatParticipants ←→ ChatMessages
```

## Multi-Tenant Isolation

### Row Level Security
All business data tables include `owned_company_id` to ensure data isolation:
```sql
-- Example RLS policy
CREATE POLICY company_isolation_policy ON customers
    FOR ALL TO authenticated_users
    USING (owned_company_id IN (
        SELECT owned_company_id FROM team_members 
        WHERE user_id = current_user_id()
    ));
```

### Global Scopes
Eloquent models apply global scopes for company filtering:
```php
protected static function booted()
{
    static::addGlobalScope('company', function ($query) {
        $query->where('owned_company_id', auth()->user()->current_company_id);
    });
}
```

## Migration Strategy

### Version Control
- All schema changes via Laravel migrations
- Migration files in `database/migrations/`
- Rollback support for all changes

### Data Seeding
- Structure: Migrations only
- Data: Seeders in `database/seeders/`
- Environment-specific seeders

### Backup Strategy
- Regular automated backups
- Point-in-time recovery
- Cross-environment migration scripts

## Performance Optimization

### Query Optimization
- Use appropriate indexes
- Avoid N+1 queries with eager loading
- Implement pagination for large datasets
- Use database views for complex queries

### Caching Strategy
- Cache frequently accessed data
- Implement query result caching
- Use Redis for session and application cache

### Monitoring
- Track slow queries
- Monitor database connections
- Set up performance alerts
