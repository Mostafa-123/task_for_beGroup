<?php

namespace App\Http\Resources;

use App\Traits\ManageFileTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use function App\formatDate;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    use ManageFileTrait;

    public function toArray($request)
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'status'      => $this->status,
            'deadline'    => $this->deadline ? formatDate($this->deadline) : 'its open',
            'assign_to'   =>  $this->assign_to ? new UserResource($this->user_assign) : 'not assigned to any one for that time',
            'image'       => $this->image ? $this->getFileUrl($this->image) : null,
            'created_at'  => formatDate($this->created_at),
            'created_by'  => new UserResource($this->user_create),
            'updated_at'  => $this->updated_at ? formatDate($this->updated_at) : null,
        ];
    }
}
