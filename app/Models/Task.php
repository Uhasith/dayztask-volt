<?php

namespace App\Models;

use App\Observers\TaskObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

#[ObservedBy([TaskObserver::class])]
class Task extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, Searchable, SoftDeletes;

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
            'name' => $this->name,
        ];
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = Str::uuid();
        });
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

    public function trackingRecords(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_trackings', 'task_id', 'user_id');
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
