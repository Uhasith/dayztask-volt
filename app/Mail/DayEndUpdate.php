<?php

namespace App\Mail;

use App\Models\Task;
use App\Models\TaskTracking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DayEndUpdate extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public $trackings;
    /**
     * Create a new message instance.
     */
    public function __construct(public $data)
    {
        $this->data = $data;
        $this->user = $data['user'];

        $this->data['completed_count'] = $this->user->tasks->where('status', 'done')->where('updated_at', Carbon::today())->count();
        $this->data['pending_count'] = $this->user->tasks()->where('status', 'todo')->count();
        $this->data['overdue_count'] = $this->user->tasks()->where('deadline', '<=', Carbon::now())->count();
        $this->data['screenshots_count'] = Media::where('collection_name', 'screenshot')->whereJsonContains('custom_properties', ['user_id' => $this->user->id])->where('created_at', Carbon::today())->count();

        $this->trackings = TaskTracking::select('task_id')
        ->selectRaw('SUM(TIMESTAMPDIFF(SECOND, start_time, end_time)) as total_tracking_time')
        ->whereDate('created_at',Carbon::today())
        ->with(['task']) // Include the related task details
        ->groupBy('task_id')
        ->get();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->user->name . ' is checking out for the day',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.day-end-update',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
