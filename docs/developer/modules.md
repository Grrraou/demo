# Module Development Guide

## Overview
This guide explains how to develop new modules for the ERP system following the established architecture patterns and conventions.

## Module Structure

### Standard Module Directory Structure
```
app/
├── Models/ModuleName/
│   ├── ModelName.php
│   └── RelatedModel.php
├── Repositories/ModuleName/
│   ├── ModelNameRepository.php
│   └── RelatedModelRepository.php
├── Managers/ModuleName/
│   ├── ModelNameManager.php
│   └── ModuleManager.php
├── Http/Controllers/Api/ModuleName/
│   └── ModelNameController.php
├── Http/Controllers/Web/ModuleName/
│   └── ModelNameController.php
├── Livewire/ModuleName/
│   ├── ModelNameList.php
│   ├── ModelNameForm.php
│   └── ModelNameShow.php
└── Enums/ModuleName/
    └── ModelNameStatus.php
```

### Database Structure
```
database/
├── migrations/
│   └── 2024_01_01_create_model_names_table.php
├── seeders/
│   └── ModelNameSeeder.php
└── factories/
    └── ModelNameFactory.php
```

### Frontend Structure
```
resources/
├── views/module-name/
│   ├── index.blade.php
│   ├── create.blade.php
│   ├── edit.blade.php
│   └── show.blade.php
└── views/livewire/module-name/
    ├── list.blade.php
    ├── form.blade.php
    └── show.blade.php
```

## Creating a New Module

### Step 1: Database Migration

#### Create Migration
```bash
php artisan make:migration create_projects_table
```

