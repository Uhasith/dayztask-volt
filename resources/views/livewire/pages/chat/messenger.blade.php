<?php

use Livewire\Volt\Component;
use App\Models\ChatMessage;
use App\Models\User;
use App\Events\MessageSent;
use Livewire\Attributes\On; 
use Illuminate\Support\Facades\Log;

new class extends Component {
    public $message = '', $messages, $chat_tab_users, $unread_counts = [];
    public User $user;
    public $friend_id;
    
    function mount() : void {
        $this->messages = collect();
        $this->user = auth()->user();
        $this->chat_tab_users = ChatMessage::where('sender_id', $this->user->id)->orWhere('receiver_id', $this->user->id)->get()->map(function($chat){
            return $chat->sender_id == $this->user->id ? $chat->receiver : $chat->sender;
        })->reject(function($chat_tab_user){
            return $chat_tab_user->id == $this->user->id && $chat_tab_user->belongsToTeam($this->user->currentTeam);
        })->unique()->each(function($user){
            $unread_count = ChatMessage::where('receiver_id', $this->user->id)->where('sender_id', $user->id)->where('seen', 0)->count();
            $this->unread_counts[$user->id] = $unread_count ?? 0;
        });

        if(!$this->chat_tab_users->isEmpty()){
            $this->loadChat($this->chat_tab_users->first()->id);
        }
    }

    public function getListeners()
    {
        return [
            "echo-private:chat.{$this->user->id},MessageSent" => 'chatReceived',
        ];
    }
    
    function sendMessage() : void {
        $message = ChatMessage::create([
            'receiver_id' => $this->friend_id,
            'sender_id' => auth()->user()->id,
            'group_id' => 0,
            'text' => $this->message,
            'attachments' =>  null
        ]);

        $this->messages->push($message);
        broadcast(new MessageSent($message));

        $this->reset('message');
    }

    function chatReceived(ChatMessage $message) : void {
        if(empty($this->friend_id)){ //if no chatbox is selected, auto jump to the incoming chat - this is happening only when fresh chats are being receive
            $this->loadChat($message->sender_id);
        }elseif(!$this->messages->where('sender_id', $message->sender_id)->isEmpty()){ //push only if the current chat box is with the sender
            $this->messages->push($message);
        }else{
            $this->dispatch('play-notification-sound', sound: asset('assets/sounds/notification.mp3'));
        }
        $this->unread_counts[$message->sender_id] = $this->unread_counts[$message->sender_id] + 1;
        // $this->chat_tab_users->where('id', $message->sender_id)->first()->unread = $this->chat_tab_users->where('id', $message->sender_id)->first()->unread + 1;
    }

    function loadChat($user_id) : void {
        $friend_id = $user_id;
        $this->friend_id = $user_id;
        $this->messages = ChatMessage::query()->where(function ($query) use ($friend_id) {
            $query->where('sender_id', auth()->id())->where('receiver_id', $friend_id);
        })->orWhere(function ($query) use ($friend_id) {
            $query->where('sender_id', $friend_id)
                ->where('receiver_id', auth()->id());
        })->with(['sender', 'receiver'])->orderBy('id', 'asc')->get();

        if($this->chat_tab_users->where('id', $user_id)->isEmpty()){
            $this->chat_tab_users->push(User::find($user_id));
        }
    }

    function messageMarkSeen(ChatMessage $message) : void {
        if($this->user->id !== $message->sender_id && !$message->seen){
            $message->seen = 1;
            $message->save();
            $this->unread_counts[$message->sender_id] = $this->unread_counts[$message->sender_id] - 1;
        }
    }
}; ?>
<div class="overflow-y-hidden max-h-screen">
    <div class="flex antialiased text-gray-800">
        <div class="flex flex-row w-full overflow-x-hidden">
            <div class="flex flex-col py-8 pl-6 pr-2 w-64 bg-white flex-shrink-0">
                <div class="flex flex-row items-center justify-center h-12 w-full">
                    <div class="flex items-center justify-center rounded-2xl text-indigo-700 bg-indigo-100 h-10 w-10">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z">
                            </path>
                        </svg>
                    </div>
                    <div class="ml-2 font-bold text-2xl">Dayz Messenger</div>
                </div>
                <div
                    class="flex flex-col items-center bg-indigo-100 border border-gray-200 mt-4 w-full py-6 px-4 rounded-lg">
                    <div class="h-20 w-20 rounded-full border overflow-hidden">
                        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                        <button
                            class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition">
                            <img class="w-full rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}"
                                alt="{{ Auth::user()->name }}" />
                        </button>
                        @else
                        <span class="inline-flex rounded-md">
                            <button type="button"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none focus:bg-gray-50 dark:focus:bg-gray-700 active:bg-gray-50 dark:active:bg-gray-700 transition ease-in-out duration-150">
                                {{ Auth::user()->name }}

                                <svg class="ms-2 -me-0.5 w-full" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                            </button>
                        </span>
                        @endif
                    </div>
                    <div class="text-sm font-semibold mt-2">{{auth()->user()->name}}</div>
                    <div class="text-xs text-gray-500">Lead UI/UX Designer</div>
                    <div class="flex flex-row items-center mt-3">
                        <div class="flex flex-col justify-center h-4 w-8 bg-indigo-500 rounded-full">
                            <div class="h-3 w-3 bg-white rounded-full self-end mr-1"></div>
                        </div>
                        <div class="leading-none ml-1 text-xs">Active</div>
                    </div>
                </div>
                <div class="flex flex-col mt-8">
                    <div class="flex flex-row items-center justify-between text-xs">
                        <span class="font-bold">Active Conversations</span>
                        <span class="flex items-center justify-center bg-gray-300 h-4 w-4 rounded-full">{{count($chat_tab_users)}}</span>
                    </div>
                    <div class="mt-4">
                        <x-wui-select
                            placeholder="Select some user"
                            :async-data="route('messenger.search-member')"
                            option-label="name"
                            option-value="id"
                            x-on:selected="$wire.loadChat($event.detail.value)"
                        />
                    </div>
                    <div class="flex flex-col space-y-1 mt-4 -mx-2 h-48 overflow-y-auto">
                        @foreach ($chat_tab_users as $chat_tab_user)
                            <button wire:click="loadChat({{$chat_tab_user->id}})" class="flex flex-row items-center hover:bg-gray-100 rounded-xl p-2 border {{$friend_id == $chat_tab_user->id ? 'border-green-300' : ''}}" wire:key="chatroom-{{$chat_tab_user->id}}">
                                <div class="flex items-center justify-center h-8 w-8 bg-indigo-200 rounded-full">
                                    <img class="h-8 w-8 rounded-full object-cover"
                                        src="{{ $chat_tab_user->profile_photo_url }}" alt="{{ $chat_tab_user->name }}" />
                                </div>
                                <div class="ml-2 text-sm font-semibold">{{$chat_tab_user->name}}</div>
                                @if ($unread_counts[$chat_tab_user->id] > 0)
                                    <span class="inline-flex items-center justify-center w-6 h-6 ms-5 text-xs font-semibold text-blue-800 bg-blue-200 rounded-full">
                                        {{$unread_counts[$chat_tab_user->id]}}
                                    </span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="flex flex-col flex-auto p-6">
                <div class="flex flex-col flex-auto flex-shrink-0 rounded-2xl bg-gray-100 p-4">
                    @persist('scrollbar')

                    <div class="flex flex-col overflow-x-auto mb-4" > <!--x-data="{ scroll: () => { $el.scrollTo(0, $el.scrollHeight); }}" x-init="scroll()"-->
                        <div class="flex flex-col" x-data="setHeight()" x-init="adjustHeight" x-resize="adjustHeight">
                            <div class="grid grid-cols-12" x-ref="content">
                                @foreach ($messages as $message)
                                @if ($message->sender_id == $user->id)
                                <div class="col-start-6 col-end-13 px-3 py-1 rounded-lg" wire:key="message-box-{{$message->id}}">
                                    <div class="flex items-center justify-start flex-row-reverse">
                                        <img class="h-10 w-10 rounded-full object-cover" src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" />
                                        <div class="relative mr-3 text-sm bg-indigo-100 py-1 px-4 shadow rounded-xl" data-tooltip-target="message-time-{{$message->id}}">
                                            <div>{{$message->text}}</div>
                                        </div>
                                        <div id="message-time-{{$message->id}}" role="tooltip" class="absolute z-10 text-xs invisible inline-block px-3 py-1 font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                                            {{$message->created_at}}
                                            <div class="tooltip-arrow" data-popper-arrow></div>
                                        </div>
                                    </div>
                                </div>
                                @else
                                <div class="col-start-1 col-end-8 px-3 py-1 rounded-lg" x-intersect.full.once="$wire.messageMarkSeen({{$message}})" wire:key="message-box-{{$message->id}}">
                                    <div class="flex flex-row items-center">
                                        <img class="h-10 w-10 rounded-full object-cover" src="{{ $message->sender->profile_photo_url }}" alt="{{ $message->sender->name }}" />
                                        <div class="relative ml-3 text-sm bg-white py-1 px-4 shadow rounded-xl" data-tooltip-target="message-time-{{$message->id}}">
                                            <div>{{$message->text}}</div>
                                        </div>
                                        <div id="message-time-{{$message->id}}" role="tooltip" class="absolute z-10 text-xs invisible inline-block px-3 py-1 font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                                            {{$message->created_at}}
                                            <div class="tooltip-arrow" data-popper-arrow></div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-row items-center h-16 rounded-xl bg-white w-full px-4">
                        <div>
                            <button class="flex items-center justify-center text-gray-400 hover:text-gray-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13">
                                    </path>
                                </svg>
                            </button>
                        </div>
                        <div class="flex-grow ml-4">
                            <div class="relative w-full">
                                <input type="text" wire:model="message"
                                    class="flex w-full border rounded-xl focus:outline-none focus:border-indigo-300 pl-4 h-10" />
                                <button
                                    class="absolute flex items-center justify-center h-full w-12 right-0 top-0 text-gray-400 hover:text-gray-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                        </path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="ml-4">
                            <button wire:click="sendMessage"
                                class="flex items-center justify-center bg-indigo-500 hover:bg-indigo-600 rounded-xl text-white px-4 py-1 flex-shrink-0">
                                <span>Send</span>
                                <span class="ml-2">
                                    <svg class="w-4 h-4 transform rotate-45 -mt-px" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                    </svg>
                                </span>
                            </button>
                        </div>
                    </div>
                    @endpersist

                </div>
            </div>
        </div>
    </div>
    <script>
        function setHeight() {
            return {
                adjustHeight() {
                    const content = this.$refs.content;
                    content.style.height = `${window.innerHeight - 200}px`;
                }
            };
        }
    </script>
</div>