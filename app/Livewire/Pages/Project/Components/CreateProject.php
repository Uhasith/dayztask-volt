<?php

namespace App\Livewire\Pages\Project\Components;

use App\Models\Project;
use App\Services\Notifications\NotificationService;
use App\Services\Team\TeamService;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
class CreateProject extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public function mount(): void
    {
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
                TextInput::make('title')->rules(['required']),

                Section::make('Project Display Settings')
                    ->schema([
                        Forms\Components\Select::make('visibility')->default('public')->options([
                            'public' => 'Public',
                            'private' => 'Private',
                        ])->rules(['required']),
                        Forms\Components\ColorPicker::make('font_color'),
                        Forms\Components\ColorPicker::make('bg_color'),
                        Forms\Components\Select::make('guest_users')->options(app(TeamService::class)->getGuestUsers())->multiple()->searchable()->label('Guest Users'),
                    ])->columns([
                        'sm' => 1,
                        'lg' => 2,
                    ]),

                SpatieMediaLibraryFileUpload::make('company_logo')->image()->collection('company_logo')->optimize('webp'),
            ])
            ->statePath('data')
            ->model(Project::class);
    }

    public function create(): void
    {
        DB::beginTransaction();

        try {
            $data = $this->form->getState();
            $data['user_id'] = auth()->id();
            $data['workspace_id'] = auth()->user()->current_workspace_id;
            $record = Project::create($data);
            $this->form->model($record)->saveRelationships();
            $this->form->fill();

            app(NotificationService::class)->sendSuccessNotification('Project updated successfully');

            DB::commit();

            $this->dispatch('close-modal', id: 'project-drawer');
            $this->redirectRoute('projects.index');
        } catch (\Exception $e) {
            DB::rollBack();
            app(NotificationService::class)->sendExeptionNotification();
            throw $e;
        }
    }

    public function render(): View
    {
        return view('livewire.pages.project.components.create-project');
    }
}
