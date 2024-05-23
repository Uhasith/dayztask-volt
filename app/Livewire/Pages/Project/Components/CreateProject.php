<?php

namespace App\Livewire\Pages\Project\Components;

use Filament\Forms;
use App\Models\Project;
use Livewire\Component;
use Filament\Forms\Form;
use Livewire\Attributes\Lazy;
use Illuminate\Contracts\View\View;
use Filament\Forms\Contracts\HasForms;
use App\Interfaces\ProjectRepositoryInterface;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;

#[Lazy]
class CreateProject extends Component implements HasForms
{
    use InteractsWithForms;

    private ProjectRepositoryInterface $projectRepository;

    public ?array $data = [];

    public function mount(ProjectRepositoryInterface $projectRepository): void
    {
        $this->projectRepository = $projectRepository;
        $this->form->fill();
    }

    protected function onValidationError(ValidationException $exception): void
    {
        Notification::make()
            ->title('Validation Error')
            ->body($exception->getMessage())
            ->danger()
            ->send();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')->rules(['required']),
                Forms\Components\FileUpload::make('company_logo'),
                Forms\Components\TextInput::make('visibility')->rules(['required']),
                Forms\Components\ColorPicker::make('font_color'),
                Forms\Components\ColorPicker::make('bg_color'),
                Forms\Components\TextInput::make('guest_users'),
            ])
            ->statePath('data')
            ->model(Project::class);
    }

    public function create(): void
    {
        $data = $this->form->getState();

        $data['user_id'] = auth()->id();

        $record = $this->projectRepository->createProject($data);

        $this->form->model($record)->saveRelationships();
    }

    public function render(): View
    {
        return view('livewire.pages.project.components.create-project');
    }
}
