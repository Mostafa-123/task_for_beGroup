<?php
namespace Tests\Unit;

use App\Models\Task;
use App\Models\User;
use App\Services\TaskService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Tests\TestCase;

class TaskServiceTest extends TestCase
{
    use RefreshDatabase;

    protected TaskService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TaskService();
        Storage::fake('public');
    }

    public function test_creates_a_task()
    {
        $user = User::factory()->create();

        $request = Request::create('/api/tasks', 'POST', [
            'name' => 'create Task name',
            'description' => 'Create Task description',
            'status' => 'pending',
            'deadline' => now()->addDays(5)->toDateString(),
            'image' =>null
        ]);

        $task = $this->service->createTask($request, $user);

        $this->assertEquals('create Task name', $task->name);
        $this->assertDatabaseHas('tasks', ['name' => 'create Task name']);
    }

    public function test_updates_a_task()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create([
            'created_by' => $user->id,
            'image' => null
        ]);

        $request = Request::create("/api/tasks/{$task->id}", 'PUT', [
            'name' => 'Update Task name',
            'description' => 'Update task desc',
            'status' => 'in_progress',
        ]);

        $updatedTask = $this->service->updateTask($task, $request);

        $this->assertEquals('Update Task name', $updatedTask->name);
        $this->assertEquals('in_progress', $updatedTask->status);
    }

    public function test_delete_a_task()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create([
            'created_by' => $user->id,
            'assign_to' => $user->id,
            'image' => null
        ]);

        $result = $this->service->deleteTask($task);

        $this->assertTrue($result);
        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }
}
