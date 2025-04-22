<?php
namespace App\Services;

use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Traits\ManageFileTrait;
use Exception;

class TaskService
{
    use ManageFileTrait;

    public function createTask($data, $user)
    {
        try {
            DB::beginTransaction();
            $image = $this->uploadFile($data, 'image', 'TasksPhotos');

            $task = Task::create([
                'name' => $data['name'],
                'description' => $data['description'],
                'status' => $data['status'],
                'deadline' => $data['deadline'] ?? null,
                'assign_to' => $data['assign_to'] ?? null,
                'created_by' => $user->id,
                'image' => $image,
            ]);

            DB::commit();
            return $task;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateTask(Task $task, $data)
    {
        try {
            DB::beginTransaction();
            $image = $task->image;

            if (isset($data['image'])) {
                if ($task->image) {
                    $this->deleteFile($task->image);
                }
                $image = $this->uploadFile($data, 'image', 'TasksPhotos');
            }

            $task->update([
                'name' => $data['name'] ?? $task->name,
                'description' => $data['description'] ?? $task->description,
                'status' => $data['status'] ?? $task->status,
                'deadline' => $data['deadline'] ?? $task->deadline,
                'assign_to' => $data['assign_to'] ?? $task->assign_to,
                'image' => $image,
            ]);

            DB::commit();
            return $task;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteTask(Task $task)
    {
        if ($task->image) {
            $this->deleteFile($task->image);
        }
        $task->image=null;
        $task->save();
        return $task->delete();
    }

    public function assign_task_to_user(Task $task,User $user){
        $task->assign_to=$user->id;
        $task->save();
        return $task;
    }
}
