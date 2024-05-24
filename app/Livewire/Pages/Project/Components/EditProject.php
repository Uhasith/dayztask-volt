<?php

namespace App\Livewire\Pages\Project\Components;

use Filament\Forms;
use App\Models\Project;
use Livewire\Component;
use Filament\Forms\Form;
use App\Services\Team\TeamService;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Filament\Forms\Components\Section;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use App\Services\Notifications\NotificationService;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

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
            ->model($this->record);
    }

    public function save(): void
    {
        DB::beginTransaction();

        try {
            $data = $this->form->getState();
            $this->record->update($data);
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
        return view('livewire.pages.project.components.edit-project');
    }
}
