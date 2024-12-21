<?php

use App\Http\Controllers\TaskController;
use App\Livewire\Pages\Dashboard\Index as DashboardIndex;
use App\Livewire\Pages\Project\Index as ProjectIndex;
use App\Livewire\Pages\Project\Show as ProjectShow;
use App\Livewire\Pages\Project\ShowAll as ProjectShowAll;
use App\Livewire\Pages\Task\Create as TaskCreate;
use App\Livewire\Pages\Task\Update as TaskUpdate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    // 'verified',
])->group(function () {

    // Full Page Components Routes
    Route::get('/dashboard', DashboardIndex::class)->name('dashboard');
    Route::get('/projects', ProjectIndex::class)->name('projects.index');
    Route::get('/projects/{uuid}', ProjectShow::class)->name('projects.show');
    Route::get('/projects/show/all', ProjectShowAll::class)->name('projects.show.all');
    Route::get('/projects/tasks/create/{uuid}', TaskCreate::class)->name('projects.tasks.create');
    Route::get('/projects/tasks/update/{uuid}', TaskUpdate::class)->name('projects.tasks.update');

    Volt::route('/summary', 'pages.summary.index')
        ->name('summary.index');

    Volt::route('/checklist', 'pages.checklist.index')
    ->name('checklist.index');

    // User's Current Workspace Changing route when user's team is changed
    Route::get('/update-user-team-workspace/{uuid}', [TaskController::class, 'updateUserTeamAndWorkspace'])->name('update.user.team.workspace');
});
