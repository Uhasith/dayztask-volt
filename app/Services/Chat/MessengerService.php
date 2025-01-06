<?php

namespace App\Services\Chat;

use App\Models\ChatMessage;

/**
 * Class MessengerService
 * @package App\Services
 */
class MessengerService
{
function getMessengerCount() : int {
    return ChatMessage::where('seen_at', null)
        ->where('receiver_id', auth()->id())
        ->count();
}
}
