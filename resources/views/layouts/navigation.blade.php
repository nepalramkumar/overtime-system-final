<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-end h-20 items-center">

            <div class="flex items-center gap-1 sm:gap-3">
                @auth
                <!-- Notification Bell -->
                <x-dropdown align="right" width="w-80">
                    <x-slot name="trigger">
                        <button class="relative inline-flex items-center p-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                            <i class="fas fa-bell text-xl"></i>
                            @if(auth()->user()->unreadNotifications->count() > 0)
                                <span class="absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-bold leading-none text-white bg-rose-600 rounded-full">
                                    {{ auth()->user()->unreadNotifications->count() }}
                                </span>
                            @endif
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="max-h-96 overflow-y-auto">
                            <div class="px-4 py-2 flex items-center justify-between border-b border-gray-100 dark:border-gray-700">
                                <span class="text-xs font-semibold text-gray-500 uppercase">Notifications</span>
                                @if(auth()->user()->unreadNotifications->count() > 0)
                                    <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                                        @csrf
                                        <button type="submit" class="text-[11px] text-blue-600 hover:underline">सबै Read गर्नुहोस्</button>
                                    </form>
                                @endif
                            </div>
                            @forelse(auth()->user()->notifications->take(10) as $notif)
                                <form method="POST" action="{{ route('notifications.read', $notif->id) }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left block px-4 py-2.5 text-sm border-b border-gray-50 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 {{ is_null($notif->read_at) ? 'bg-blue-50 dark:bg-gray-900' : 'opacity-70' }}">
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium {{ is_null($notif->read_at) ? 'text-gray-900 dark:text-gray-100 font-bold' : 'text-gray-500 dark:text-gray-400' }}">{{ $notif->data['title'] ?? '' }}</span>
                                            @if(is_null($notif->read_at))
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[9px] font-bold bg-blue-600 text-white">नयाँ</span>
                                            @else
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[9px] font-medium bg-gray-200 dark:bg-gray-600 text-gray-500">हेरिसकियो</span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $notif->data['message'] ?? '' }}</div>
                                        <div class="text-[10px] text-gray-400 mt-1">{{ $notif->created_at->format('Y-m-d h:i A') }} &middot; {{ $notif->created_at->diffForHumans() }}</div>
                                    </button>
                                </form>
                            @empty
                                <div class="px-4 py-6 text-center text-xs text-gray-400">कुनै Notification छैन</div>
                            @endforelse
                        </div>
                    </x-slot>
                </x-dropdown>

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-2 sm:px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                            <div class="truncate max-w-[90px] sm:max-w-none">{{ Auth::user()->name }}</div>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
                @else
                    <a href="{{ route('login') }}" class="text-sm text-gray-700 dark:text-gray-300">Log in</a>
                @endauth
            </div>
        </div>
    </div>
</nav>