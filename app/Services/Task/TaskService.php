<?php

namespace App\Services\Task;

use App\Mail\NotificationMail;
use App\Models\SubTask;
use App\Models\Task;
use App\Models\TaskTracking;
use App\Models\User;
use App\Services\Notifications\NotificationService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use Spatie\ImageOptimizer\OptimizerChainFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Class TaskService
 */
class TaskService extends Component
{
    public function search($query)
    {
        return Task::search($query)->get();
    }

    public function markAsDone($taskId)
    {
        DB::beginTransaction();

        try {
            $task = Task::where('id', $taskId)->first();

            if (! $task) {
                app(NotificationService::class)->sendExeptionNotification();

                return;
            }

            $task->update(['status' => 'done']);
            $updatedTask = $task->fresh();
            DB::commit();

            return $updatedTask;
        } catch (\Exception $e) {
            DB::rollBack();
            app(NotificationService::class)->sendExeptionNotification();
            throw $e;
        }
    }

    public function approveTask($taskUuid)
    {
        DB::beginTransaction();

        try {
            $task = Task::where('uuid', $taskUuid)->first();

            if (! $task) {
                app(NotificationService::class)->sendExeptionNotification();

                return;
            }

            if (Auth::user()->id == $task->check_by_user_id && $task->is_checked == false) {
                $task->update(['is_checked' => true]);
            }

            if (Auth::user()->id == $task->confirm_by_user_id && $task->is_confirmed == false) {
                $task->update(['is_confirmed' => true]);
            }
            $assignedUsers = $task->users;
            foreach ($assignedUsers as $user) {
                if ($user->id !== Auth::id()) {
                    $title = 'Task Approved';
                    $body = 'Submitted  ' . $task->name . ' task is approved by ' . Auth::user()->name . '.';

                    app(NotificationService::class)->sendUserTaskDBNotification($user, $title, $body, $task->id);

                    $mailData = [
                        'email' => $user->email,
                        'email_subject' => 'Task Approved',
                        'email_body' => 'Submitted  ' . $task->name . ' task is approved by ' . Auth::user()->name . '.',
                        'task' => $task,
                        'user' => $user,
                        'caused_by' => Auth::user(),
                    ];

                    Mail::to($user->email)->queue(new NotificationMail($mailData));
                }
            }

            $updatedTask = $task->fresh();
            DB::commit();

            return $updatedTask;
        } catch (\Exception $e) {
            DB::rollBack();
            app(NotificationService::class)->sendExeptionNotification();
            throw $e;
        }
    }

    public function rejectTask($taskUuid, $rejectionReason)
    {
        DB::beginTransaction();

        try {
            $task = Task::where('uuid', $taskUuid)->first();

            if (! $task) {
                app(NotificationService::class)->sendExeptionNotification();

                return;
            }

            $task->update(['is_checked' => false, 'is_confirmed' => false, 'status' => 'todo']);

            $assignedUsers = $task->users;
            foreach ($assignedUsers as $user) {
                if ($user->id !== Auth::id()) {
                    $title = 'Task Rejected';
                    $body = '' . $task->name . ' task is rejected by ' . Auth::user()->name . '.' . '<br>' . 'Reason: ' . '<strong>' . $rejectionReason . '</strong>';

                    app(NotificationService::class)->sendUserTaskDBNotification($user, $title, $body, $task->id);

                    $mailData = [
                        'email' => $user->email,
                        'email_subject' => 'Task Approved',
                        'email_body' => '' . $task->name . ' task is rejected by ' . Auth::user()->name . '.' . '<br>' . 'Reason: ' . '<strong>' . $rejectionReason . '</strong>',
                        'task' => $task,
                        'user' => $user,
                        'caused_by' => Auth::user(),
                    ];

                    Mail::to($user->email)->queue(new NotificationMail($mailData));
                }
            }

            $updatedTask = $task->fresh();
            DB::commit();

            return $updatedTask;
        } catch (\Exception $e) {
            DB::rollBack();
            app(NotificationService::class)->sendExeptionNotification();
            throw $e;
        }
    }

