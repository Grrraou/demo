<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\File;
use League\CommonMark\CommonMarkConverter;

class DocumentationController extends Controller
{
    private CommonMarkConverter $converter;

    public function __construct()
    {
        $this->converter = new CommonMarkConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }

    /**
     * Display the main documentation index
     */
    public function index(): View
    {
        return view('documentation.index');
    }

    /**
     * Display user getting started guide
     */
    public function userGettingStarted(): View
    {
        return view('documentation.user.getting-started');
    }

    /**
     * Display user accounting documentation
     */
    public function userAccounting(): View
    {
        return $this->showUserDocumentation('accounting', 'Accounting Module');
    }

    /**
     * Display user inventory documentation
     */
    public function userInventory(): View
    {
        return $this->showUserDocumentation('inventory', 'Inventory Management');
    }

    /**
     * Display user sales documentation
     */
    public function userSales(): View
    {
        return $this->showUserDocumentation('sales', 'Sales Management');
    }

    /**
     * Display user customers documentation
     */
    public function userCustomers(): View
    {
        return $this->showUserDocumentation('customers', 'Customer Management');
    }

    /**
     * Display user chat documentation
     */
    public function userChat(): View
    {
        return $this->showUserDocumentation('chat', 'Chat & Communication');
    }

    /**
     * Display user calendar documentation
     */
    public function userCalendar(): View
    {
        return $this->showUserDocumentation('calendar', 'Calendar & Events');
    }

    /**
     * Display user leads documentation
     */
    public function userLeads(): View
    {
        return $this->showUserDocumentation('leads', 'Lead Management');
    }

    /**
     * Display user FAQ
     */
    public function userFaq(): View
    {
        return $this->showUserDocumentation('faq', 'Frequently Asked Questions');
    }

    /**
     * Display developer architecture documentation
     */
    public function developerArchitecture(): View
    {
        return $this->showDeveloperDocumentation('architecture', 'Architecture Overview');
    }

    /**
     * Display developer API documentation
     */
    public function developerApi(): View
    {
        return $this->showDeveloperDocumentation('api', 'API Documentation');
    }

    /**
     * Display developer database documentation
     */
    public function developerDatabase(): View
    {
        return $this->showDeveloperDocumentation('database', 'Database Schema');
    }

    /**
     * Display developer authentication documentation
     */
    public function developerAuth(): View
    {
        return $this->showDeveloperDocumentation('auth', 'Authentication & Security');
    }

    /**
     * Display developer modules documentation
     */
    public function developerModules(): View
    {
        return $this->showDeveloperDocumentation('modules', 'Module Development');
    }

    /**
     * Display developer testing documentation
     */
    public function developerTesting(): View
    {
        return $this->showDeveloperDocumentation('testing', 'Testing Guide');
    }

    /**
     * Display developer deployment documentation
     */
    public function developerDeployment(): View
    {
        return $this->showDeveloperDocumentation('deployment', 'Deployment Guide');
    }

    /**
     * Search documentation API endpoint
     */
    public function search(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        $results = $this->performDeepSearch($query);
        
        return response()->json(['results' => $results]);
    }

    /**
     * Helper method to show user documentation
     */
    private function showUserDocumentation(string $file, string $title): View
    {
        $filePath = base_path("docs/user/{$file}.md");
        
        if (!File::exists($filePath)) {
            abort(404, 'Documentation not found');
        }

        $content = File::get($filePath);
        $htmlContent = $this->converter->convert($content);

        return view('documentation.user.show', [
            'title' => $title,
            'content' => $htmlContent
        ]);
    }

    /**
     * Helper method to show developer documentation
     */
    private function showDeveloperDocumentation(string $file, string $title): View
    {
        $filePath = base_path("docs/developer/{$file}.md");
        
        if (!File::exists($filePath)) {
            abort(404, 'Documentation not found');
        }

        $content = File::get($filePath);
        $htmlContent = $this->converter->convert($content);

        return view('documentation.user.show', [
            'title' => $title,
            'content' => $htmlContent
        ]);
    }

