<?php

namespace App\Observers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class TaskObserver
{
    /**
     * Handle the Task "creating" event.
     *
     * @return void
     */
    public function creating(Task $task)
    {
        $task->uuid = Str::uuid();
    }

    /**
     * Handle the Task "created" event.
     */
    public function created(Task $task): void
    {
        //
    }

    /**
     * Handle the Task "updated" event.
     */
    public function updated(Task $task): void
    {
        //
    }

    /**
     * Handle the Task "deleting" event.
     */
    public function deleting(Task $task): void
    {
        DB::beginTransaction();

        try {
            // Retrieve the parent task and its follow-up user, if they exist
            $parentTask = $task->parent_task_id ? Task::find($task->parent_task_id) : null;
            $user = $parentTask && $parentTask->follow_up_user_id ? User::find($parentTask->follow_up_user_id) : null;

            // Detach all users associated with the task
            $task->users()->detach();

            // Delete all subtasks associated with the task
            $task->subtasks()->each(function ($subtask) {
                $subtask->delete(); // This triggers the `deleting` observer for each subtask
            });

            // Delete all tracking records associated with the task
            $task->trackingRecords()->delete();

            // Delete notifications related to the task if the follow-up user exists
            if ($user) {
                $user->notifications
                    ->filter(fn($notification) => isset($notification->data['viewData']['task_id']) && $notification->data['viewData']['task_id'] == $task->id)
                    ->each(fn($notification) => $notification->delete());
            }

            // Commit the transaction
            DB::commit();
        } catch (\Exception $e) {
            // Rollback the transaction in case of an error
            DB::rollBack();

            // Log the error or rethrow it
            throw $e;
        }
    }

    /**
     * Handle the Task "deleted" event.
     */
    public function deleted(Task $task): void
    {
        //
    }

    /**
     * Handle the Task "restored" event.
     */
    public function restored(Task $task): void
    {
        //
    }

    /**
     * Handle the Task "force deleted" event.
     */
    public function forceDeleted(Task $task): void
    {
        //
    }
}