#### Migration Structure
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('owned_company_id')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('budget', 12, 2)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign keys
            $table->foreign('owned_company_id')
                  ->references('id')
                  ->on('owned_companies')
                  ->onDelete('cascade');
            
            // Indexes
            $table->index(['owned_company_id', 'status']);
            $table->index('start_date');
            $table->index('end_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
```

### Step 2: Model Creation

#### Model Class
```php
<?php

namespace App\Models\Projects;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'owned_company_id',
        'name',
        'description',
        'status',
        'start_date',
        'end_date',
        'budget',
        'metadata',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'budget' => 'decimal:2',
        'metadata' => 'array',
    ];

    // Relationships
    public function company()
    {
        return $this->belongsTo(OwnedCompany::class, 'owned_company_id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function teamMembers()
    {
        return $this->belongsToMany(TeamMember::class, 'project_team_members')
                    ->withPivot(['role', 'joined_at']);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Attributes
    public function getDurationAttribute()
    {
        if ($this->start_date && $this->end_date) {
            return $this->start_date->diffInDays($this->end_date);
        }
        return null;
    }

    // Global scope for company isolation
    protected static function booted()
    {
        static::addGlobalScope('company', function ($query) {
            if (auth()->check()) {
                $query->where('owned_company_id', auth()->user()->current_company_id);
            }
        });
    }
}
```

### Step 3: Repository Implementation

#### Repository Interface
```php
<?php

namespace App\Repositories\Projects;

use App\Models\Projects\Project;
use Illuminate\Pagination\LengthAwarePaginator;

interface ProjectRepositoryInterface
{
    public function create(array $data): Project;
    public function findById(string $id): ?Project;
    public function findByUuid(string $uuid): ?Project;
    public function update(string $id, array $data): Project;
    public function delete(string $id): bool;
    public function paginate(int $perPage = 15): LengthAwarePaginator;
    public function search(string $term): array;
    public function getByStatus(string $status): array;
}
```

#### Repository Implementation
```php
<?php

namespace App\Repositories\Projects;

use App\Models\Projects\Project;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ProjectRepository implements ProjectRepositoryInterface
{
    public function create(array $data): Project
    {
        return Project::create($data);
    }

    public function findById(string $id): ?Project
    {
        return Project::find($id);
    }

    public function findByUuid(string $uuid): ?Project
    {
        return Project::where('id', $uuid)->first();
    }

    public function update(string $id, array $data): Project
    {
        $project = $this->findById($id);
        if ($project) {
            $project->update($data);
            return $project->fresh();
        }
        throw new \Exception("Project not found");
    }

    public function delete(string $id): bool
    {
        $project = $this->findById($id);
        if ($project) {
            return $project->delete();
        }
        return false;
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Project::with(['company', 'teamMembers'])
                     ->orderBy('created_at', 'desc')
                     ->paginate($perPage);
    }

    public function search(string $term): array
    {
        return Project::where('name', 'LIKE', "%{$term}%")
                      ->orWhere('description', 'LIKE', "%{$term}%")
                      ->get()
                      ->toArray();
    }

    public function getByStatus(string $status): array
    {
        return Project::byStatus($status)->get()->toArray();
    }
}
```

### Step 4: Manager Implementation

#### Manager Class
```php
<?php

namespace App\Managers\Projects;

use App\Repositories\Projects\ProjectRepositoryInterface;
use App\Managers\Teams\TeamMemberManager;
use Illuminate\Support\Facades\DB;
use Exception;

class ProjectManager
{
    public function __construct(
        private ProjectRepositoryInterface $repository,
        private TeamMemberManager $teamMemberManager
    ) {}

    public function createProject(array $data): Project
    {
        try {
            DB::beginTransaction();

            // Set company context
            $data['owned_company_id'] = auth()->user()->current_company_id;

            $project = $this->repository->create($data);

            // Add creator as project manager
            if (isset($data['team_members'])) {
                $this->assignTeamMembers($project, $data['team_members']);
            }

            DB::commit();
            return $project;

        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception("Failed to create project: " . $e->getMessage());
        }
    }

    public function updateProject(string $id, array $data): Project
    {
        try {
            DB::beginTransaction();

            $project = $this->repository->update($id, $data);

            // Update team members if provided
            if (isset($data['team_members'])) {
                $this->updateTeamMembers($project, $data['team_members']);
            }

            DB::commit();
            return $project;

        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception("Failed to update project: " . $e->getMessage());
        }
    }

    public function deleteProject(string $id): bool
    {
        try {
            DB::beginTransaction();

            $project = $this->repository->findById($id);
            
            if (!$project) {
                throw new Exception("Project not found");
            }

            // Check if project has active tasks
            if ($project->tasks()->where('status', 'active')->exists()) {
                throw new Exception("Cannot delete project with active tasks");
            }

            $result = $this->repository->delete($id);

            DB::commit();
            return $result;

        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception("Failed to delete project: " . $e->getMessage());
        }
    }

    public function assignTeamMembers(Project $project, array $members): void
    {
        $syncData = [];
        
        foreach ($members as $member) {
            $syncData[$member['user_id']] = [
                'role' => $member['role'] ?? 'member',
                'joined_at' => now(),
            ];
        }

        $project->teamMembers()->sync($syncData);
    }

    public function getProjectMetrics(string $id): array
    {
        $project = $this->repository->findById($id);
        
        if (!$project) {
            throw new Exception("Project not found");
        }

        return [
            'total_tasks' => $project->tasks()->count(),
            'completed_tasks' => $project->tasks()->where('status', 'completed')->count(),
            'active_tasks' => $project->tasks()->where('status', 'active')->count(),
            'team_size' => $project->teamMembers()->count(),
            'budget_used' => $project->tasks()->sum('cost'),
            'progress_percentage' => $this->calculateProgress($project),
        ];
    }

    private function calculateProgress(Project $project): float
    {
        $totalTasks = $project->tasks()->count();
        if ($totalTasks === 0) return 0;

        $completedTasks = $project->tasks()->where('status', 'completed')->count();
        return ($completedTasks / $totalTasks) * 100;
    }
}
```

### Step 5: Controllers

#### API Controller
```php
<?php

namespace App\Http\Controllers\Api\Projects;

use App\Http\Controllers\Controller;
use App\Http\Requests\Projects\StoreProjectRequest;
use App\Http\Requests\Projects\UpdateProjectRequest;
use App\Managers\Projects\ProjectManager;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProjectController extends Controller
{
    public function __construct(
        private ProjectManager $projectManager
    ) {}

    public function index(Request $request): JsonResponse
    {
        $projects = $this->projectManager->getProjects(
            $request->get('per_page', 15),
            $request->get('search'),
            $request->get('status')
        );

        return response()->json($projects);
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        $project = $this->projectManager->createProject($request->validated());
        
        return response()->json($project, 201);
    }

    public function show(string $id): JsonResponse
    {
        $project = $this->projectManager->getProjectWithRelations($id);
        
        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }

        return response()->json($project);
    }

    public function update(UpdateProjectRequest $request, string $id): JsonResponse
    {
        $project = $this->projectManager->updateProject($id, $request->validated());
        
        return response()->json($project);
    }

    public function destroy(string $id): JsonResponse
    {
        $result = $this->projectManager->deleteProject($id);
        
        if (!$result) {
            return response()->json(['message' => 'Project not found'], 404);
        }

        return response()->json(null, 204);
    }

    public function metrics(string $id): JsonResponse
    {
        try {
            $metrics = $this->projectManager->getProjectMetrics($id);
            return response()->json($metrics);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
```

#### Web Controller
```php
<?php

namespace App\Http\Controllers\Web\Projects;

use App\Http\Controllers\Controller;
use App\Http\Requests\Projects\StoreProjectRequest;
use App\Http\Requests\Projects\UpdateProjectRequest;
use App\Managers\Projects\ProjectManager;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function __construct(
        private ProjectManager $projectManager
    ) {}

    public function index(Request $request)
    {
        $projects = $this->projectManager->getProjects(
            $request->get('per_page', 15),
            $request->get('search'),
            $request->get('status')
        );

        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        return view('projects.create');
    }

    public function store(StoreProjectRequest $request)
    {
        $project = $this->projectManager->createProject($request->validated());
        
        return redirect()
            ->route('projects.show', $project->id)
            ->with('success', 'Project created successfully');
    }

    public function show(string $id)
    {
        $project = $this->projectManager->getProjectWithRelations($id);
        
        if (!$project) {
            abort(404);
        }

        return view('projects.show', compact('project'));
    }

    public function edit(string $id)
    {
        $project = $this->projectManager->getProject($id);
        
        if (!$project) {
            abort(404);
        }

        return view('projects.edit', compact('project'));
    }

    public function update(UpdateProjectRequest $request, string $id)
    {
        $project = $this->projectManager->updateProject($id, $request->validated());
        
        return redirect()
            ->route('projects.show', $project->id)
            ->with('success', 'Project updated successfully');
    }

    public function destroy(string $id)
    {
        $result = $this->projectManager->deleteProject($id);
        
        if (!$result) {
            return redirect()
                ->route('projects.index')
                ->with('error', 'Project not found');
        }

        return redirect()
            ->route('projects.index')
            ->with('success', 'Project deleted successfully');
    }
}
```

### Step 6: Request Validation

#### Store Request
```php
<?php

namespace App\Http\Requests\Projects;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->currentTeamMember()->hasPermission('edit.projects');
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'start_date' => 'nullable|date|before_or_equal:end_date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'budget' => 'nullable|numeric|min:0',
            'team_members' => 'nullable|array',
            'team_members.*.user_id' => 'required|exists:team_members,id',
            'team_members.*.role' => 'required|in:manager,member,viewer',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Project name is required',
            'start_date.before_or_equal' => 'Start date must be before or equal to end date',
            'budget.min' => 'Budget must be a positive number',
        ];
    }
}
```

### Step 7: Livewire Components

#### List Component
```php
<?php

