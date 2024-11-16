<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class TaskTracking extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_break' => 'boolean',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function project(): HasOneThrough
    {
        return $this->hasOneThrough(Project::class, Task::class, 'id', 'id', 'task_id', 'project_id');
    }
}
