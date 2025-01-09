<?php

use App\Http\Controllers\MessengerController;
use App\Http\Controllers\TaskController;
use App\Livewire\Pages\Dashboard\Index as DashboardIndex;
use App\Livewire\Pages\Project\Index as ProjectIndex;
use App\Livewire\Pages\Project\Show as ProjectShow;
use App\Livewire\Pages\Project\ShowAll as ProjectShowAll;
use App\Livewire\Pages\Task\Create as TaskCreate;
use App\Livewire\Pages\Task\Update as TaskUpdate;
use App\Mail\DayEndUpdate;
use App\Models\Event;
use App\Models\Task;
use App\Models\TaskTracking;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
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

    Route::get('/projects/user/search', 'App\Http\Controllers\API\ProjectController@webGetProjects')->name('projects.search');
    Route::get('/tasks/user/search', 'App\Http\Controllers\API\TaskController@getTeamTasks')->name('tasks.search');

    Volt::route('/status', 'pages.status.index')
    ->name('status.index');

    Volt::route('/summary', 'pages.summary.index')
        ->name('summary.index');

    Volt::route('/checklist', 'pages.checklist.index')
    ->name('checklist.index');

    // User's Current Workspace Changing route when user's team is changed
    Route::get('/update-user-team-workspace/{uuid}', [TaskController::class, 'updateUserTeamAndWorkspace'])->name('update.user.team.workspace');

    // Chat Room
    Volt::route('messenger', 'pages.chat.messenger')->name('messenger');
    Route::get('/messenger/search-member', [MessengerController::class, 'search_member'])->name('messenger.search-member');

    // Team Owner
    Volt::route('event-approvals', 'pages.team-owner.event-approvals')->name('event-approvals');
    Volt::route('screenshots', 'pages.team-owner.screenshots')->name('screenshots');

    Route::get('/test-mail', function () {
        $data = [
            'user' => Auth::user(),
            'team' => Auth::user()->currentTeam,
            'checkin' => Carbon::parse('2025-01-01 09:00:00'),
            'checkout' => Carbon::parse('2025-01-01 18:00:00'),
            'location' => 'Office',
            'update' => 'Day End Update',
        ];

        echo Mail::to(Auth::user()->currentTeam->owner->email)->queue(new DayEndUpdate($data));

    //    $tasks = Task::with('project')->where('user_id', 1)->whereDate('updated_at', Carbon::parse('2025-01-01'))->select('task_id', DB::raw('SUM(TIMESTAMPDIFF(SECOND, start_time, end_time)) as total_tracking_time'))
        // ->groupBy('task_id')->get();

        $trackings = TaskTracking::select('task_id')
        ->selectRaw('SUM(TIMESTAMPDIFF(SECOND, start_time, end_time)) as total_tracking_time')
        ->whereDate('created_at',Carbon::today())
        ->with(['task']) // Include the related task details
        ->groupBy('task_id')
        ->get();

        foreach ($trackings as $tracking):
            echo $tracking->task->project->title . '<br>';
            echo $tracking->task->name . '<br>';
            echo CarbonInterval::seconds($tracking->total_tracking_time)->cascade()->forHumans() . '<br>';
            echo $tracking->task->status . '<br>';
    endforeach;

        // dd($trackings);
    });
});
