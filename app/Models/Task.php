<?php

namespace App\Models;

use Illuminate\Support\Str;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Task extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes, Searchable;

    protected $guarded = ['id'];

    protected $casts = [
        'deadline' => 'date',
        'is_mark_as_done' => 'boolean',
        'is_checked' => 'boolean',
        'is_confirmed' => 'boolean',
        'is_archived' => 'boolean',
        'check_by_user_id' => 'string',
        'confirm_by_user_id' => 'string',
        'follow_up_user_id' => 'string',

    ];

    public function toSearchableArray()
    {
        return [
            'id' => (int) $this->id,
            'name' => $this->name
        ];
    }

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

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(368)
            ->height(232);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'tasks_users', 'task_id', 'user_id');
    }

    public function subTasks()
    {
        return $this->hasMany(SubTask::class);
    }

    public function checkByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'check_by_user_id');
    }

    public function confirmByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirm_by_user_id');
    }

    public function followUpUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'follow_up_user_id');
    }
}
