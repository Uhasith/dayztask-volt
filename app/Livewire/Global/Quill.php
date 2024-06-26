<?php

namespace App\Livewire\Global;

use Livewire\Component;
use Illuminate\Support\Facades\Log;

class Quill extends Component
{
    const EVENT_VALUE_UPDATED = 'quill_value_updated';

    public $value;

    public $quillId;

    public function mount($value = '')
    {
        $this->value = $value;
        $this->quillId = 'quill-' . uniqid();
    }

    public function updatedValue($value)
    {
        $this->dispatch(self::EVENT_VALUE_UPDATED, $value);
    }

    public function render()
    {
        return view('livewire.global.quill');
    }
}
