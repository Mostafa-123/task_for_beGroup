<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory,SoftDeletes;

    protected $guarded="id";

    protected $fillable = [
        'name',
        'description',
        'status',
        'deadline',
        'assign_to',
        'created_by',
        'image',
    ];

    public function user_create(){
        return $this->belongsTo(User::class,'created_by');
    }

    public function user_assign(){
        return $this->belongsTo(User::class,'assign_to');
    }
}
