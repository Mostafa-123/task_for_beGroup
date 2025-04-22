<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Models\User;
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
        $tasks = Task::orderBy('id', 'DESC')->with(['user_assign.assign_tasks', 'user_create.created_tasks'])->paginate($limit);

        if ($tasks->isEmpty()) {
            return apiResponse(
                201,
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
        $task = Task::where('deleted_at', null)->with(['user_assign.assign_tasks', 'user_create.created_tasks'])->find($id);
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
            $task->load(['user_assign.assign_tasks', 'user_create.created_tasks']);
            return apiResponse(201, new TaskResource($task), "Task created successfully");
        } catch (\Exception $e) {
            return apiResponse(500, [], $e->getMessage());
        }
    }

    public function update(UpdateTaskRequest $request, $id)
    {
        $task = Task::whereNull('deleted_at')->with(['user_assign.assign_tasks', 'user_create.created_tasks'])->find($id);

        if (!$task) {
            return apiResponse(404, [], 'Sorry! But No Task Found With That Id');
        }

        if ($task->created_by !== auth('sanctum')->user()->id) {
            return apiResponse(401, [], "Unauthorized Action");
        }

        try {
            $updatedTask = $this->taskService->updateTask($task, $request);
            return apiResponse(201, new TaskResource($updatedTask), "Task updated successfully");
        } catch (\Exception $e) {
            return apiResponse(500, [], $e->getMessage());
        }
    }




    public function delete($id)
    {
        $task = Task::with(['user_assign.assign_tasks', 'user_create.created_tasks'])->whereNull('deleted_at')->find($id);

        if (!$task) {
            return apiResponse(404, [], "Sorry! But No Task Found With That Id");
        }

        if ($task->created_by !== auth('sanctum')->user()->id) {
            return apiResponse(401, [], "Unauthorized Action");
        }

        $this->taskService->deleteTask($task);
        return apiResponse(201, [], "Task deleted successfully");
    }


    public function user_created_tasks(Request $request)
    {
        $perPage = $request->header('perPage', 10);
        $limit = max(1, min($perPage, 50));
        $user = auth('sanctum')->user();

        if (!$user) {
            return apiResponse(401, [], 'Unauthorized');
        }

        $tasks = $user->created_tasks()->whereNull('deleted_at')->with(['user_assign.assign_tasks', 'user_create.created_tasks'])->orderBy('id','DESC')->paginate($limit);
        if ($tasks->isEmpty()) {
            return apiResponse(201, [], "there are no created tasks for now");
        }

        return apiResponse(200, TaskResource::collection($tasks)->response()->getData(true), "Created tasks");
    }

    public function user_assigned_tasks(Request $request)
    {
        $user = auth('sanctum')->user();
        $perPage = $request->header('perPage', 10);
        $limit = max(1, min($perPage, 50));
        if (!$user) {
            return apiResponse(401, [], 'Unauthorized');
        }

        $tasks = $user->assign_tasks()->whereNull('deleted_at')->with(['user_assign.assign_tasks', 'user_create.created_tasks'])->orderBy('id','DESC')->paginate($limit);

        if ($tasks->isEmpty()) {
            return apiResponse(201, [], "there are no assined tasks for now");
        }
        return apiResponse(200, TaskResource::collection($tasks)->response()->getData(true), "Assigned tasks");
    }


    public function user_deleted_tasks(Request $request)
    {

        $user = auth('sanctum')->user();
        $perPage = $request->header('perPage', 10);
        $limit = max(1, min($perPage, 50));
        if (!$user) {
            return apiResponse(401, [], 'Unauthorized');
        }

        $tasks = $user->created_tasks()->onlyTrashed()->with(['user_assign.assign_tasks', 'user_create.created_tasks'])->orderBy('id','DESC')->paginate($limit);
        if ($tasks->isEmpty()) {
            return apiResponse(201, [], "there are no deleted tasks for now");
        }

        return apiResponse(200, TaskResource::collection($tasks)->response()->getData(true), "deleted tasks");

    }

    public function restore_user_task($id){
        $task=Task::onlyTrashed()->find($id);

        if (!$task) {
            return apiResponse(404, [], "Sorry! But No Task Found With That Id");
        }
        if ($task->created_by !== auth('sanctum')->user()->id) {
            return apiResponse(401, [], "Unauthorized Action");
        }

        $task->restore();
        $task->load(['user_assign.assign_tasks', 'user_create.created_tasks']);
        return apiResponse(
            200,
            new TaskResource($task),
            'Task Restored Successfully'
        );
    }

    public function assig_task_to_user($task_id,$user_id){
        $task=Task::find($task_id);
        $user=User::find($user_id);

        if (!$task) {
            return apiResponse(404, [], "Sorry! But No Task Found With That Id");
        }

        if (!$user) {
            return apiResponse(404, [], "Sorry! But No User Found With That Id");
        }

        if ($task->created_by !== auth('sanctum')->user()->id) {
            return apiResponse(401, [], "Unauthorized Action");
        }

        return apiResponse(201, new TaskResource($this->taskService->assign_task_to_user($task,$user)), "Task assigned successfully");




    }

}
