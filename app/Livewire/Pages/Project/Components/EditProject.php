<?php

namespace App\Livewire\Pages\Project\Components;

use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Livewire\Component;
use Illuminate\Contracts\View\View;

class EditProject extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public Project $record;

    public function mount(Project $record): void
    {
        $this->record = $record;
        $this->form->fill($record->attributesToArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('uuid')
                    ->label('UUID')
                    ->required()
                    ->maxLength(36),
                Forms\Components\TextInput::make('company_logo')
                    ->maxLength(255),
                Forms\Components\TextInput::make('font_color')
                    ->maxLength(255),
                Forms\Components\TextInput::make('bg_color')
                    ->maxLength(255),
                Forms\Components\FileUpload::make('bg_image')
                    ->image(),
                Forms\Components\TextInput::make('visibility')
                    ->required(),
                Forms\Components\TextInput::make('order')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('guest_users'),
            ])
            ->statePath('data')
            ->model($this->record);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $this->record->update($data);
    }

    public function render(): View
    {
        return view('livewire.pages.project.components.edit-project');
    }
}
