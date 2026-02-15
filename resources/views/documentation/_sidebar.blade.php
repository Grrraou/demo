<div class="w-64 bg-gray-50 rounded-lg p-4">
    <h3 class="font-semibold text-gray-900 mb-4">📚 Documentation</h3>
    
    <!-- Advanced Search Box -->
    <div class="mb-6">
        <div class="relative">
            <input 
                type="text" 
                id="docs-search" 
                placeholder="Search in articles..." 
                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
            <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
        </div>
        <div id="search-loading" class="mt-2 hidden">
            <div class="flex items-center text-xs text-gray-500">
                <svg class="animate-spin h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Searching...
            </div>
        </div>
        <div id="search-results" class="mt-2 hidden">
            <div class="text-xs text-gray-500 mb-1">Search Results</div>
            <div id="search-results-list" class="space-y-2 text-sm max-h-64 overflow-y-auto"></div>
        </div>
    </div>
    
    <div class="space-y-6">
        <!-- User Documentation -->
        <div>
            <h4 class="font-medium text-gray-700 mb-2">👥 User Guides</h4>
            <ul class="space-y-1 text-sm">
                <li>
                    <a href="{{ route('docs.user.getting-started') }}" class="block px-2 py-1 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded">
                        🚀 Getting Started
                    </a>
                </li>
                <li>
                    <a href="{{ route('docs.user.accounting') }}" class="block px-2 py-1 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded">
                        🧮 Accounting
                    </a>
                </li>
                <li>
                    <a href="{{ route('docs.user.inventory') }}" class="block px-2 py-1 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded">
                        📦 Inventory
                    </a>
                </li>
                <li>
                    <a href="{{ route('docs.user.sales') }}" class="block px-2 py-1 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded">
                        💰 Sales
                    </a>
                </li>
                <li>
                    <a href="{{ route('docs.user.customers') }}" class="block px-2 py-1 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded">
                        👥 Customers
                    </a>
                </li>
                <li>
                    <a href="{{ route('docs.user.chat') }}" class="block px-2 py-1 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded">
                        💬 Chat
                    </a>
                </li>
                <li>
                    <a href="{{ route('docs.user.calendar') }}" class="block px-2 py-1 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded">
                        📅 Calendar
                    </a>
                </li>
                <li>
                    <a href="{{ route('docs.user.leads') }}" class="block px-2 py-1 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded">
                        🎯 Leads
                    </a>
                </li>
                <li>
                    <a href="{{ route('docs.user.faq') }}" class="block px-2 py-1 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded">
                        ❓ FAQ
                    </a>
                </li>
            </ul>
        </div>

        <!-- Developer Documentation -->
        <div>
            <h4 class="font-medium text-gray-700 mb-2">💻 Developer Docs</h4>
            <ul class="space-y-1 text-sm">
                <li>
                    <a href="{{ route('docs.developer.architecture') }}" class="block px-2 py-1 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded">
                        🏗️ Architecture
                    </a>
                </li>
                <li>
                    <a href="{{ route('docs.developer.api') }}" class="block px-2 py-1 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded">
                        🔌 API
                    </a>
                </li>
                <li>
                    <a href="{{ route('docs.developer.database') }}" class="block px-2 py-1 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded">
                        🗄️ Database
                    </a>
                </li>
                <li>
                    <a href="{{ route('docs.developer.auth') }}" class="block px-2 py-1 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded">
                        🔐 Security
                    </a>
                </li>
                <li>
                    <a href="{{ route('docs.developer.modules') }}" class="block px-2 py-1 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded">
                        🧩 Modules
                    </a>
                </li>
                <li>
                    <a href="{{ route('docs.developer.testing') }}" class="block px-2 py-1 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded">
                        🧪 Testing
                    </a>
                </li>
                <li>
                    <a href="{{ route('docs.developer.deployment') }}" class="block px-2 py-1 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded">
                        🚀 Deployment
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="mt-6 pt-6 border-t border-gray-200">
        <a href="{{ route('docs.index') }}" class="block px-2 py-1 text-sm text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded">
            📋 All Documentation
        </a>
    </div>
</div>

<script>
let searchTimeout;
let currentSearchRequest = null;

