<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class JobApplication extends Model
{
    use HasFactory;
    protected $table = "tbl_job_applications";
    protected $fillable = ['job_id', 'name', 'email', 'phone', 'cv_path', 'cover_letter_path', 'ip_address'];

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }
    public function getCvUrlAttribute()
    {
        return Storage::disk('public')->url($this->cv_path);
    }

    public function getCoverLetterUrlAttribute()
    {
        return Storage::disk('public')->url($this->cover_letter_path);
    }
}
