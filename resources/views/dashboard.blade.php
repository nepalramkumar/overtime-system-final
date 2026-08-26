<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="w-full sm:px-6 lg:px-8 space-y-6">

            {{-- OT/Event Notifications Panel --}}
            @php $notifications = auth()->user()->notifications->take(1); @endphp
            @if($notifications->count() > 0)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-xl border border-slate-100 dark:border-gray-700">
                <div class="px-5 py-4 border-b border-slate-100 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="font-bold text-slate-800 dark:text-gray-200 text-sm flex items-center gap-2">
                        <i class="fas fa-bell text-blue-600"></i>
                        Notifications
                        @if(auth()->user()->unreadNotifications->count() > 0)
                            <span class="inline-flex items-center justify-center px-2 py-0.5 text-[11px] font-bold text-white bg-rose-600 rounded-full">
                                {{ auth()->user()->unreadNotifications->count() }} नयाँ
                            </span>
                        @endif
                    </h3>
                    <div class="flex items-center gap-3">
                        @if(auth()->user()->unreadNotifications->count() > 0)
                            <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                                @csrf
                                <button type="submit" class="text-xs font-medium text-blue-600 hover:text-blue-700 hover:underline">सबै Read गर्नुहोस्</button>
                            </form>
                        @endif
                        <a href="{{ route('notifications.index') }}" class="text-xs font-semibold text-slate-500 dark:text-gray-400 hover:text-slate-700 dark:hover:text-gray-200">
                            सबै हेर्नुहोस् <i class="fas fa-arrow-right text-[10px] ml-0.5"></i>
                        </a>
                    </div>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-gray-700">
                    @foreach($notifications as $notif)
                        {{-- यदि तपाईं <x-notification-item> कम्पोनेन्ट प्रयोग गर्न चाहनुहुन्छ भने यो राख्नुहोस् --}}
                        @if(View::exists('components.notification-item'))
                            <x-notification-item :notif="$notif" />
                        @else
                            {{-- Fallback यदि कम्पोनेन्ट बनेको छैन भने Direct HTML --}}
                            <form method="POST" action="{{ route('notifications.read', $notif->id) }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-5 py-3.5 flex items-start gap-3 hover:bg-slate-50 dark:hover:bg-gray-700/50 transition {{ is_null($notif->read_at) ? 'bg-blue-50/60 dark:bg-gray-900/40' : 'opacity-70' }}">
                                    <span class="mt-1 w-2 h-2 rounded-full flex-shrink-0 {{ is_null($notif->read_at) ? 'bg-blue-500' : 'bg-gray-300 dark:bg-gray-600' }}"></span>
                                    <span class="flex-1">
                                        <span class="flex items-center gap-2">
                                            <span class="block text-sm {{ is_null($notif->read_at) ? 'font-bold text-slate-900 dark:text-white' : 'font-medium text-slate-600 dark:text-gray-400' }}">{{ $notif->data['title'] ?? '' }}</span>
                                            @if(is_null($notif->read_at))
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-600 text-white">नयाँ</span>
                                            @endif
                                        </span>
                                        <span class="block text-xs {{ is_null($notif->read_at) ? 'text-slate-700 dark:text-gray-300' : 'text-slate-400 dark:text-gray-500' }} mt-0.5">{{ $notif->data['message'] ?? '' }}</span>
                                        <span class="block text-[11px] text-slate-400 dark:text-gray-500 mt-1">
                                            {{ $notif->created_at->format('Y-m-d h:i A') }} &middot; {{ $notif->created_at->diffForHumans() }}
                                        </span>
                                    </span>
                                </button>
                            </form>
                        @endif
                    @endforeach
                </div>
            </div>
            @endif

            @if($isAdmin && $overall)
            {{-- ================= Admin: Overall (सबैको) Summary ================= --}}
            <div>
                <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-3">Overall — यस महिना</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <a href="{{ route('overtime.pending') }}" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4 hover:shadow-md hover:border-blue-200 transition">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">सिफारिस बाँकी OT</span>
                            <i class="fas fa-hourglass-half text-amber-500"></i>
                        </div>
                        <div class="text-2xl font-bold text-gray-800 dark:text-gray-100 mt-1">{{ $overall['ot_pending_recommend'] }}</div>
                    </a>
                    <a href="{{ route('overtime.recommended') }}" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4 hover:shadow-md hover:border-blue-200 transition">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">स्वीकृति बाँकी OT</span>
                            <i class="fas fa-user-check text-blue-500"></i>
                        </div>
                        <div class="text-2xl font-bold text-gray-800 dark:text-gray-100 mt-1">{{ $overall['ot_pending_approve'] }}</div>
                    </a>
                    <a href="{{ route('reports.index') }}" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4 hover:shadow-md hover:border-blue-200 transition">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Verified OT घण्टा</span>
                            <i class="fas fa-check-circle text-emerald-500"></i>
                        </div>
                        <div class="text-2xl font-bold text-gray-800 dark:text-gray-100 mt-1">{{ number_format($overall['ot_hours_month'], 1) }}</div>
                        <div class="text-[11px] text-gray-400">{{ $overall['ot_verified_month'] }} रेकर्ड</div>
                    </a>
                    <a href="{{ route('events.list') }}" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4 hover:shadow-md hover:border-blue-200 transition">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">सक्रिय Events</span>
                            <i class="fas fa-calendar-check text-blue-500"></i>
                        </div>
                        <div class="text-2xl font-bold text-gray-800 dark:text-gray-100 mt-1">{{ $overall['events_active'] }}</div>
                    </a>
                    <a href="{{ route('petrol.bills.index') }}" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4 hover:shadow-md hover:border-blue-200 transition">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Petrol खर्च</span>
                            <i class="fas fa-gas-pump text-orange-500"></i>
                        </div>
                        <div class="text-2xl font-bold text-gray-800 dark:text-gray-100 mt-1">रु. {{ number_format($overall['petrol_month_total'], 0) }}</div>
                        <div class="text-[11px] text-gray-400">{{ $overall['petrol_count_month'] }} बिल</div>
                    </a>
                    <a href="{{ route('repair.expenses.index') }}" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4 hover:shadow-md hover:border-blue-200 transition">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Repair खर्च</span>
                            <i class="fas fa-wrench text-orange-500"></i>
                        </div>
                        <div class="text-2xl font-bold text-gray-800 dark:text-gray-100 mt-1">रु. {{ number_format($overall['repair_month_total'], 0) }}</div>
                        <div class="text-[11px] text-gray-400">{{ $overall['repair_count_month'] }} entry</div>
                    </a>
                </div>
            </div>
            @endif

            {{-- ================= मेरो (Personal) Summary — सबैलाई ================= --}}
            @if($myOt)
            <div>
                <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-3">मेरो विवरण</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <a href="{{ route('overtime.my') }}" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4 hover:shadow-md hover:border-blue-200 transition">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">यस महिना OT घण्टा</span>
                            <i class="fas fa-clock text-blue-500"></i>
                        </div>
                        <div class="text-2xl font-bold text-gray-800 dark:text-gray-100 mt-1">{{ number_format($myOt['total_hours_month'], 1) }}</div>
                    </a>
                    <a href="{{ route('overtime.my') }}" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4 hover:shadow-md hover:border-blue-200 transition">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">बाँकी (Pending) OT</span>
                            <i class="fas fa-hourglass-half text-amber-500"></i>
                        </div>
                        <div class="text-2xl font-bold text-gray-800 dark:text-gray-100 mt-1">{{ $myOt['pending'] }}</div>
                    </a>
                    <a href="{{ route('overtime.my') }}" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4 hover:shadow-md hover:border-blue-200 transition">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Verified OT</span>
                            <i class="fas fa-check-circle text-emerald-500"></i>
                        </div>
                        <div class="text-2xl font-bold text-gray-800 dark:text-gray-100 mt-1">{{ $myOt['verified'] }}</div>
                    </a>
                    <a href="{{ route('overtime.my') }}" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4 hover:shadow-md hover:border-blue-200 transition">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Rejected OT</span>
                            <i class="fas fa-times-circle text-rose-500"></i>
                        </div>
                        <div class="text-2xl font-bold text-gray-800 dark:text-gray-100 mt-1">{{ $myOt['rejected'] }}</div>
                    </a>
                </div>
            </div>
            @endif

            {{-- Petrol / Repair (मेरो) --}}
            @if($myRepairFy || $myPetrolPending)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @if($myPetrolPending)
                <a href="{{ route('petrol.bills.create') }}" class="bg-white dark:bg-gray-800 rounded-xl border {{ $myPetrolPending['claimed'] ? 'border-gray-100 dark:border-gray-700' : 'border-amber-300' }} shadow-sm p-4 hover:shadow-md transition flex items-center justify-between">
                    <div>
                        <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Petrol Claim — {{ $myPetrolPending['month'] }} {{ $myPetrolPending['year'] }}</div>
                        @if($myPetrolPending['claimed'])
                            <div class="text-sm font-bold text-emerald-600 dark:text-emerald-400"><i class="fas fa-check-circle mr-1"></i> यो महिनाको Claim भइसक्यो</div>
                        @else
                            <div class="text-sm font-bold text-amber-600 dark:text-amber-400"><i class="fas fa-exclamation-circle mr-1"></i> {{ $myPetrolPending['month'] }} को Claim बाँकी छ</div>
                        @endif
                    </div>
                    <i class="fas fa-gas-pump text-2xl text-orange-400"></i>
                </a>
                @endif

                @if($myRepairFy)
                <a href="{{ route('repair.expenses.index', ['employee_id' => auth()->user()->employee_id, 'fy_year' => $myRepairFy['fy_year']]) }}"
                   class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4 hover:shadow-md hover:border-blue-200 transition">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Repair Expense — FY {{ $myRepairFy['fy_year'] }}</span>
                        <i class="fas fa-wrench text-orange-400"></i>
                    </div>
                    <div class="text-sm">
                        <span class="font-bold text-gray-800 dark:text-gray-200">रु. {{ number_format($myRepairFy['claimed'], 0) }}</span>
                        <span class="text-gray-400">claimed /</span>
                        <span class="font-bold {{ $myRepairFy['remaining'] > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">रु. {{ number_format($myRepairFy['remaining'], 0) }} बाँकी</span>
                        <span class="text-gray-400">(Limit रु. {{ number_format($myRepairFy['limit'], 0) }})</span>
                    </div>
                    <div class="text-[11px] text-blue-600 dark:text-blue-400 mt-1">कुन मितिमा के claim भयो हेर्नुहोस् →</div>
                </a>
                @endif
            </div>
            @endif

            {{-- ================= Quick Links ================= --}}
            <div>
                <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-3">छिटो पहुँच (Quick Links)</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <a href="{{ route('overtime.create') }}" class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-3 rounded-lg shadow-sm transition">
                        <i class="fas fa-plus"></i> OT Entry
                    </a>
                    <a href="{{ route('events.list') }}" class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-3 rounded-lg shadow-sm transition">
                        <i class="fas fa-calendar-check"></i> Event OT
                    </a>
                    <a href="{{ route('petrol.bills.create') }}" class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-3 rounded-lg shadow-sm transition">
                        <i class="fas fa-gas-pump"></i> Petrol Entry
                    </a>
                    <a href="{{ route('repair.expenses.create') }}" class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-3 rounded-lg shadow-sm transition">
                        <i class="fas fa-wrench"></i> Repair Entry
                    </a>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>