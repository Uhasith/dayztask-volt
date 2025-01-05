<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\TestTask;

class MyTask extends Component
{
    public $name, $description, $deadline;
    public $tasks;

    public function mount()
    {
        $this->tasks = Task::all();
    }

    public function resetInputFields()
    {
        $this->name = '';
        $this->description = '';
        $this->deadline = '';
    }

    public function store()
    {
        $this->validate([
            'name' => 'required|min:3',
            'description' => 'required|min:5',
            'deadline' => 'required|date',
        ]);

        Task::create([
            'name' => $this->name,
            'description' => $this->description,
            'deadline' => $this->deadline,
        ]);

        session()->flash('message', 'Task created successfully!');
        $this->resetInputFields();
        $this->tasks = Task::all(); // Refresh the task list
    }

    public function render()
    {
        return view('livewire.my-task');
    }
}
