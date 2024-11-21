<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class ChatMessage extends Model
{
    protected $guarded = ['id'];

     /**
     * Get the user's first name.
     */
        protected function createdAt(): Attribute
        {
            return Attribute::make(
                get: fn (string $value) => Carbon::parse($value)->format(config('dayztasks.date_time_format')),
            );
        }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
 
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
