<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskCategory extends Model
{
    protected $fillable = ['nama'];

    public function labels(): HasMany
    {
        return $this->hasMany(TaskCategoryLabel::class)->orderBy('nama');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
