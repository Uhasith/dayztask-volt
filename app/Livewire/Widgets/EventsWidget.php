<?php

namespace App\Livewire\Widgets;

use App\Models\Event;
use Carbon\Carbon;
use Closure;
use \Guava\Calendar\Widgets\CalendarWidget;
use Illuminate\Support\Collection;
use Guava\Calendar\Actions\CreateAction;
use Filament\Forms\Form;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Illuminate\Support\Facades\Log;

class EventsWidget extends CalendarWidget
{
    protected bool $eventClickEnabled = true;
    protected bool $dateClickEnabled = true;
    // protected string $calendarView = 'resourceTimeGridDay';

    protected ?string $defaultEventClickAction = 'edit';


    function getEvents(array $fetchInfo = []): Collection|array
    {
        return Event::whereNull('user_id')->orWhereIn('user_id', auth()->user()->currentTeam->allUsers()->pluck('id'))->where('is_approved', 1)->get()->map(function ($event) {
            $event['title'] = $event['description'];
            return $event;
        });
    }

    function onDateClick(array $info = []): void {}

    public function getHeaderActions(): array
    {
        return [
            CreateAction::make('createLeaveRequest')->model(Event::class)->label('Request a leave')
        ];
    }

    public function getEventClickContextMenuActions(): array
    {
        return [
            $this->editAction(),
            $this->deleteAction(),
        ];
    }

    public function getDateSelectContextMenuActions(): array
    {
        return $this->getDateClickContextMenuActions();
    }

    public function getDateClickContextMenuActions(): array
    {
        return [
            CreateAction::make('createLeaveRequest')
                ->model(Event::class)->label('Request a leave')
                ->mountUsing(fn($arguments, $form) => $form->fill([
                    'start' => data_get($arguments, 'dateStr'),
                    'end' => data_get($arguments, 'dateStr'),
                ]))
        ];
    }

    public function getSchema(?string $model = null): ?array
    {
        Log::info('TEST');
        return match ($model) {
            Event::class => [
                Hidden::make('user_id')->default(auth()->user()->id)->required(),
                Textarea::make('description')->label('Reason')->rows(5)->disabled($this->getEventRecord() && $this->getEventRecord()->user_id !== auth()->user()->id),
                Group::make([
                    Radio::make('is_full_day')->label(false)->options(
                        [
                            1 => 'Full day',
                            0 => 'Half day'
                        ]
                    )->reactive()
                ])->disabled($this->getEventRecord() && $this->getEventRecord()->user_id !== auth()->user()->id),
                Group::make([
                    DateTimePicker::make('start')
                        ->native(false)
                        ->seconds(false)
                        ->required()->minDate(Carbon::today()->addDays(2)),
                    // DateTimePicker::make('end')
                    //     ->native(false)
                    //     ->seconds(false)
                    //     ->required()->minDate(Carbon::today()->addDays(3))
                    //     ->hidden(function (Get $get) {
                    //         return $get('is_full_day') == 0;
                    //     }),
                ])->columns()->disabled($this->getEventRecord() && $this->getEventRecord()->user_id !== auth()->user()->id)
            ],
        };
    }

    public function getOptions(): array
    {
        return [
            'slotMinTime' => '09:00:00',
            'slotMaxTime' => '17:00:00',
        ];
    }

    public function authorize($ability, $arguments = [])
    {
        return true;
    }
}
