@extends('layouts.app')

@section('title', 'Documentation')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-4">📚 ERP System Documentation</h1>
        <p class="text-lg text-gray-600">
            Welcome to the comprehensive documentation for the ERP system. This documentation is organized for both users and developers to understand and work with the system effectively.
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
        <!-- User Documentation -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">👥 User Documentation</h2>
            <p class="text-gray-600 mb-6">
                Guides and tutorials for using the ERP system effectively.
            </p>
            
            <div class="space-y-3">
                <a href="{{ route('docs.user.getting-started') }}" class="block p-3 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                    <h3 class="font-semibold text-blue-900">🚀 Getting Started</h3>
                    <p class="text-sm text-blue-700">Basic setup and navigation</p>
                </a>
                
                <a href="{{ route('docs.user.accounting') }}" class="block p-3 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                    <h3 class="font-semibold text-green-900">🧮 Accounting</h3>
                    <p class="text-sm text-green-700">Financial management</p>
                </a>
                
                <a href="{{ route('docs.user.inventory') }}" class="block p-3 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                    <h3 class="font-semibold text-purple-900">📦 Inventory</h3>
                    <p class="text-sm text-purple-700">Stock and product management</p>
                </a>
                
                <a href="{{ route('docs.user.sales') }}" class="block p-3 bg-orange-50 rounded-lg hover:bg-orange-100 transition-colors">
                    <h3 class="font-semibold text-orange-900">💰 Sales</h3>
                    <p class="text-sm text-orange-700">Quotes, orders, and invoicing</p>
                </a>
                
                <a href="{{ route('docs.user.customers') }}" class="block p-3 bg-pink-50 rounded-lg hover:bg-pink-100 transition-colors">
                    <h3 class="font-semibold text-pink-900">👥 Customers</h3>
                    <p class="text-sm text-pink-700">Customer and contact management</p>
                </a>
                
                <a href="{{ route('docs.user.chat') }}" class="block p-3 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition-colors">
                    <h3 class="font-semibold text-indigo-900">💬 Chat</h3>
                    <p class="text-sm text-indigo-700">Team communication</p>
                </a>
                
                <a href="{{ route('docs.user.calendar') }}" class="block p-3 bg-teal-50 rounded-lg hover:bg-teal-100 transition-colors">
                    <h3 class="font-semibold text-teal-900">📅 Calendar</h3>
                    <p class="text-sm text-teal-700">Events and scheduling</p>
                </a>
                
                <a href="{{ route('docs.user.leads') }}" class="block p-3 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                    <h3 class="font-semibold text-red-900">🎯 Leads</h3>
                    <p class="text-sm text-red-700">Lead tracking and conversion</p>
                </a>
                
                <a href="{{ route('docs.user.faq') }}" class="block p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                    <h3 class="font-semibold text-gray-900">❓ FAQ</h3>
                    <p class="text-sm text-gray-700">Frequently asked questions</p>
                </a>
            </div>
        </div>

        <!-- Developer Documentation -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">💻 Developer Documentation</h2>
            <p class="text-gray-600 mb-6">
                Technical documentation for developers working with the ERP system.
            </p>
            
            <div class="space-y-3">
                <a href="{{ route('docs.developer.architecture') }}" class="block p-3 bg-cyan-50 rounded-lg hover:bg-cyan-100 transition-colors">
                    <h3 class="font-semibold text-cyan-900">🏗️ Architecture</h3>
                    <p class="text-sm text-cyan-700">System design and patterns</p>
                </a>
                
                <a href="{{ route('docs.developer.api') }}" class="block p-3 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                    <h3 class="font-semibold text-blue-900">🔌 API</h3>
                    <p class="text-sm text-blue-700">REST API endpoints and usage</p>
                </a>
                
                <a href="{{ route('docs.developer.database') }}" class="block p-3 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                    <h3 class="font-semibold text-green-900">🗄️ Database</h3>
                    <p class="text-sm text-green-700">Database schema and design</p>
                </a>
                
                <a href="{{ route('docs.developer.auth') }}" class="block p-3 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                    <h3 class="font-semibold text-purple-900">🔐 Security</h3>
                    <p class="text-sm text-purple-700">Authentication and authorization</p>
                </a>
                
                <a href="{{ route('docs.developer.modules') }}" class="block p-3 bg-orange-50 rounded-lg hover:bg-orange-100 transition-colors">
                    <h3 class="font-semibold text-orange-900">🧩 Modules</h3>
                    <p class="text-sm text-orange-700">Module development guide</p>
                </a>
                
                <a href="{{ route('docs.developer.testing') }}" class="block p-3 bg-pink-50 rounded-lg hover:bg-pink-100 transition-colors">
                    <h3 class="font-semibold text-pink-900">🧪 Testing</h3>
                    <p class="text-sm text-pink-700">Testing strategies and examples</p>
                </a>
                
                <a href="{{ route('docs.developer.deployment') }}" class="block p-3 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition-colors">
                    <h3 class="font-semibold text-indigo-900">🚀 Deployment</h3>
                    <p class="text-sm text-indigo-700">Production deployment guide</p>
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Navigation -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-semibold text-gray-900 mb-4">🚀 Quick Navigation</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <h3 class="font-semibold text-gray-900 mb-3">For New Users</h3>
                <ul class="space-y-2">
                    <li><a href="{{ route('docs.user.getting-started') }}" class="text-blue-600 hover:text-blue-800">Getting Started Guide</a></li>
                    <li><a href="{{ route('docs.user.faq') }}" class="text-blue-600 hover:text-blue-800">FAQ</a></li>
                </ul>
            </div>
            
            <div>
                <h3 class="font-semibold text-gray-900 mb-3">For Developers</h3>
                <ul class="space-y-2">
                    <li><a href="{{ route('docs.developer.architecture') }}" class="text-green-600 hover:text-green-800">Architecture Overview</a></li>
                    <li><a href="{{ route('docs.developer.api') }}" class="text-green-600 hover:text-green-800">API Documentation</a></li>
                </ul>
            </div>
            
            <div>
                <h3 class="font-semibold text-gray-900 mb-3">Popular Topics</h3>
                <ul class="space-y-2">
                    <li><a href="{{ route('docs.user.accounting') }}" class="text-purple-600 hover:text-purple-800">Accounting Module</a></li>
                    <li><a href="{{ route('docs.user.inventory') }}" class="text-purple-600 hover:text-purple-800">Inventory Management</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
