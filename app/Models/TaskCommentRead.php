<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskCommentRead extends Model
{
    protected $fillable = [
        'task_id',
        'group_id',
        'user_id',
        'last_read_at',
    ];

    protected $casts = [
        'last_read_at' => 'datetime',
    ];
}