namespace App\Livewire\Projects;

use Livewire\Component;
use Livewire\WithPagination;
use App\Managers\Projects\ProjectManager;

class ProjectList extends Component
{
    use WithPagination;

    public $search = '';
    public $status = '';
    public $perPage = 15;

    protected $queryString = ['search', 'status', 'perPage'];

    public function render()
    {
        $projects = $this->projectManager->getProjects(
            $this->perPage,
            $this->search,
            $this->status
        );

        return view('livewire.projects.list', [
            'projects' => $projects,
        ]);
    }

    public function deleteProject(string $id): void
    {
        try {
            $this->projectManager->deleteProject($id);
            $this->dispatch('project-deleted');
        } catch (\Exception $e) {
            $this->dispatch('error', message: $e->getMessage());
        }
    }
}
```

### Step 8: Routes

#### API Routes
```php
// routes/api.php
Route::middleware(['auth:sanctum', 'permission:edit.projects'])
    ->prefix('projects')
    ->group(function () {
        Route::get('/', [ProjectController::class, 'index']);
        Route::post('/', [ProjectController::class, 'store']);
        Route::get('/{id}', [ProjectController::class, 'show']);
        Route::put('/{id}', [ProjectController::class, 'update']);
        Route::delete('/{id}', [ProjectController::class, 'destroy']);
        Route::get('/{id}/metrics', [ProjectController::class, 'metrics']);
    });
