<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Log;
use App\Mail\NotificationMail;
use App\Models\User;
use App\Services\Notifications\NotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

new class extends Component {
    public $model;
    public $commentBody = '';
    public $teamMembers = [];
    public $filteredTeamMembers = [];
    public $showDropdown = false;

    public function mount()
    {
        $this->teamMembers = auth()
            ->user()
            ->currentTeam->allUsers()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => strtolower(str_replace(' ', '_', $user->name)),
                    'profile_photo_path' => $user->profile_photo_path,
                ];
            })
            ->toArray();
    }

    public function updatedCommentBody($value)
    {
        if (preg_match('/@([a-zA-Z0-9_]*)$/', $value, $matches)) {
            $this->showDropdown = true;
        } else {
            $this->showDropdown = false;
        }
    }

    public function selectUser($username)
    {
        $this->commentBody = preg_replace('/@([a-zA-Z0-9_]*)$/', "@$username ", $this->commentBody);
        $this->showDropdown = false;
    }

    public function saveComment()
    {
        $this->validate([
            'commentBody' => 'required|string',
        ]);

        // Extract all mentions from commentBody
        preg_match_all('/@([a-zA-Z0-9_]+)/', $this->commentBody, $matches);

        // Map mentions to user IDs
        $mentionedUserIds = [];
        if (!empty($matches[1])) {
            $mentionedUsernames = $matches[1];
            $mentionedUserIds = collect($this->teamMembers)
                ->whereIn('username', $mentionedUsernames)
                ->pluck('id')
                ->toArray();
        }

        // Log the comment body and mentioned user IDs
        Log::info('Comment Body: ' . $this->commentBody);
        Log::info('Mentioned User IDs: ' . json_encode($mentionedUserIds));

        $this->model->comments()->create([
            'user_id' => auth()->id(),
            'content' => $this->commentBody,
        ]);

        if ($mentionedUserIds && count($mentionedUserIds) > 0) {
            foreach ($mentionedUserIds as $key => $id) {
                if ($id !== Auth::id()) {
                    $title = 'You were mentioned';
                    $body = Auth::user()->name . ' mentioned you in a comment on the task ' . $this->model->name . '.';
                    $user = User::find($id);

                    // Send notification
                    app(NotificationService::class)->sendUserTaskDBNotification($user, $title, $body, $this->model->id);

                    // Prepare email data
                    $mailData = [
                        'email' => $user->email,
                        'email_subject' => 'You were mentioned',
                        'email_body' => Auth::user()->name . ' mentioned you in a comment on the task ' . $this->model->name . '.',
                        'task' => $this->model,
                        'user' => $user,
                        'caused_by' => Auth::user(),
                    ];

                    // Send email
                    Mail::to($user->email)->queue(new NotificationMail($mailData));
                }
            }
        }

        $this->dispatch('commentAdded');
        $this->commentBody = '';
        $this->showDropdown = false;
    }
}; ?>

<div>
    <div class="flex items-center gap-4 mb-6 relative">
        <div class="w-full max-w-md" x-data="{
            dropdownOpen: $wire.entangle('showDropdown').live,
            resize() {
                $el.style.height = '0px';
                $el.style.height = $el.scrollHeight + 'px';
            }
        }">
            <textarea id="commentBody" x-init="resize()" @input="resize();" wire:model.live.debounce.300ms="commentBody"
                placeholder="Type your message here. Use @ to mention a team member."
                class="flex w-full h-auto min-h-[80px] px-3 py-2 text-sm bg-white border rounded-md border-neutral-300 ring-offset-background placeholder:text-neutral-400 focus:border-neutral-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-neutral-400 disabled:cursor-not-allowed disabled:opacity-50">
            </textarea>

            <!-- Mention User Dropdown -->
            <div>
                <div x-show="dropdownOpen" @click.away="dropdownOpen=false" x-transition:enter="ease-out duration-200"
                    x-transition:enter-start="-translate-y-2" x-transition:enter-end="translate-y-0"
                    class="absolute top-0 z-50 w-full -mt-14 bg-white border rounded-md shadow-md border-neutral-200 text-neutral-700 max-w-xs"
                    x-cloak>
                    @foreach ($teamMembers as $member)
                        <div wire:click="selectUser('{{ $member['username'] }}')"
                            class="cursor-pointer px-3 py-2 hover:bg-neutral-100 flex items-center gap-3"
                            onclick="setTimeout(() => document.getElementById('commentBody').focus(), 500);"
                            wire:key="{{ $member['id'] }}">
                            <x-wui-avatar xs warning
                                src="{{ asset('storage/' . $member['profile_photo_path']) ?? asset('assets/images/no-user-image.png') }}" />
                            <span>{{ $member['name'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <x-wui-button xs primary label="Save" wire:click="saveComment" />
        <x-wui-button xs negative label="Cancel" wire:click="$dispatch('commentAdded'); $wire.set('showDropdown', false);" />
    </div>
</div>
