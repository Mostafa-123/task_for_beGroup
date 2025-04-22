<?php

namespace App\Http\Controllers;

use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Traits\ManageFileTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use function App\apiResponse;

class TaskController extends Controller
{
    use ManageFileTrait;
    public function getAll(Request $request)
    {
        $perPage = $request->header('perPage', 10);
        $limit = max(1, min($perPage, 50));
        $tasks = Task::orderBy('created_at', 'DESC')->paginate($limit);

        if ($tasks->isEmpty()) {
            return apiResponse(
                404,
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
                'Sorry! There Are No Task With That Id'
            );
        }
        return apiResponse(
            200,
            new TaskResource($task),
            'Task Returned Successfully'
        );
    }

    public function create(Request $request)
    {
        try {
            $this->validateTask($request);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return apiResponse(422, $e->errors(), 'Validation Failed');
        }
        try {
            DB::beginTransaction();
            $task = Task::create(array_merge(
                $request->only(['name', 'description', 'status', 'deadline', 'assign_to']),
                [
                    'created_by' => auth('sanctum')->user()->id,
                    'image' => $this->uploadFile($request, 'image', 'TasksPhotos'),
                ]
            ));
            DB::commit();
            return apiResponse(200, new TaskResource($task), "task created successfully");
        } catch (Exception $e) {
            DB::rollBack();
            return apiResponse(500, '', $e);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $rules = [
                'name' => 'max:50',
                'description' => 'max:300',
                'status' => 'in:pending,in_progress,completed',
                'deadline' => 'nullable|date',
                'assign_to' => 'nullable|exists:users,id'
            ];

            if ($request->hasFile('image')) {
                $rules['image'] = 'mimes:jpg,jpeg,png,gif,webp|max:2048';
            }

            $messages = [
                'status.in' => 'Status must be one of: pending, in_progress, or completed.',
                'assign_to.exists' => 'Assigned user does not exist.'
            ];

            $request->validate($rules, $messages);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return apiResponse(422, $e->errors(), 'Validation Failed');
        }
        try {
            DB::beginTransaction();

            $task = Task::where('deleted_at', null)->find($id);
            ;
            if (!$task) {
                return apiResponse(
                    404,
                    [],
                    'Sorry! There Are No Task With That Id'
                );
            }

            if ($task->created_by !== auth('sanctum')->user()->id) {
                return apiResponse(401, '', "Unauthorized action");
            } else {
                $image = $request->file('image');
                if ($image && $task->image) {
                    $this->deleteFile($task->image);
                    $image = $this->uploadFile($request, 'image', 'TasksPhotos');
                } elseif ($image != null && $task->image == null) {
                    $image = $this->uploadFile($request, 'image', 'TasksPhotos');
                } else {
                    $image = $task->image;
                }
                $task->update(array_merge(
                    $request->only(['name', 'description', 'status', 'deadline', 'assign_to']),
                    [
                        'image' => $image,
                    ]
                ));
            }
            DB::commit();
            return apiResponse(200, new TaskResource($task), "task updated successfully");
        } catch (Exception $e) {
            DB::rollBack();
            return apiResponse(500, '', $e);
        }
    }


    public function delete($id)
    {
        $task = Task::where('deleted_at', null)->find($id);
        ;
        if ($task) {
            if ($task->created_by !== auth('sanctum')->user()->id) {
                return apiResponse(401, '', "Unauthorized action");
            }
            if ($task->image) {
                $this->deleteFile($task->image);
            }
            $task->delete();
            return apiResponse(200, '', "Task Deleted Successfully");
        }
        return apiResponse(404, '', "Sorry! There Are No Task With That Id");
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


    public function validateTask($request)
    {
        $rules = [
            'name' => 'required|max:50',
            'description' => 'required|max:300',
            'status' => 'required|in:pending,in_progress,completed',
            'deadline' => 'nullable|date',
            'assign_to' => 'nullable|exists:users,id'
        ];

        if ($request->hasFile('image')) {
            $rules['image'] = 'mimes:jpg,jpeg,png,gif,webp|max:2048';
        }

        $messages = [
            'status.in' => 'Status must be one of: pending, in_progress, or completed.',
            'assign_to.exists' => 'Assigned user does not exist.'
        ];

        $request->validate($rules, $messages);
    }


}