function performSearch(query) {
    const searchResults = document.getElementById('search-results');
    const searchLoading = document.getElementById('search-loading');
    const searchResultsList = document.getElementById('search-results-list');
    
    if (query.length < 2) {
        searchResults.classList.add('hidden');
        searchLoading.classList.add('hidden');
        return;
    }
    
    // Show loading
    searchLoading.classList.remove('hidden');
    searchResults.classList.add('hidden');
    
    // Cancel previous request if exists
    if (currentSearchRequest) {
        currentSearchRequest.abort();
    }
    
    // Create new request
    currentSearchRequest = new XMLHttpRequest();
    currentSearchRequest.open('GET', `/docs/search?q=${encodeURIComponent(query)}`);
    currentSearchRequest.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    currentSearchRequest.setRequestHeader('Accept', 'application/json');
    
    currentSearchRequest.onload = function() {
        if (currentSearchRequest.status === 200) {
            const data = JSON.parse(currentSearchRequest.responseText);
            displaySearchResults(data.results);
        } else {
            searchResultsList.innerHTML = '<div class="px-2 py-1 text-red-500 text-xs">Search failed</div>';
            searchResults.classList.remove('hidden');
        }
        searchLoading.classList.add('hidden');
        currentSearchRequest = null;
    };
    
    currentSearchRequest.onerror = function() {
        searchResultsList.innerHTML = '<div class="px-2 py-1 text-red-500 text-xs">Network error</div>';
        searchResults.classList.remove('hidden');
        searchLoading.classList.add('hidden');
        currentSearchRequest = null;
    };
    
    currentSearchRequest.send();
}

function displaySearchResults(results) {
    const searchResults = document.getElementById('search-results');
    const searchResultsList = document.getElementById('search-results-list');
    
    if (results.length === 0) {
        searchResultsList.innerHTML = '<div class="px-2 py-1 text-gray-500 text-xs">No results found</div>';
    } else {
        searchResultsList.innerHTML = results.map(result => {
            const icon = getContentTypeIcon(result.type);
            const relevance = getRelevanceLabel(result.relevance);
            
            // Fix URL generation - url already contains the full path
            let url = result.url;
            if (result.anchor) {
                url += '#' + result.anchor;
            }
            
            return `
                <a href="${url}" class="block p-2 bg-white border border-gray-200 rounded hover:bg-blue-50 hover:border-blue-200 transition-colors">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center mb-1">
                                <span class="text-xs">${icon}</span>
                                <span class="ml-1 font-medium text-gray-900 text-xs">${result.title}</span>
                                ${result.type === 'header' ? '<span class="ml-1 text-xs bg-blue-100 text-blue-800 px-1 rounded">Header</span>' : ''}
                            </div>
                            <div class="text-xs text-gray-600 line-clamp-2">${highlightSearchTerm(result.context)}</div>
                            <div class="flex items-center mt-1 text-xs text-gray-400">
                                <span>Line ${result.line}</span>
                                <span class="mx-1">•</span>
                                <span>${relevance}</span>
                            </div>
                        </div>
                    </div>
                </a>
            `;
        }).join('');
    }
    
    searchResults.classList.remove('hidden');
}

function getContentTypeIcon(type) {
    const icons = {
        'header': '📰',
        'list': '📋',
        'code': '💻',
        'table': '📊',
        'text': '📄'
    };
    return icons[type] || '📄';
}

function getRelevanceLabel(score) {
    if (score >= 80) return '🔥 Very relevant';
    if (score >= 50) return '⭐ Relevant';
    if (score >= 30) return '📍 Related';
    return '📝 Mentioned';
}

function highlightSearchTerm(text) {
    const searchTerm = document.getElementById('docs-search').value;
    if (!searchTerm) return text;
    
    const regex = new RegExp(`(${escapeRegExp(searchTerm)})`, 'gi');
    return text.replace(regex, '<mark class="bg-yellow-200">$1</mark>');
}

function escapeRegExp(string) {
    return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

// Initialize search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('docs-search');
    
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                performSearch(e.target.value);
            }, 300); // 300ms debounce
        });
        
        // Clear search when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !document.getElementById('search-results').contains(e.target)) {
                document.getElementById('search-results').classList.add('hidden');
                document.getElementById('search-loading').classList.add('hidden');
                searchInput.value = '';
            }
        });
        
        // Handle keyboard navigation
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.getElementById('search-results').classList.add('hidden');
                searchInput.value = '';
            }
        });
    }
});
</script>

<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

mark {
    padding: 0 1px;
    border-radius: 2px;
}
</style>
