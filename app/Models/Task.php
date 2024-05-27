<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid', 'project_id', 'check_by_user_id', 'confirm_by_user_id', 'follow_up_user_id',
        'name', 'description', 'status', 'priority', 'page_order', 'follow_up_message',
        'proof_method', 'invoice_reference', 'estimate_time', 'deadline', 'recurring_period',
        'is_mark_as_done', 'is_checked', 'is_confirmed', 'is_archived'
    ];

    protected $casts = [
        'deadline' => 'date',
        'is_mark_as_done' => 'boolean',
        'is_checked' => 'boolean',
        'is_confirmed' => 'boolean',
        'is_archived' => 'boolean',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = Str::uuid();
        });

        // static::deleting(function ($task) {
        //     // Delete related comments, attachments, notifications, tracking records, and sub-tasks
        //     $task->comments()->delete();
        //     $task->attachments()->delete();
        //     $task->notifications()->delete();
        //     $task->tracking_records()->delete();
        //     $task->sub_tasks()->delete();

        //     // Detach related users in many-to-many relationships
        //     $task->users()->detach();
        //     $task->watch_users()->detach();
        // });
    }

    public function project() : BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function checkByUser() : BelongsTo
    {
        return $this->belongsTo(User::class, 'check_by_user_id');
    }

    public function confirmByUser() : BelongsTo
    {
        return $this->belongsTo(User::class, 'confirm_by_user_id');
    }

    public function followUpUser() : BelongsTo
    {
        return $this->belongsTo(User::class, 'follow_up_user_id');
    }
}