    /**
     * Perform deep search across all documentation
     */
    private function performDeepSearch(string $query): array
    {
        $results = [];
        $searchTerm = strtolower($query);

        // User documentation files
        $userDocs = [
            'getting-started' => ['title' => 'Getting Started', 'url' => route('docs.user.getting-started')],
            'accounting' => ['title' => 'Accounting', 'url' => route('docs.user.accounting')],
            'inventory' => ['title' => 'Inventory', 'url' => route('docs.user.inventory')],
            'sales' => ['title' => 'Sales', 'url' => route('docs.user.sales')],
            'customers' => ['title' => 'Customers', 'url' => route('docs.user.customers')],
            'chat' => ['title' => 'Chat', 'url' => route('docs.user.chat')],
            'calendar' => ['title' => 'Calendar', 'url' => route('docs.user.calendar')],
            'leads' => ['title' => 'Leads', 'url' => route('docs.user.leads')],
            'faq' => ['title' => 'FAQ', 'url' => route('docs.user.faq')],
        ];

        // Developer documentation files
        $devDocs = [
            'architecture' => ['title' => 'Architecture', 'url' => route('docs.developer.architecture')],
            'api' => ['title' => 'API', 'url' => route('docs.developer.api')],
            'database' => ['title' => 'Database', 'url' => route('docs.developer.database')],
            'auth' => ['title' => 'Security', 'url' => route('docs.developer.auth')],
            'modules' => ['title' => 'Modules', 'url' => route('docs.developer.modules')],
            'testing' => ['title' => 'Testing', 'url' => route('docs.developer.testing')],
            'deployment' => ['title' => 'Deployment', 'url' => route('docs.developer.deployment')],
        ];

        // Search user documentation
        foreach ($userDocs as $file => $info) {
            $filePath = base_path("docs/user/{$file}.md");
            if (File::exists($filePath)) {
                $content = File::get($filePath);
                $matches = $this->findMatchesInContent($content, $searchTerm, $info['title'], $info['url']);
                $results = array_merge($results, $matches);
            }
        }

        // Search developer documentation
        foreach ($devDocs as $file => $info) {
            $filePath = base_path("docs/developer/{$file}.md");
            if (File::exists($filePath)) {
                $content = File::get($filePath);
                $matches = $this->findMatchesInContent($content, $searchTerm, $info['title'], $info['url']);
                $results = array_merge($results, $matches);
            }
        }

        // Sort results by relevance
        usort($results, function ($a, $b) {
            return $b['relevance'] <=> $a['relevance'];
        });

        return array_slice($results, 0, 20); // Limit to 20 results
    }

    /**
     * Find matches in content with context and anchor links
     */
    private function findMatchesInContent(string $content, string $searchTerm, string $title, string $url): array
    {
        $matches = [];
        $lines = explode("\n", $content);
        
        foreach ($lines as $lineNum => $line) {
            if (stripos($line, $searchTerm) !== false) {
                // Extract context (2 lines before and after)
                $contextStart = max(0, $lineNum - 2);
                $contextEnd = min(count($lines) - 1, $lineNum + 2);
                $context = array_slice($lines, $contextStart, $contextEnd - $contextStart + 1);
                $contextText = trim(implode(' ', $context));
                
                // Limit context length
                if (strlen($contextText) > 200) {
                    $contextText = substr($contextText, 0, 200) . '...';
                }

                // Generate anchor ID from line (for headers)
                $anchorId = $this->generateAnchorId($line);
                
                // Calculate relevance score
                $relevance = $this->calculateRelevance($line, $searchTerm);

                $matches[] = [
                    'title' => $title,
                    'url' => $url,
                    'anchor' => $anchorId,
                    'context' => $contextText,
                    'line' => $lineNum + 1,
                    'relevance' => $relevance,
                    'type' => $this->getContentType($line)
                ];
            }
        }

        return $matches;
    }

    /**
     * Generate anchor ID from a line (for headers)
     */
    private function generateAnchorId(string $line): string
    {
        // Check if it's a header
        if (preg_match('/^(#{1,6})\s+(.+)$/', $line, $matches)) {
            $headerText = $matches[2];
            // Convert to lowercase, replace spaces with hyphens, remove special chars
            $anchor = strtolower($headerText);
            $anchor = preg_replace('/[^a-z0-9\s-]/', '', $anchor);
            $anchor = preg_replace('/\s+/', '-', $anchor);
            return $anchor;
        }
        
        return '';
    }

    /**
     * Calculate relevance score for a match
     */
    private function calculateRelevance(string $line, string $searchTerm): int
    {
        $score = 0;
        $lineLower = strtolower($line);
        $searchLower = strtolower($searchTerm);
        
        // Exact match gets highest score
        if ($lineLower === $searchLower) {
            $score += 100;
        }
        
        // Header matches get bonus
        if (preg_match('/^#+\s.*' . preg_quote($searchTerm, '/') . '/i', $line)) {
            $score += 50;
        }
        
        // Word boundary matches get bonus
        if (preg_match('/\b' . preg_quote($searchTerm, '/') . '\b/i', $line)) {
            $score += 25;
        }
        
        // Multiple occurrences
        $occurrences = substr_count($lineLower, $searchLower);
        $score += $occurrences * 10;
        
        // Shorter lines get bonus (more focused)
        $score += max(0, 20 - strlen($line) / 5);
        
        return $score;
    }

    /**
     * Determine content type (header, list, paragraph, etc.)
     */
    private function getContentType(string $line): string
    {
        $trimmed = trim($line);
        
        if (preg_match('/^#{1,6}\s/', $trimmed)) {
            return 'header';
        } elseif (preg_match('/^[\*\-\+]\s/', $trimmed) || preg_match('/^\d+\.\s/', $trimmed)) {
            return 'list';
        } elseif (preg_match('/^```/', $trimmed)) {
            return 'code';
        } elseif (preg_match('/^\|/', $trimmed)) {
            return 'table';
        } else {
            return 'text';
        }
    }
}
