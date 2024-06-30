<?php

namespace App\Services\Task;

use Exception;
use App\Models\Task;
use App\Models\SubTask;
use App\Mail\NotificationMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Spatie\ImageOptimizer\OptimizerChainFactory;
use App\Services\Notifications\NotificationService;

/**
 * Class TaskService
 * @package App\Services
 */
class TaskService
{

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

            if (!empty($validatedData['subtasks'])) {
                foreach ($validatedData['subtasks'] as $subtask) {
                    if (!empty($subtask['subTask'])) {
                        $data = [
                            'name' => $subtask['subTask'],
                            'task_id' => $task->id,
                        ];

                        SubTask::create($data);
                    }
                }
            }

            if (!empty($validatedData['attachments'])) {
                $optimizerChain = OptimizerChainFactory::create();

                foreach ($validatedData['attachments'] as $attachment) {
                    $path = $attachment->store('TaskAttachments');
                    $absolutePath = storage_path('app/' . $path);

                    // Optimize the image
                    $optimizerChain->optimize($absolutePath);

                    // Add to media collection
                    $task->addMedia($absolutePath)
                        ->preservingOriginal()
                        ->toMediaCollection('attachments');
                }
            }

            $task->users()->detach();

            if (!empty($validatedData['assigned_users'])) {
                $task->users()->attach($validatedData['assigned_users']);
            }

            $assignedUsers = $task->users;

            foreach ($assignedUsers as $user) {
                if ($user->id !== Auth::id()) {
                    $title = 'Task Assigned';
                    $body = 'You were Assigned to task ' . $task->name . ' by ' . Auth::user()->name . '.';

                    app(NotificationService::class)->sendDBNotification($user, $title, $body);

                    $mailData = [
                        'email' => $user->email,
                        'email_subject' => 'New Task.',
                        'email_body' => 'We are excited to inform you that ' . Auth::user()->name . ' has just assigned you the task ' . $task->name . '. Your expertise and skills are valued, and we trust you will excel in this assignment.',
                        'task' => $task,
                        'user' => $user,
                        'caused_by' => Auth::user()
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
}
