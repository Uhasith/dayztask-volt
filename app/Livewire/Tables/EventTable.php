<?php

namespace App\Livewire\Tables;

use App\Models\Event;
use Livewire\Attributes\On;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;

final class EventTable extends PowerGridComponent
{
    public string $tableName = 'EventTable';

    public function setUp(): array
    {
        $this->showCheckBox();

        return [
            PowerGrid::header()
                ->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function header(): array
    {
        return [
            Button::add('bulk-delete')
                ->slot('Bulk Delete')
                ->class('rounded bg-red-600 text-white btn-sm')
                ->dispatch('bulkDelete.' . $this->tableName, []),
        ];
    }

    #[On('bulkDelete.{tableName}')]
    public function bulkDelete(): void
    {
        if ($this->checkboxValues) {
            Event::destroy($this->checkboxValues);
            $this->js('window.pgBulkActions.clearAll()'); // clear the count on the interface.
        }
    }

    public function datasource(): Builder
    {
        $teamIds = Auth::user()->currentTeam->allUsers()->pluck('id');
        return Event::query()->whereIn('user_id', $teamIds)->with('user')->latest();
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('event_user', function ($event) {
                return $event->user->name;
            })
            ->add('is_full_day', function ($event) {
                return $event->is_full_day ? e('Full day') : e('Half day');
            })
            ->add('start', function ($event) {
                return $event->start;
            })
            ->add('end', function ($event) {
                return $event->end;
            })
            ->add('created_at');
    }

    public function columns(): array
    {
        return [
            Column::make('Id', 'id')->hidden(),
            Column::make('User', 'event_user'),
            Column::make('Reason', 'description')->sortable(),
            Column::make('Approval', 'is_approved')->toggleable(
                trueLabel: 1,
                falseLabel: 0
            ),
            Column::make('Type', 'is_full_day'),
            Column::make('From', 'start'),
            Column::make('To', 'end'),
            Column::make('Submitted at', 'created_at')
            ->sortable()
                ->searchable(),
        ];
    }

    public function onUpdatedToggleable(string|int $id, string $field, string $value): void
    {
        Event::query()->find($id)->update([
            $field => e($value),
        ]);

        $this->dispatch('calendar--refresh');

    }

    public function filters(): array
    {
        return [];
    }
}
