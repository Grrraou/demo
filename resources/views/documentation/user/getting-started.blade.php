@extends('layouts.app')

@section('title', 'Getting Started Guide')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <nav class="flex items-center space-x-2 text-sm text-gray-500">
            <a href="{{ route('docs.index') }}" class="hover:text-gray-700">Documentation</a>
            <span>/</span>
            <a href="{{ route('docs.user.getting-started') }}" class="hover:text-gray-700">User Guides</a>
            <span>/</span>
            <span class="text-gray-900">Getting Started</span>
        </nav>
    </div>

    <div class="flex gap-8">
        <!-- Sidebar -->
        <div class="flex-shrink-0">
            @include('documentation._sidebar')
        </div>

        <!-- Main Content -->
        <div class="flex-1">
            <div class="bg-white rounded-lg shadow-md p-8">
                <div class="prose prose-lg max-w-none">
                    <h1 class="text-3xl font-bold text-gray-900 mb-6">🚀 Getting Started Guide</h1>
                    
                    <p class="text-lg text-gray-600 mb-8">
                        Welcome to the ERP system! This guide will help you get familiar with the basic navigation and features.
                    </p>

                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">🚀 First Steps</h2>
                    
                    <h3 class="text-xl font-semibold text-gray-900 mt-6 mb-3">1. Logging In</h3>
                    <ol class="list-decimal list-inside space-y-2 text-gray-700">
                        <li>Open your browser and navigate to the ERP system URL</li>
                        <li>Enter your credentials:
                            <ul class="list-disc list-inside ml-6 mt-2">
                                <li><strong>Email</strong>: Your registered email address</li>
                                <li><strong>Password</strong>: Your password</li>
                            </ul>
                        </li>
                        <li>Click "Sign In"</li>
                    </ol>

                    <h3 class="text-xl font-semibold text-gray-900 mt-6 mb-3">2. Understanding the Dashboard</h3>
                    <p class="text-gray-700 mb-4">After logging in, you'll see the main dashboard with:</p>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 ml-6">
                        <li><strong>Navigation Menu</strong> - Left sidebar with all modules</li>
                        <li><strong>Quick Actions</strong> - Common tasks and shortcuts</li>
                        <li><strong>Recent Activity</strong> - Latest updates across modules</li>
                        <li><strong>Statistics</strong> - Key metrics and charts</li>
                    </ul>

                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">📱 Navigation Basics</h2>
                    
                    <h3 class="text-xl font-semibold text-gray-900 mt-6 mb-3">Main Menu Structure</h3>
                    <p class="text-gray-700 mb-4">The navigation is organized into logical groups:</p>
                    
                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <h4 class="font-semibold text-gray-900 mb-3">🏢 Business Operations</h4>
                        <ul class="list-disc list-inside space-y-1 text-gray-700 ml-6">
                            <li><strong>Accounting</strong> - Financial management</li>
                            <li><strong>Inventory</strong> - Stock and product management</li>
                            <li><strong>Sales</strong> - Sales pipeline and orders</li>
                            <li><strong>Customers</strong> - Customer database</li>
                        </ul>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <h4 class="font-semibold text-gray-900 mb-3">💬 Communication</h4>
                        <ul class="list-disc list-inside space-y-1 text-gray-700 ml-6">
                            <li><strong>Chat</strong> - Team messaging</li>
                            <li><strong>Calendar</strong> - Events and scheduling</li>
                            <li><strong>Leads</strong> - Lead management</li>
                        </ul>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <h4 class="font-semibold text-gray-900 mb-3">⚙️ System</h4>
                        <ul class="list-disc list-inside space-y-1 text-gray-700 ml-6">
                            <li><strong>Users</strong> - Team member management</li>
                            <li><strong>Company</strong> - Organization settings</li>
                            <li><strong>Articles</strong> - Content management</li>
                        </ul>
                    </div>

                    <h3 class="text-xl font-semibold text-gray-900 mt-6 mb-3">Using the Interface</h3>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 ml-6">
                        <li><strong>Click</strong> on any menu item to open that module</li>
                        <li><strong>Use the search bar</strong> to quickly find records</li>
                        <li><strong>Filter and sort</strong> data using the controls above each table</li>
                        <li><strong>Use action buttons</strong> (Create, Edit, Delete) to manage records</li>
                    </ul>

                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">🔐 User Roles and Permissions</h2>
                    
                    <h3 class="text-xl font-semibold text-gray-900 mt-6 mb-3">Role Types</h3>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 ml-6">
                        <li><strong>Admin</strong> - Full access to all modules and settings</li>
                        <li><strong>User</strong> - Limited access based on assigned permissions</li>
                        <li><strong>Custom Roles</strong> - Configured by your administrator</li>
                    </ul>

                    <h3 class="text-xl font-semibold text-gray-900 mt-6 mb-3">Permission Levels</h3>
                    <p class="text-gray-700 mb-4">Each module has separate permissions for:</p>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 ml-6">
                        <li><strong>View</strong> - Read access to data</li>
                        <li><strong>Edit</strong> - Modify existing records</li>
                        <li><strong>Create</strong> - Add new records</li>
                        <li><strong>Delete</strong> - Remove records</li>
                    </ul>

                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">🧠 Understanding Business Logic</h2>
                    
                    <p class="text-gray-700 mb-4">
                        The ERP system is built around core business logic that automates and streamlines your organization's operations. Understanding these business principles will help you leverage the system effectively.
                    </p>

                    <h3 class="text-xl font-semibold text-gray-900 mt-6 mb-3">🏢 Multi-Company Architecture</h3>
                    <p class="text-gray-700 mb-4">
                        Our system supports multiple companies within a single installation, enabling:
                    </p>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 ml-6">
                        <li><strong>Data Isolation</strong> - Each company's data is completely separate and secure</li>
                        <li><strong>Shared Resources</strong> - Users can access multiple companies with appropriate permissions</li>
                        <li><strong>Independent Operations</strong> - Each company maintains its own accounting, inventory, and customer records</li>
                        <li><strong>Cross-Company Reporting</strong> - Consolidated views for parent companies or holdings</li>
                    </ul>

                    <h3 class="text-xl font-semibold text-gray-900 mt-6 mb-3">💰 Financial Flow Logic</h3>
                    <p class="text-gray-700 mb-4">
                        The accounting module follows standard business financial principles:
                    </p>
                    <div class="bg-blue-50 rounded-lg p-4 mb-4">
                        <h4 class="font-semibold text-blue-900 mb-2">Revenue Cycle</h4>
                        <ol class="list-decimal list-inside space-y-2 text-blue-800 ml-6">
                            <li><strong>Quote Creation</strong> - Generate price quotes for potential customers</li>
                            <li><strong>Order Processing</strong> - Convert quotes to sales orders when approved</li>
                            <li><strong>Invoice Generation</strong> - Create invoices from delivered orders/services</li>
                            <li><strong>Payment Tracking</strong> - Record customer payments and apply to invoices</li>
                            <li><strong>Revenue Recognition</strong> - Automatically recognize revenue based on payment terms</li>
                        </ol>
                    </div>
                    
                    <div class="bg-green-50 rounded-lg p-4 mb-4">
                        <h4 class="font-semibold text-green-900 mb-2">Expense Management</h4>
                        <ol class="list-decimal list-inside space-y-2 text-green-800 ml-6">
                            <li><strong>Purchase Orders</strong> - Create POs for suppliers and vendors</li>
                            <li><strong>Bill Processing</strong> - Convert received goods/services to bills</li>
                            <li><strong>Payment Scheduling</strong> - Schedule and track bill payments</li>
                            <li><strong>Cost Allocation</strong> - Distribute costs across departments or projects</li>
                            <li><strong>Expense Recognition</strong> - Match expenses with corresponding revenue periods</li>
                        </ol>
                    </div>

                    <h3 class="text-xl font-semibold text-gray-900 mt-6 mb-3">📦 Inventory Management Logic</h3>
                    <p class="text-gray-700 mb-4">
                        Smart inventory logic ensures optimal stock levels and cost efficiency:
                    </p>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 ml-6">
                        <li><strong>Just-in-Time Inventory</strong> - Automatic reordering based on sales velocity</li>
                        <li><strong>Multi-Location Tracking</strong> - Track stock across warehouses, stores, or locations</li>
                        <li><strong>Cost Flow Assumption</strong> - FIFO/LIFO/Weighted Average cost calculation methods</li>
                        <li><strong>Stock Valuation</strong> - Real-time inventory valuation for financial reporting</li>
                        <li><strong>Low Stock Alerts</strong> - Proactive notifications for reorder points</li>
                        <li><strong>Batch/Lot Tracking</strong> - Track products by batch numbers for recalls or expiration</li>
                    </ul>

                    <h3 class="text-xl font-semibold text-gray-900 mt-6 mb-3">� Customer Relationship Logic</h3>
                    <p class="text-gray-700 mb-4">
                        The system implements comprehensive CRM business logic:
                    </p>
                    <div class="bg-purple-50 rounded-lg p-4 mb-4">
                        <h4 class="font-semibold text-purple-900 mb-2">Customer Lifecycle</h4>
                        <ul class="list-disc list-inside space-y-2 text-purple-800 ml-6">
                            <li><strong>Lead Capture</strong> - Import leads from various sources (web forms, manual entry, imports)</li>
                            <li><strong>Lead Qualification</strong> - Score and prioritize leads based on criteria</li>
                            <li><strong>Opportunity Tracking</strong> - Convert qualified leads to sales opportunities</li>
                            <li><strong>Customer Creation</strong> - Establish customer records with credit terms and limits</li>
                            <li><strong>Relationship Management</strong> - Track interactions, communications, and preferences</li>
                            <li><strong>Retention Analysis</strong> - Monitor customer satisfaction and churn risk</li>
                        </ul>
                    </div>

                    <h3 class="text-xl font-semibold text-gray-900 mt-6 mb-3">🔄 Workflow Automation Logic</h3>
                    <p class="text-gray-700 mb-4">
                        The system automates business workflows to reduce manual effort:
                    </p>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 ml-6">
                        <li><strong>Approval Workflows</strong> - Multi-level approval for purchases, expenses, or discounts</li>
                        <li><strong>Automated Notifications</strong> - Email/SMS alerts for important events</li>
                        <li><strong>Scheduled Reports</strong> - Automatic generation and distribution of reports</li>
                        <li><strong>Data Validation Rules</strong> - Business rules to ensure data integrity</li>
                        <li><strong>Audit Trail</strong> - Complete tracking of all changes for compliance</li>
                    </ul>

                    <h3 class="text-xl font-semibold text-gray-900 mt-6 mb-3">📊 Business Intelligence Logic</h3>
                    <p class="text-gray-700 mb-4">
                        Built-in analytics provide actionable business insights:
                    </p>
                    <div class="bg-orange-50 rounded-lg p-4 mb-4">
                        <h4 class="font-semibold text-orange-900 mb-2">Key Performance Indicators</h4>
                        <ul class="list-disc list-inside space-y-2 text-orange-800 ml-6">
                            <li><strong>Financial KPIs</strong> - Gross margin, net profit, cash flow, receivables turnover</li>
                            <li><strong>Operational KPIs</strong> - Inventory turnover, order fulfillment rate, customer acquisition cost</li>
                            <li><strong>Sales KPIs</strong> - Conversion rate, average deal size, sales cycle length</li>
                            <li><strong>Customer KPIs</strong> - Customer lifetime value, churn rate, satisfaction scores</li>
                        </ul>
                    </div>

                    <h3 class="text-xl font-semibold text-gray-900 mt-6 mb-3">🔐 Security & Compliance Logic</h3>
                    <p class="text-gray-700 mb-4">
                        Enterprise-grade security with business context:
                    </p>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 ml-6">
                        <li><strong>Role-Based Access</strong> - Permissions aligned with job functions and responsibilities</li>
                        <li><strong>Data Encryption</strong> - End-to-end encryption for sensitive business data</li>
                        <li><strong>Compliance Features</strong> - GDPR, SOX, and industry-specific compliance support</li>
                        <li><strong>Segregation of Duties</strong> - Prevent conflicts of interest through access controls</li>
                        <li><strong>Audit Readiness</strong> - Complete audit logs and compliance reporting</li>
                    </ul>

                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mt-8">
                        <p class="text-yellow-800">
                            <strong>💡 Business Logic Tip:</strong> The system is designed to mirror real-world business processes. Understanding these underlying principles will help you configure the system to match your specific business requirements and industry best practices.
                        </p>
                    </div>
                    
                    <h3 class="text-xl font-semibold text-gray-900 mt-6 mb-3">Creating a New Record</h3>
                    <ol class="list-decimal list-inside space-y-2 text-gray-700">
                        <li>Navigate to the desired module</li>
                        <li>Click the "Create" or "Add New" button</li>
                        <li>Fill in the required fields (marked with *)</li>
                        <li>Click "Save" to create the record</li>
                    </ol>

                    <h3 class="text-xl font-semibold text-gray-900 mt-6 mb-3">Searching and Filtering</h3>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 ml-6">
                        <li><strong>Use the search bar</strong> for quick text searches</li>
                        <li><strong>Apply filters</strong> using the dropdown menus</li>
                        <li><strong>Sort columns</strong> by clicking column headers</li>
                        <li><strong>Export data</strong> using the export button</li>
                    </ul>

                    <h3 class="text-xl font-semibold text-gray-900 mt-6 mb-3">Working with Tables</h3>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 ml-6">
                        <li><strong>Select rows</strong> using checkboxes</li>
                        <li><strong>Perform bulk actions</strong> on selected items</li>
                        <li><strong>Navigate pages</strong> using pagination controls</li>
                        <li><strong>Adjust column width</strong> by dragging headers</li>
                    </ul>

                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">💡 Tips and Tricks</h2>
                    
                    <h3 class="text-xl font-semibold text-gray-900 mt-6 mb-3">Keyboard Shortcuts</h3>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 ml-6">
                        <li><code>Ctrl + /</code> - Open search</li>
                        <li><code>Ctrl + N</code> - Create new record (in most modules)</li>
                        <li><code>Esc</code> - Close modal or cancel action</li>
                        <li><code>Enter</code> - Save form (when focused on save button)</li>
                    </ul>

                    <h3 class="text-xl font-semibold text-gray-900 mt-6 mb-3">Best Practices</h3>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 ml-6">
                        <li><strong>Save frequently</strong> when working with complex forms</li>
                        <li><strong>Use descriptive names</strong> for easy searching later</li>
                        <li><strong>Check permissions</strong> if you can't access certain features</li>
                        <li><strong>Log out</strong> when finished, especially on shared devices</li>
                    </ul>

                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">🔄 Next Steps</h2>
                    <p class="text-gray-700 mb-4">After completing this guide:</p>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 ml-6">
                        <li><strong>Explore each module</strong> relevant to your role</li>
                        <li><strong>Practice common tasks</strong> in your work area</li>
                        <li><strong>Review module-specific guides</strong> for detailed instructions</li>
                        <li><strong>Set up your profile</strong> and preferences</li>
                    </ul>

                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">📞 Need Additional Help?</h2>
                    <p class="text-gray-700 mb-4">If you need assistance:</p>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 ml-6">
                        <li>Contact your system administrator</li>
                        <li>Check the FAQ section</li>
                        <li>Review module-specific documentation</li>
                        <li>Submit a support ticket (if available)</li>
                    </ul>

                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mt-8">
                        <p class="text-blue-800">
                            <strong>Welcome to the ERP system!</strong> We're here to help you succeed.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
