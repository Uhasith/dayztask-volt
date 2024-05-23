 <div>
     <x-mary-button icon="o-bell" class="btn-circle btn-sm relative">
         @if ($unreadNotificationsCount > 0)
             <x-mary-badge value="{{ $unreadNotificationsCount }}"
                 class="badge-error badge-sm absolute -right-2 -top-2" />
         @endif
     </x-mary-button>

     <audio id="notification-sound" src="{{ asset('assets/sounds/notification.mp3') }}"></audio>

 </div>
