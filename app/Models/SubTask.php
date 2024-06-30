<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SubTask extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_completed' => 'boolean',
    ];


    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
    }
}
