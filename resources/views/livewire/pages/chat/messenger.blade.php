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
            $unread_count = ChatMessage::where('receiver_id', $this->user->id)->where('sender_id', $user->id)->whereNull('seen_at')->count();
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
        $this->dispatch('message-sent');
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
        $this->unread_counts[$message->sender_id] = ($this->unread_counts[$message->sender_id] ?? 0) + 1;
        
        if($this->chat_tab_users->where('id', $message->sender_id)->isEmpty()){
            $this->chat_tab_users->push($message->sender);
        }
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
        if($this->user->id !== $message->sender_id && empty($message->seen_at)){
            $message->seen_at = now();
            $message->save();
            $this->unread_counts[$message->sender_id] = ($this->unread_counts[$message->sender_id] ?? 0) - 1;
        }
    }
}; ?>
<div class="h-full">
    <div class="flex h-full antialiased text-gray-800">
        <div class="flex flex-row w-full m-4 gap-4 overflow-x-hidden">
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
                        <span
                            class="flex items-center justify-center bg-gray-300 h-4 w-4 rounded-full">{{count($chat_tab_users)}}</span>
                    </div>
                    <div class="mt-4">
                        <x-wui-select placeholder="Select some user" :async-data="route('messenger.search-member')"
                            option-label="name" option-value="id" x-on:selected="$wire.loadChat($event.detail.value)" />
                    </div>
                    <div class="flex flex-col space-y-1 mt-4 -mx-2 h-48 overflow-y-auto">
                        @foreach ($chat_tab_users as $chat_tab_user)
                        <button wire:click="loadChat({{$chat_tab_user->id}})"
                            class="flex flex-row items-center hover:bg-gray-100 rounded-xl p-2 border {{$friend_id == $chat_tab_user->id ? 'border-green-300' : ''}}"
                            wire:key="chatroom-{{$chat_tab_user->id}}">
                            <div class="flex items-center justify-center h-8 w-8 bg-indigo-200 rounded-full">
                                <img class="h-8 w-8 rounded-full object-cover"
                                    src="{{ $chat_tab_user->profile_photo_url }}" alt="{{ $chat_tab_user->name }}" />
                            </div>
                            <div class="ml-2 text-sm font-semibold">{{$chat_tab_user->name}}</div>
                            @if (isset($unread_counts[$chat_tab_user->id]) && $unread_counts[$chat_tab_user->id] > 0)
                            <span
                                class="inline-flex items-center justify-center w-6 h-6 ms-5 text-xs font-semibold text-blue-800 bg-blue-200 rounded-full">
                                {{$unread_counts[$chat_tab_user->id]}}
                            </span>
                            @endif
                        </button>
                        @endforeach
                    </div>
                </div>
            </div>
            
            <div class="flex flex-col flex-auto flex-shrink-0 rounded-2xl bg-gray-100 p-4 relative"
                x-data="{ height: 0, messagesEle: document.getElementById('messages'), adjustHeight() {
                    const content = this.$refs.content;
                    content.style.height = `${window.innerHeight - 175}px`;
                    content.scrollTop = content.scrollHeight
                    console.log(content.scrollHeight)

        }}" x-init="
                adjustHeight();
                height = messagesEle.scrollHeight;
                console.log(messagesEle)
                $nextTick(() => messagesEle.scrollTop = height)
            " x-resize="adjustHeight" @message-sent.window="$nextTick(() => messagesEle.scrollTop = height)" >

                <div class=" grid grid-cols-12 overflow-y-scroll pb-10" id="messages" x-ref="content" >
                    @foreach ($messages as $message)
                    @if ($message->sender_id == $user->id)
                    <div class="col-start-6 col-end-13 px-3 py-1 rounded-lg relative" wire:key="message-box-{{$message->id}}">
                        <div class="flex items-center justify-start flex-row-reverse">
                            <img class="h-10 w-10 rounded-full object-cover" src="{{ $user->profile_photo_url }}"
                                alt="{{ $user->name }}" />
                            <div class="relative mr-3 text-sm bg-indigo-100 py-1 px-4 shadow rounded-xl"
                                data-tooltip-target="message-time-{{$message->id}}">
                                <div>{{$message->text}}</div>
                                <span class="text-2xs font-normal text-gray-500 dark:text-gray-400 mr-2">{{date('h:iA',
                                    strtotime($message->created_at))}}</span>
                            </div>
                            <div id="message-time-{{$message->id}}" role="tooltip"
                                class="absolute z-10 text-xs invisible inline-block px-3 py-1 font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                                {{$message->created_at}}
                                <div class="tooltip-arrow" data-popper-arrow></div>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="col-start-1 col-end-8 px-3 py-1 rounded-lg relative"
                        x-intersect.full.once="$wire.messageMarkSeen({{$message}})"
                        wire:key="message-box-{{$message->id}}">
                        <div class="flex flex-row items-center">
                            <img class="h-10 w-10 rounded-full object-cover"
                                src="{{ $message->sender->profile_photo_url }}" alt="{{ $message->sender->name }}" />
                            <div class="relative ml-3 text-sm bg-white py-1 px-4 shadow rounded-xl"
                                data-tooltip-target="message-time-{{$message->id}}">
                                <div>{{$message->text}}</div>
                                <span class="text-2xs font-normal text-gray-500 dark:text-gray-400 mr-2">{{date('h:iA',
                                    strtotime($message->created_at))}}</span>
                            </div>
                            <div id="message-time-{{$message->id}}" role="tooltip"
                                class="absolute z-10 text-xs invisible inline-block px-3 py-1 font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                                {{$message->created_at}}
                                <div class="tooltip-arrow" data-popper-arrow></div>
                            </div>
                        </div>
                    </div>
                    @endif
                    @endforeach
                </div>
                <form wire:submit="sendMessage" class="absolute bottom-3 left-3 right-3 flex flex-row items-center h-16 rounded-xl bg-white w-auto px-4">
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
                        <button type="submit"
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
                </form>
            </div>
        </div>
    </div>

    {{-- <div class="max-w-[1780px] mx-auto mb-10 mt-10 relative px-10 flex flex-col overflow-hidden">
        <div class="flex gap-[15px] px-[30px]">
            <!-- Team Members -->
            <div class="bg-[#F1F1F1] w-[33%] flex flex-col justify-between items-center mt-[20px] p-5 rounded-t-[20px]">
                <h2 class="font-montserrat font-semibold text-[16px] text-[#050914] mb-3">Team Members</h2>
                <div class="flex gap-8">
                    <div class="flex flex-col justify-center items-center gap-2">
                        <img class="w-11 h-11 rounded-full" src="/assets/images/image.png" alt="Hannah Baker">
                        <h3 class="font-montserrat font-semibold text-[12px] text-center">Hannah Baker</h3>
                    </div>
                    <div class="flex flex-col justify-center items-center gap-2">
                        <img class="w-11 h-11 rounded-full" src="/assets/images/image.png" alt="Hannah Baker">
                        <h3 class="font-montserrat font-semibold text-[12px] text-center">Hannah Baker</h3>
                    </div>
                    <div class="flex flex-col justify-center items-center gap-2">
                        <img class="w-11 h-11 rounded-full" src="/assets/images/image.png" alt="Hannah Baker">
                        <h3 class="font-montserrat font-semibold text-[12px] text-center">Hannah Baker</h3>
                    </div>
                </div>
                <h4 class="mt-3">+ New team chat</h4>
            </div>
    
            <!-- Group Members -->
            <div class="bg-[#FBFBFB] w-[33%] flex flex-col justify-between items-center mt-[20px] p-5 rounded-t-[20px]">
                <h2 class="font-montserrat font-semibold text-[16px] text-[#050914] mb-3">Group Members</h2>
                <div class="flex gap-4 overflow-x-auto">
                    <div class="w-1/3 flex gap-8">
                        <img class="w-11 h-11 rounded-full" src="/assets/images/image.png" alt="Group Member">
                        <img class="w-11 h-11 rounded-full" src="/assets/images/image.png" alt="Group Member">
                        <img class="w-11 h-11 rounded-full" src="/assets/images/image.png" alt="Group Member">
                    </div>
                </div>
                <h4 class="mt-3">+ New Group chat</h4>
            </div>
    
            <!-- Projects -->
            <div class="bg-[#FBFBFB] w-[33%] flex flex-col justify-center mt-[20px] p-5 rounded-t-[20px]">
                <h2 class="font-montserrat font-semibold text-[16px] text-[#050914] mb-3 flex justify-center w-full self-start">Projects</h2>
                <div class="flex gap-4 justify-center self-center">
                    <img class="w-11 h-11 rounded-full" src="/assets/images/logo_circle.png" alt="Project Logo">
                    <img class="w-11 h-11 rounded-full" src="/assets/images/logo_circle.png" alt="Project Logo">
                    <img class="w-11 h-11 rounded-full" src="/assets/images/logo_circle.png" alt="Project Logo">
                </div>
            </div>
        </div>
    
        <div class="px-[30px]">
            <div class="bg-[#E7E7E7] px-[64px] py-[16px] flex justify-between">
                <div class="flex gap-5">
                    <img class="w-12 h-12 rounded-full" src="/assets/images/logo_circle.png" alt="Profile">
                    <h3 class="font-montserrat font-semibold text-[18px] text-center flex items-center text-[#050914]">Hannah Baker</h3>
                </div>
                <div class="w-[50%]">
                    <input type="text" placeholder="Search" class="w-full rounded-lg border-gray-300 p-2">
                </div>
                <div class="flex gap-5">
                    <button>
                        <svg width="30" height="30" viewBox="0 0 34 34" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M24.224 1.58362H9.77565C4.74065 1.58362 1.58398 5.14862 1.58398 10.1936V23.807C1.58398 28.852 4.72565 32.417 9.77565 32.417H24.2223C29.274 32.417 32.4173 28.852 32.4173 23.807V10.1936C32.4173 5.14862 29.274 1.58362 24.224 1.58362Z" stroke="#050914" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M16.9912 23.6668V17.0001" stroke="#050914" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M16.9844 10.6739H17.001" stroke="#050914" stroke-width="3.33333" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    <button>
                        <svg width="30" height="30" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M17.216 27.6549H6.71484" stroke="#050914" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M21.9004 11.5007H32.4016" stroke="#050914" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
            </div>
            <!-- Add other sections here -->
        </div>

        <div class="px-[30px]">
            <div class="bg-[#E7E7E7] px-[64px] py-[16px] flex justify-between">
                <div class="flex gap-5">
                    <n-avatar round :size="50" src="/assets/images/logo_circle.png"/>
                    <h3 class="montserrat font-semibold text-[18px] text-center flex items-center text-[#050914]">Hannah Baker</h3>
                </div>
                <div class="w-[50%]">
                    <!-- Search input -->
                    <n-input round placeholder="Search" size="large">
                        <template #suffix>
                            <button class="flex items-center"><n-icon size="20"><search-icon /></n-icon></button>
                        </template>
                    </n-input>
                    <!-- Search input -->
                </div>
                <div class="flex gap-5">
                    <button>
                        <svg width="30" height="30" viewBox="0 0 34 34" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M24.224 1.58362H9.77565C4.74065 1.58362 1.58398 5.14862 1.58398 10.1936V23.807C1.58398 28.852 4.72565 32.417 9.77565 32.417H24.2223C29.274 32.417 32.4173 28.852 32.4173 23.807V10.1936C32.4173 5.14862 29.274 1.58362 24.224 1.58362Z" stroke="#050914" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M16.9912 23.6668V17.0001" stroke="#050914" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M16.9844 10.6739H17.001" stroke="#050914" stroke-width="3.33333" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>

                    <button><svg width="30" height="30" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M17.216 27.6549H6.71484" stroke="#050914" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M21.9004 11.5007H32.4016" stroke="#050914" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/><path fill-rule="evenodd" clip-rule="evenodd" d="M14.5432 11.4104C14.5432 9.251 12.7796 7.5 10.6046 7.5C8.42962 7.5 6.66602 9.251 6.66602 11.4104C6.66602 13.5698 8.42962 15.3208 10.6046 15.3208C12.7796 15.3208 14.5432 13.5698 14.5432 11.4104Z" stroke="#050914" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/><path fill-rule="evenodd" clip-rule="evenodd" d="M33.3322 27.5896C33.3322 25.4302 31.57 23.6792 29.395 23.6792C27.2187 23.6792 25.4551 25.4302 25.4551 27.5896C25.4551 29.749 27.2187 31.5 29.395 31.5C31.57 31.5 33.3322 29.749 33.3322 27.5896Z" stroke="#050914" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
                </div>
            </div>

            <div class="w-full flex">
                <div class="w-[40%]">
                    <ul class="max-w-md divide-y divide-gray-200 dark:divide-gray-700">
                        <li class="px-3 py-2">
                            <div class="flex items-center space-x-4 rtl:space-x-reverse">
                                <div class="flex-shrink-0">
                                    <img class="w-8 h-8 rounded-full"
                                        src="https://flowbite.com/docs/images/people/profile-picture-1.jpg"
                                        alt="Neil image">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate dark:text-white">
                                        Neil Sims
                                    </p>
                                    <p class="text-sm text-gray-500 truncate dark:text-gray-400">
                                        email@flowbite.com
                                    </p>
                                </div>

                            </div>
                        </li>
                        <li class="px-3 py-2">
                            <div class="flex items-center space-x-4 rtl:space-x-reverse">
                                <div class="flex-shrink-0">
                                    <img class="w-8 h-8 rounded-full"
                                        src="https://flowbite.com/docs/images/people/profile-picture-2.jpg"
                                        alt="Neil image">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate dark:text-white">
                                        Neil Sims
                                    </p>
                                    <p class="text-sm text-gray-500 truncate dark:text-gray-400">
                                        email@flowbite.com
                                    </p>
                                </div>

                            </div>
                        </li>
                        <li class="px-3 py-2">
                            <div class="flex items-center space-x-4 rtl:space-x-reverse">
                                <div class="flex-shrink-0">
                                    <img class="w-8 h-8 rounded-full"
                                        src="https://flowbite.com/docs/images/people/profile-picture-3.jpg"
                                        alt="Neil image">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate dark:text-white">
                                        Neil Sims
                                    </p>
                                    <p class="text-sm text-gray-500 truncate dark:text-gray-400">
                                        email@flowbite.com
                                    </p>
                                </div>

                            </div>
                        </li>
                        <li class="px-3 py-2">
                            <div class="flex items-center space-x-4 rtl:space-x-reverse">
                                <div class="flex-shrink-0">
                                    <img class="w-8 h-8 rounded-full"
                                        src="https://flowbite.com/docs/images/people/profile-picture-4.jpg"
                                        alt="Neil image">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate dark:text-white">
                                        Neil Sims
                                    </p>
                                    <p class="text-sm text-gray-500 truncate dark:text-gray-400">
                                        email@flowbite.com
                                    </p>
                                </div>

                            </div>
                        </li>
                        <li class="px-3 py-2">
                            <div class="flex items-center space-x-4 rtl:space-x-reverse">
                                <div class="flex-shrink-0">
                                    <img class="w-8 h-8 rounded-full"
                                        src="https://flowbite.com/docs/images/people/profile-picture-5.jpg"
                                        alt="Neil image">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate dark:text-white">
                                        Neil Sims
                                    </p>
                                    <p class="text-sm text-gray-500 truncate dark:text-gray-400">
                                        email@flowbite.com
                                    </p>
                                    <p class="text-sm text-gray-500 truncate dark:text-gray-400">
                                        Typing ......
                                    </p>
                                </div>
                                <div>
                                    <div class="online-indicator">
                                        <svg fill="#2eb82e" width="12" height="12" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512z"/></svg>
                                    </div>
                                    <!-- <div class="offline-indicator">
                                        <svg fill="#ff0000" width="12" height="12" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512z"/></svg>
                                    </div>
                                    <div class="away-indicator">
                                        <svg fill="#ff9933" width="12" height="12" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512z"/></svg>
                                    </div> -->
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>

                <div class="w-[60%]">
                    <div class="bg-[#F1F1F1] w-full h-full px-16 py-8 flex-col justify-end items-center gap-2.5 inline-flex rounded-bl-[20px] rounded-br-[20px] max-h-[400px]">
                        <div class="overflow-y-auto overflow-x-hidden flex flex-col items-center w-full">
                            <div class="text-gray-400 text-sm font-medium montserrat break-words">12:00 am Sep 12, 2023</div>
                            <div class="self-stretch h-13 flex-col justify-center items-end gap-2.5 flex">
                                <div
                                    class="px-[18px] py-[12px] bg-blue-600 rounded-tl-[24px] rounded-tr-[24px] rounded-bl-[24px] overflow-hidden justify-center items-end gap-2.5 inline-flex">
                                    <h3 class="text-white text-sm font-medium montserrat break-words">Hi</h3>
                                </div>
                            </div>
                            <div class="text-gray-400 text-sm font-medium montserrat break-words">09:00 am Today</div>
                            <div class="self-stretch h-33.5 flex-col justify-center items-start gap-2.5 flex">
                                <div
                                    class="px-[18px] py-[12px] bg-white rounded-tl-[24px] rounded-t-[24px] rounded-br-[24px] overflow-hidden justify-center items-end gap-2.5 inline-flex">
                                    <div class="text-gray-900 text-sm font-medium montserrat break-words">Hi!</div>
                                </div>
                                <div
                                    class="px-[18px] py-[12px] bg-white rounded-tl-[24px] rounded-tr-[24px] rounded-br-[24px] overflow-hidden justify-center items-end gap-2.5 inline-flex">
                                    <div class="text-gray-900 text-sm font-medium montserrat break-words">I am coming
                                        there
                                        in
                                        few minutes. Please Wait!!<br />I am in taxy right now. </div>
                                </div>
                            </div>
                            <div class="self-stretch h-13 flex-col justify-center items-end gap-2.5 flex">
                                <div
                                    class="px-[18px] py-[12px] bg-blue-600 rounded-tl-[24px] rounded-tr-[24px] rounded-bl-[24px] overflow-hidden justify-center items-end gap-2.5 inline-flex">
                                    <div class="text-white text-sm font-medium montserrat break-words">Thanks for the
                                        update.
                                        Looking forward to it.</div>
                                </div>
                            </div>
                        </div>
                        <div class="w-full px-[18px] py-[12px] justify-start items-start gap-4 flex">
                            <n-input v-model:value="value" type="input" placeholder="Type a message" class="!rounded-[12px]" />
                            <n-button strong secondary circle type="success">
                                <template #icon>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M498.1 5.6c10.1 7 15.4 19.1 13.5 31.2l-64 416c-1.5 9.7-7.4 18.2-16 23s-18.9 5.4-28 1.6L284 427.7l-68.5 74.1c-8.9 9.7-22.9 12.9-35.2 8.1S160 493.2 160 480V396.4c0-4 1.5-7.8 4.2-10.7L331.8 202.8c5.8-6.3 5.6-16-.4-22s-15.7-6.4-22-.7L106 360.8 17.7 316.6C7.1 311.3 .3 300.7 0 288.9s5.9-22.8 16.1-28.7l448-256c10.7-6.1 23.9-5.5 34 1.4z"/></svg>
                                </template>
                            </n-button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div> --}}
    

    <script>
        // document.addEventListener('alpine:initialized', () => {
//     console.log('isksks')
//     var root = document.documentElement;
//             root.className += ' !overflow-y-hidden';
//         });
        // const el = document.getElementById('messages')
        // el.scrollTop = el.scrollHeight
        // console.log(el.scrollTop)
        // function setHeight() {
        //     return {
        //         adjustHeight() {
        //             const content = this.$refs.content;
        //             content.style.height = `${window.innerHeight - 200}px`;
        //             content.scrollTop = content.scrollHeight
        //             console.log(content.scrollHeight)

        //         }
        //     };
        // }
    </script>
</div>