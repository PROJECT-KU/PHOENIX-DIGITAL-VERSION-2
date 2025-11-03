<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Job extends Model
{
    use HasFactory;
    protected $table = "tbl_jobs";
    protected $fillable = ['title', 'is_active'];

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }
}
