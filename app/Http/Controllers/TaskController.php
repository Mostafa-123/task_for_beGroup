<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Services\TaskService;
use App\Traits\ManageFileTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use function App\apiResponse;

class TaskController extends Controller
{

    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    public function getAll(Request $request)
    {
        $perPage = $request->header('perPage', 10);
        $limit = max(1, min($perPage, 50));
        $tasks = Task::orderBy('created_at', 'DESC')->paginate($limit);

        if ($tasks->isEmpty()) {
            return apiResponse(
                200,
                [],
                'Sorry! But No Tasks For Now'
            );
        }

        return apiResponse(
            200,
            TaskResource::collection($tasks)->response()->getData(true),
            'All Tasks Returned Successfully'
        );
    }
    public function get($id)
    {
        $task = Task::where('deleted_at', null)->find($id);
        ;
        if (!$task) {
            return apiResponse(
                404,
                [],
                'Sorry! But No Task Found With That Id'
            );
        }
        return apiResponse(
            200,
            new TaskResource($task),
            'Task Returned Successfully'
        );
    }


    public function create(CreateTaskRequest $request)
    {
        try {
            $task = $this->taskService->createTask($request, auth('sanctum')->user());
            return apiResponse(200, new TaskResource($task), "Task created successfully");
        } catch (\Exception $e) {
            return apiResponse(500, [], $e->getMessage());
        }
    }

    public function update(UpdateTaskRequest $request, $id)
    {
        $task = Task::whereNull('deleted_at')->find($id);

        if (!$task) {
            return apiResponse(404, [], 'Sorry! But No Task Found With That Id');
        }

        if ($task->created_by !== auth('sanctum')->user()->id) {
            return apiResponse(401, [], "Unauthorized Action");
        }

        try {
            $updatedTask = $this->taskService->updateTask($task, $request);
            return apiResponse(200, new TaskResource($updatedTask), "Task updated successfully");
        } catch (\Exception $e) {
            return apiResponse(500, [], $e->getMessage());
        }
    }




    public function delete($id)
    {
        $task = Task::whereNull('deleted_at')->find($id);

        if (!$task) {
            return apiResponse(404, [], "Sorry! But No Task Found With That Id");
        }

        if ($task->created_by !== auth('sanctum')->user()->id) {
            return apiResponse(401, [], "Unauthorized Action");
        }

        $this->taskService->deleteTask($task);
        return apiResponse(200, [], "Task deleted successfully");
    }


    public function user_created_tasks(Request $request)
    {
        $perPage = $request->header('perPage', 10);
        $limit = max(1, min($perPage, 50));
        $user = auth('sanctum')->user();

        if (!$user) {
            return apiResponse(401, [], 'Unauthorized');
        }

        $tasks = $user->created_tasks()->whereNull('deleted_at')->paginate($limit);
        if ($tasks->isEmpty()) {
            return apiResponse(200, [], "there are no created tasks for now");
        }

        return apiResponse(200, TaskResource::collection($tasks), "Created tasks");
    }

    public function user_assigned_tasks(Request $request)
    {
        $user = auth('sanctum')->user();
        $perPage = $request->header('perPage', 10);
        $limit = max(1, min($perPage, 50));
        if (!$user) {
            return apiResponse(401, [], 'Unauthorized');
        }

        $tasks = $user->assign_tasks()->whereNull('deleted_at')->paginate($limit);

        if ($tasks->isEmpty()) {
            return apiResponse(200, [], "there are no assined tasks for now");
        }
        return apiResponse(200, TaskResource::collection($tasks), "Assigned tasks");
    }


}