```

#### Web Routes
```php
// routes/web.php
Route::middleware(['auth', 'permission:edit.projects'])
    ->prefix('projects')
    ->group(function () {
        Route::get('/', [ProjectController::class, 'index'])->name('projects.index');
        Route::get('/create', [ProjectController::class, 'create'])->name('projects.create');
        Route::post('/', [ProjectController::class, 'store'])->name('projects.store');
        Route::get('/{id}', [ProjectController::class, 'show'])->name('projects.show');
        Route::get('/{id}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
        Route::put('/{id}', [ProjectController::class, 'update'])->name('projects.update');
        Route::delete('/{id}', [ProjectController::class, 'destroy'])->name('projects.destroy');
    });
```

## Testing Your Module

### Unit Tests
```php
<?php

namespace Tests\Unit\Projects;

use Tests\TestCase;
use App\Managers\Projects\ProjectManager;
use App\Repositories\Projects\ProjectRepository;
use App\Models\Projects\Project;

class ProjectManagerTest extends TestCase
{
    private ProjectManager $manager;
    private ProjectRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->app->make(ProjectRepository::class);
        $this->manager = new ProjectManager($this->repository);
    }

    public function test_can_create_project(): void
    {
        $data = [
            'name' => 'Test Project',
            'description' => 'Test Description',
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(30),
            'budget' => 10000.00,
        ];

        $project = $this->manager->createProject($data);

        $this->assertInstanceOf(Project::class, $project);
        $this->assertEquals('Test Project', $project->name);
        $this->assertEquals(10000.00, $project->budget);
    }

    public function test_cannot_delete_project_with_active_tasks(): void
    {
        $project = Project::factory()->create();
        $project->tasks()->create(['name' => 'Active Task', 'status' => 'active']);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot delete project with active tasks');

        $this->manager->deleteProject($project->id);
    }
}
```

### Feature Tests
```php
<?php

namespace Tests\Feature\Projects;

use Tests\TestCase;
use App\Models\Projects\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProjectApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_projects(): void
    {
        Project::factory()->count(5)->create();

        $response = $this->actingAs($this->user)
                        ->getJson('/api/projects');

        $response->assertStatus(200)
                ->assertJsonCount(5, 'data');
    }

    public function test_can_create_project(): void
    {
        $data = [
            'name' => 'New Project',
            'description' => 'Project description',
            'budget' => 5000.00,
        ];

        $response = $this->actingAs($this->user)
                        ->postJson('/api/projects', $data);

        $response->assertStatus(201)
                ->assertJsonFragment(['name' => 'New Project']);
    }
}
```

## Module Registration

### Service Provider Registration
```php
// app/Providers/AppServiceProvider.php
public function register(): void
{
    $this->app->bind(
        ProjectRepositoryInterface::class,
        ProjectRepository::class
    );
}
```

### Navigation Menu Registration
```php
// config/menu.php
return [
    'projects' => [
        'title' => 'Projects',
        'icon' => 'fas fa-project-diagram',
        'route' => 'projects.index',
        'permission' => 'view.projects',
        'submenu' => [
            'all-projects' => [
                'title' => 'All Projects',
                'route' => 'projects.index',
            ],
            'create-project' => [
                'title' => 'Create Project',
                'route' => 'projects.create',
                'permission' => 'edit.projects',
            ],
        ],
    ],
];
```

## Best Practices

### 1. Code Organization
- Follow PSR-4 autoloading standards
- Use dependency injection
- Implement interfaces for repositories
- Keep controllers thin

### 2. Database Design
- Use UUID primary keys
- Include company_id for multi-tenancy
- Add proper indexes
- Use soft deletes

### 3. Security
- Validate all input
- Check permissions in controllers
- Use mass assignment protection
- Implement rate limiting

### 4. Performance
- Use eager loading
- Implement caching
- Optimize queries
- Use pagination

### 5. Testing
- Write unit tests for managers
- Write feature tests for controllers
- Test validation rules
- Test permissions

This module development guide provides a comprehensive framework for building consistent, maintainable, and secure modules for the ERP system.
