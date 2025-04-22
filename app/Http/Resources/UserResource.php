<?php

namespace App\Http\Resources;

use App\Traits\ManageFileTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    use ManageFileTrait;

    public function toArray($request)
    {
        return [
            'id'    => $this->id,
            'name'  => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'image' => $this->image ? $this->getFileUrl($this->image) : null,
            'created_tasks' => TaskResource::collection(
                $this->whenLoaded('created_tasks', function () {
                    return $this->created_tasks->whereNull('deleted_at');
                }, [])
            ),

            'assign_tasks' => TaskResource::collection(
                $this->whenLoaded('assign_tasks', function () {
                    return $this->assign_tasks->whereNull('deleted_at');
                }, [])
            ),
        ];
    }
}
