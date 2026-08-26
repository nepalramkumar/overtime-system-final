<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Notifications') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="w-full sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-slate-100">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                        <i class="fas fa-bell text-blue-600"></i>
                        सबै Notifications
                        @if(auth()->user()->unreadNotifications->count() > 0)
                            <span class="inline-flex items-center justify-center px-2 py-0.5 text-[11px] font-bold text-white bg-rose-600 rounded-full">
                                {{ auth()->user()->unreadNotifications->count() }} नयाँ
                            </span>
                        @endif
                    </h3>
                    @if(auth()->user()->unreadNotifications->count() > 0)
                        <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                            @csrf
                            <button type="submit" class="text-xs font-medium text-blue-600 hover:text-blue-700 hover:underline">
                                सबै Read गर्नुहोस्
                            </button>
                        </form>
                    @endif
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse($notifications as $notif)
                        <x-notification-item :notif="$notif" />
                    @empty
                        <div class="px-6 py-16 text-center">
                            <i class="fas fa-bell-slash text-3xl text-slate-200 mb-3 block"></i>
                            <p class="text-sm text-slate-400">कुनै Notification छैन</p>
                        </div>
                    @endforelse
                </div>

                @if($notifications->hasPages())
                    <div class="px-5 py-4 border-t border-slate-100">
                        {{ $notifications->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>