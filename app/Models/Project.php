<?php

namespace App\Models;

use Illuminate\Support\Str;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Project extends Model implements HasMedia
{ 
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $fillable = ['uuid', 'user_id', 'title', 'company_logo', 'bg_image', 'bg_color', 'font_color', 'visibility',  'order', 'guest_users'];

    protected $casts = [
        'guest_users' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
