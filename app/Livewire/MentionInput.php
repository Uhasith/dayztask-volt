<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;

class MentionInput extends Component
{
    public $name;

    public $suggestions = [];

    public function updatedName($value)
    {
        info($value);
        // Check if the last character is '@'
        if (str_starts_with($value, '@')) {
            // Fetch user suggestions
            $this->suggestions = User::where('name', 'like', '%'.substr($value, -1).'%')->get()->toArray();
        } else {
            $this->suggestions = [];
        }
    }

    public function selectSuggestion($name)
    {
        // Replace the "@" in the input with the selected name
        $this->name = preg_replace('/@$/', $name, $this->name);
        $this->suggestions = [];
    }

    public function render()
    {
        return view('livewire.mention-input');
    }
}
