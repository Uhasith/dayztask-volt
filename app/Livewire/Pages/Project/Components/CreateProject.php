<?php

namespace App\Livewire\Pages\Project\Components;

use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Lazy;

#[Lazy]
class CreateProject extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\FileUpload::make('company_logo'),
                Forms\Components\ColorPicker::make('font_color'),
                Forms\Components\ColorPicker::make('bg_color'),
                Forms\Components\TextInput::make('visibility')
                    ->required(),
                Forms\Components\TextInput::make('guest_users'),
            ])
            ->statePath('data')
            ->model(Project::class);
    }

    public function create(): void
    {
        $data = $this->form->getState();

        $data['user_id'] = auth()->id();

        $record = Project::create($data);

        $this->form->model($record)->saveRelationships();
    }

    public function render(): View
    {
        return view('livewire.pages.project.components.create-project');
    }
}