    public function revertToTodo($taskId)
    {
        DB::beginTransaction();

        try {
            $task = Task::where('id', $taskId)->first();

            if (! $task) {
                app(NotificationService::class)->sendExeptionNotification();

                return;
            }

            $task->update(['status' => 'todo']);
            $updatedTask = $task->fresh();
            DB::commit();

            return $updatedTask;
        } catch (\Exception $e) {
            DB::rollBack();
            app(NotificationService::class)->sendExeptionNotification();
            throw $e;
        }
    }

    public function createTask($validatedData)
    {
        DB::beginTransaction();

        try {

            $task = Task::updateOrCreate(['id' => $validatedData['task_id'] ?? null], $validatedData);

            if ($task->wasRecentlyCreated) {
                $lastTask = Task::orderBy('page_order', 'asc')->first();
                $task->page_order = $lastTask ? $lastTask->order + 1 : 0;
                $task->save();
            }

            if (! empty($validatedData['subtasks'])) {
                foreach ($validatedData['subtasks'] as $subtask) {
                    if (! empty($subtask['subTask'])) {
                        $data = [
                            'name' => $subtask['subTask'],
                            'task_id' => $task->id,
                        ];

                        SubTask::create($data);
                    }
                }
            }

            if (! empty($validatedData['attachments'])) {
                $optimizerChain = OptimizerChainFactory::create();

                foreach ($validatedData['attachments'] as $attachment) {
                    // $originalFileName = $attachment->getClientOriginalName();
                    $path = $attachment->getRealPath();

                    // Optimize the image
                    $optimizerChain->optimize($path);

                    // Add to media collection
                    $task->addMedia($path)
                        ->toMediaCollection('attachments');
                }
            }

            $task->users()->detach();

            if (! empty($validatedData['assigned_users'])) {
                $task->users()->attach($validatedData['assigned_users']);
            }

            $assignedUsers = $task->users;

            foreach ($assignedUsers as $user) {
                if ($user->id !== Auth::id()) {
                    $title = 'Task Assigned';
                    $body = 'You were Assigned to task ' . $task->name . ' by ' . Auth::user()->name . '.';

                    app(NotificationService::class)->sendUserTaskDBNotification($user, $title, $body, $task->id);

                    $mailData = [
                        'email' => $user->email,
                        'email_subject' => 'New Task.',
                        'email_body' => 'We are excited to inform you that ' . Auth::user()->name . ' has just assigned you the task ' . $task->name . '. Your expertise and skills are valued, and we trust you will excel in this assignment.',
                        'task' => $task,
                        'user' => $user,
                        'caused_by' => Auth::user(),
                    ];

                    Mail::to($user->email)->queue(new NotificationMail($mailData));
                }
            }

            DB::commit();

            return $task;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateTask($validatedData)
    {
        DB::beginTransaction();

        try {
            $task = Task::updateOrCreate(['id' => $validatedData['task_id'] ?? null], $validatedData);

            if ($task->wasRecentlyCreated) {
                $lastTask = Task::orderBy('page_order', 'asc')->first();
                $task->page_order = $lastTask ? $lastTask->order + 1 : 0;
                $task->save();
            }

            // Handle subtasks
            if (! empty($validatedData['subtasks'])) {
                foreach ($validatedData['subtasks'] as $subtask) {
                    // Add or update subtasks
                    if (! empty($subtask['subTask'])) {
                        SubTask::updateOrCreate(
                            ['id' => $subtask['id'] ?? null],
                            [
                                'name' => $subtask['subTask'],
                                'task_id' => $task->id,
                                'is_completed' => $subtask['is_completed'] ?? false,
                            ]
                        );
                    }
                }
            }

            if (! empty($validatedData['oldSubtasks'])) {
                foreach ($validatedData['oldSubtasks'] as $subtask) {
                    // Check if the subtask is old and removed
                    if (isset($subtask['old'])) {
                        $existingSubtask = SubTask::find($subtask['id']);
                        if ($existingSubtask) {
                            $existingSubtask->update([
                                'name' => $subtask['subTask'] ?? $existingSubtask->name,
                                'is_completed' => $subtask['is_completed'] ?? $existingSubtask->is_completed,
                            ]);
                        }
                    }
                }
            }

            if (! empty($validatedData['oldRemovedSubTasks'])) {
                $subtasks = SubTask::whereIn('id', $validatedData['oldRemovedSubTasks'])->get();
                foreach ($subtasks as $item) {
                    $item->delete();
                }
            }

            // Handle attachments
            if (! empty($validatedData['attachments'])) {
                $optimizerChain = OptimizerChainFactory::create();

                foreach ($validatedData['attachments'] as $attachment) {
                    // $originalFileName = $attachment->getClientOriginalName();
                    $path = $attachment->getRealPath();

                    // Optimize the image
                    $optimizerChain->optimize($path);

                    // Add to media collection
                    $task->addMedia($path)
                        ->toMediaCollection('attachments');
                }
            }

            if (! empty($validatedData['oldRemovedAttachments'])) {
                $mediaItems = Media::whereIn('id', $validatedData['oldRemovedAttachments'])->get();
                foreach ($mediaItems as $mediaItem) {
                    $mediaItem->delete(); // This will delete both the database record and the file
                }
            }

            // Get the list of currently assigned users
            $currentUsers = $task->users->pluck('id')->toArray();

            // Detach and reattach users
            $task->users()->detach();

            if (! empty($validatedData['assigned_users'])) {
                $newAssignedUsers = $validatedData['assigned_users'];
                $task->users()->attach($newAssignedUsers);

                // Determine newly assigned users
                $newUsers = array_diff($newAssignedUsers, $currentUsers);

                // Send notifications to newly assigned users
                foreach ($newUsers as $userId) {
                    $user = User::find($userId);
                    if ($user && $user->id !== Auth::id()) {
                        $title = 'Task Assigned';
                        $body = 'You were Assigned to task ' . $task->name . ' by ' . Auth::user()->name . '.';

                        app(NotificationService::class)->sendUserTaskDBNotification($user, $title, $body, $task->id);

                        $mailData = [
                            'email' => $user->email,
                            'email_subject' => 'New Task.',
                            'email_body' => 'We are excited to inform you that ' . Auth::user()->name . ' has just assigned you the task ' . $task->name . '. Your expertise and skills are valued, and we trust you will excel in this assignment.',
                            'task' => $task,
                            'user' => $user,
                            'caused_by' => Auth::user(),
                        ];

                        Mail::to($user->email)->queue(new NotificationMail($mailData));
                    }
                }
            }

            DB::commit();

            return $task;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateTaskStatus($taskId, $status)
    {
        try {

            /** @var \App\Models\User */
            $user = Auth::user();
            $team = $user->currentTeam;
            $roleName = $user->teamRole($team)->key;

            $task = Task::find($taskId);
            $task->status = $status;
            $task->save();
            $task = $task->refresh();

            if ($task->status == 'todo' && $task->check_by_user_id) {
                $task->is_checked = null;
                $task->is_confirmed = null;
                $task->is_mark_as_done = false;
                $task->is_archived = false;
                $task->save();
            }

            if ($task->status == 'todo' && $task->follow_up_user_id) {
                $followUpTask = Task::where('parent_task_id', $task->id)->first();

                if ($followUpTask) {
                    $followUpTask->delete();
                }
            }

            if ($task->check_by_user_id && $task->status == 'done' && $roleName != 'owner') {
                $task->is_mark_as_done = true;
                $task->save();

                $title = 'Please check your checklist';
                $body = 'Fantastic news! ' . $user->name . ' has successfully completed ' . $task->name . ' task. Please review it on your checklist.';
                $checkByUser = User::find($task->check_by_user_id);

                if ($checkByUser) {
                    $route = route('checklist.index');
                    $buttonText = 'View Checklist';
                    app(NotificationService::class)->sendDBNotificationWithAction($checkByUser, $title, $body, $route, $buttonText, $task->id);

                    $mailData = [
                        'email' => $checkByUser->email,
                        'email_subject' => $title,
                        'email_body' => $body,
                        'task' => $task,
                        'user' => $checkByUser,
                        'caused_by' => $user,
                    ];

                    Mail::to($checkByUser->email)->queue(new NotificationMail($mailData));
                }
            } elseif ($task->check_by_user_id && $task->status == 'done' && $roleName == 'owner') {
                $task->is_checked = true;
                $task->is_confirmed = true;
                $task->is_mark_as_done = true;
                $task->save();

                $title = 'Please check your checklist';
                $body = 'Fantastic news! ' . $user->name . ' has successfully completed ' . $task->name . ' task. Please review it on your checklist.';
                $checkByUser = User::find($task->check_by_user_id);

                if ($checkByUser && Auth::user()->id != $task->check_by_user_id) {
                    $route = route('checklist.index');
                    $buttonText = 'View Checklist';
                    app(NotificationService::class)->sendDBNotificationWithAction($checkByUser, $title, $body, $route, $buttonText, $task->id);

                    $mailData = [
                        'email' => $checkByUser->email,
                        'email_subject' => $title,
                        'email_body' => $body,
                        'task' => $task,
                        'user' => $checkByUser,
                        'caused_by' => $user,
                    ];

                    Mail::to($checkByUser->email)->queue(new NotificationMail($mailData));
                }
            }

            if ($task->follow_up_user_id && $task->status == 'done') {
                $lastTask = Task::where('project_id', $task->project_id)->orderBy('id', 'desc')->first();
                $taskOrder = $lastTask ? $lastTask->page_order + 1 : 0;

                $followUpTask = Task::create([
                    'name' => $task->follow_up_message ? $task->follow_up_message : $task->name . ' Follow Up',
                    'user_id' => $user->id,
                    'parent_task_id' => $task->id,
                    'order' => $taskOrder,
                    'project_id' => $task->project_id,
                    'priority' => 'high',
                    'status' => 'todo',
                ]);

                $followUpTask->users()->attach($task->follow_up_user_id);

                $title = 'New Follow Up Task';
                $body = 'Fantastic news! ' . $user->name . ' has successfully completed ' . $task->name . '. You are assigned to a Follow Up Task named ' . $followUpTask->name . '. Please review it on your task list.';
                $followUpUser = User::find($task->follow_up_user_id);

                if ($followUpUser) {
                    app(NotificationService::class)->sendUserTaskDBNotification($followUpUser, $title, $body, $followUpTask->id);

                    $mailData = [
                        'email' => $followUpUser->email,
                        'email_subject' => $title,
                        'email_body' => $body,
                        'task' => $task,
                        'user' => $followUpUser,
                    ];

                    Mail::to($followUpUser->email)->queue(new NotificationMail($mailData));
                }
            }

            DB::commit();

            $updatedTask = $task->refresh();

            return $updatedTask;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function calculateAllUsersTotalTrackedTime($taskId)
    {
        $trackedRecords = TaskTracking::where('task_id', $taskId)
            ->get();

        if ($trackedRecords->isEmpty()) {
            return '00:00:00';
        }

        $totalSeconds = $trackedRecords->reduce(function ($carry, $record) {

            if ($record->end_time) {
                $start = strtotime($record->created_at);
                $end = strtotime($record->updated_at);
            } else {
                $start = strtotime($record->created_at);
                $end = strtotime(Carbon::now());
            }

            return $carry + ($end - $start);
        }, 0);

        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $seconds = $totalSeconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    public function calculateTotalTrackedTime($taskId, $userId)
    {
        $trackedRecords = TaskTracking::where('user_id', $userId)
            ->where('task_id', $taskId)
            ->get();

        if ($trackedRecords->isEmpty()) {
            return '00:00:00';
        }

        $totalSeconds = $trackedRecords->reduce(function ($carry, $record) {

            if ($record->end_time) {
                $start = strtotime($record->created_at);
                $end = strtotime($record->updated_at);
            } else {
                $start = strtotime($record->created_at);
                $end = strtotime(Carbon::now());
            }

            return $carry + ($end - $start);
        }, 0);

        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $seconds = $totalSeconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    public function startTracking($uuid)
    {
        DB::beginTransaction();

        try {
            $userId = Auth::user()->id;
            $task = Task::where('uuid', $uuid)->first();

            if (! $task) {
                app(NotificationService::class)->sendExeptionNotification();

                return;
            }

            $taskId = $task->id;

            $alreadyTrackingDifferentTask = TaskTracking::where('user_id', $userId)
                ->whereNull('end_time')
                ->where('enable_tracking', true)
                ->with('task')
                ->first();

            if ($alreadyTrackingDifferentTask) {
                $this->stopTracking($alreadyTrackingDifferentTask->task->uuid, true);
            }

            $taskTracking = TaskTracking::firstOrCreate(
                [
                    'task_id' => $taskId,
                    'user_id' => $userId,
                    'end_time' => null,
                ],
                [
                    'start_time' => now(),
                    'enable_tracking' => true,
                ]
            );

            $task->update(['status' => 'doing']);
            $updatedTask = $task->fresh();

            app(NotificationService::class)->sendSuccessNotification('Task tracking started successfully');

            DB::commit();

            return [
                'taskId' => $taskId,
                'alreadyTrackingDifferentTask' => $alreadyTrackingDifferentTask,
                'updatedTask' => $updatedTask,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function stopTracking($uuid, $alreadyTrackingDifferentTask = false)
    {
        DB::beginTransaction();

        try {
            $userId = Auth::user()->id;
            $task = Task::where('uuid', $uuid)->first();

            if (! $task) {
                app(NotificationService::class)->sendExeptionNotification();

                return;
            }

            $taskId = $task->id;

            $taskTracking = TaskTracking::where('task_id', $taskId)
                ->where('user_id', $userId)
                ->whereNull('end_time')
                ->where('enable_tracking', true)
                ->orderBy('id', 'desc')
                ->first();

            if ($taskTracking) {
                $taskTracking->update([
                    'end_time' => now(),
                    'enable_tracking' => false,
                ]);
            }

            TaskTracking::where('task_id', $taskId)
                ->where('user_id', $userId)
                ->where('enable_tracking', true)
                ->update(['enable_tracking' => false]);

            $task->update(['status' => 'todo']);
            $updatedTask = $task->fresh();

            if (! $alreadyTrackingDifferentTask) {
                app(NotificationService::class)->sendSuccessNotification('Task tracking ended successfully');
            }

            DB::commit();

            return [
                'taskId' => $taskId,
                'updatedTask' => $updatedTask,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function checkIfProofNeeded($taskId)
    {
        try {
            $task = Task::where('id', $taskId)->first();

            if (! $task) {
                app(NotificationService::class)->sendExeptionNotification();

                return;
            }

            if (empty($task->proof_method)) {
                return false;
            } else {
                $requiredType = $task->proof_method;
                // Define proof method types
                $proofMethods = [
                    'screenshot' => 'proof_screenshot',
                    'multiple_screenshots' => 'proof_multiple_screenshots',
                    'video' => 'proof_video_link',
                    'comment' => 'proof_comment',
                    'file' => 'proof_file',
                ];

                // Check only for the required type
                if (array_key_exists($requiredType, $proofMethods)) {
                    $collection = $proofMethods[$requiredType];

                    switch ($requiredType) {
                        case 'screenshot':
                        case 'multiple_screenshots':
                        case 'file':
                            // Check if the media collection is not empty
                            if ($task->getMedia($collection)->isEmpty()) {
                                return true; // Proof is needed
                            }
                            break;

                        case 'video':
                            // Check if the proof_video_link column is not empty
                            if (empty($task->proof_video_link)) {
                                return true; // Proof is needed
                            }
                            break;

                        case 'comment':
                            // Check if the proof_comment column is not empty
                            if (empty($task->proof_comment)) {
                                return true; // Proof is needed
                            }
                            break;

                        default:
                            return false; // Unknown proof method
                    }

                    return false; // Proof is not needed, as it is already present
                }

                return false; // Invalid proof method, no proof is needed
            }
        } catch (\Exception $e) {
            app(NotificationService::class)->sendExeptionNotification();
            throw $e;
        }
    }
}
