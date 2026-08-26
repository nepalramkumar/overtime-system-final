<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-end h-20 items-center">

            <div class="flex items-center gap-1 sm:gap-3">
                @auth
                <!-- Notification Bell -->
                <x-dropdown align="right" width="w-96" content-classes="bg-white dark:bg-gray-700">
                    <x-slot name="trigger">
                        <button class="relative inline-flex items-center p-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                            <i class="fas fa-bell text-xl"></i>
                            @if(auth()->user()->unreadNotifications->count() > 0)
                                <span class="absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-bold leading-none text-white bg-rose-600 rounded-full ring-2 ring-white">
                                    {{ auth()->user()->unreadNotifications->count() }}
                                </span>
                            @endif
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="max-h-[26rem] overflow-y-auto">
                            <div class="px-4 py-3 flex items-center justify-between border-b border-slate-100 sticky top-0 bg-white z-10">
                                <span class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                                    <i class="fas fa-bell text-blue-600"></i> Notifications
                                </span>
                                @if(auth()->user()->unreadNotifications->count() > 0)
                                    <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                                        @csrf
                                        <button type="submit" class="text-[11px] font-medium text-blue-600 hover:text-blue-700 hover:underline">सबै Read गर्नुहोस्</button>
                                    </form>
                                @endif
                            </div>
                            <div class="divide-y divide-slate-100">
                                @forelse(auth()->user()->notifications->take(3) as $notif)
                                    <x-notification-item :notif="$notif" :compact="true" />
                                @empty
                                    <div class="px-4 py-10 text-center">
                                        <i class="fas fa-bell-slash text-2xl text-slate-200 mb-2 block"></i>
                                        <p class="text-xs text-slate-400">कुनै Notification छैन</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                        <a href="{{ route('notifications.index') }}"
                           class="block text-center text-xs font-semibold text-blue-600 hover:text-blue-700 hover:bg-blue-50 py-3 border-t border-slate-100 transition">
                            सबै Notification हेर्नुहोस् <i class="fas fa-arrow-right text-[10px] ml-1"></i>
                        </a>
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