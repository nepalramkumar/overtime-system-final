<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'OT System') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>

<body class="font-sans antialiased bg-slate-100 text-slate-800" x-data="{ mobileSidebarOpen: false }">
    <div class="flex min-h-screen relative overflow-x-hidden">

        <!-- Mobile Sidebar Backdrop -->
        <div x-show="mobileSidebarOpen" 
             @click="mobileSidebarOpen = false" 
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/80 z-40 lg:hidden" x-cloak></div>

        <!-- Sidebar -->
        <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-800 text-slate-100 flex-shrink-0 shadow-xl flex flex-col h-screen transform transition-transform duration-300 lg:static lg:translate-x-0"
               :class="mobileSidebarOpen ? 'translate-x-0' : '-translate-x-full'">

            <div class="flex-1 flex flex-col min-h-0">
                <!-- Logo / Brand -->
                <div class="p-5 border-b border-slate-700/70 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-blue-600 flex items-center justify-center shadow-md flex-shrink-0">
                            <i class="fas fa-clock text-white text-lg"></i>
                        </div>
                        <div class="leading-tight">
                            <h2 class="text-lg font-bold text-white">OT and Bills</h2>
                            <p class="text-xs text-blue-300">व्यवस्थापन प्रणाली</p>
                        </div>
                    </div>
                    <!-- Close button for mobile -->
                    <button @click="mobileSidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>

                <!-- Navigation Links -->
                <nav class="p-4 overflow-y-auto flex-1 space-y-4">

                    <!-- Dashboard -->
                    <div>
                        <ul class="space-y-1">
                            <li>
                                <a href="{{ route('dashboard') }}"
                                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition duration-150 text-sm font-medium
                                   {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white shadow-md' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                                    <i class="fas fa-gauge-high w-5 text-center"></i>
                                    <span>Dashboard</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Overtime Section -->
                    <div x-data="{ open: {{ request()->routeIs('overtime.*') ? 'true' : 'false' }} }">
                        <button @click="open = !open" class="w-full flex items-center justify-between px-3 mb-2 text-xs uppercase text-slate-400 font-semibold tracking-wider hover:text-blue-400 transition">
                            <span>Overtime</span>
                            <i class="fas fa-chevron-down text-[10px] transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                        </button>
                        <ul class="space-y-1 pl-2 border-l-2 border-slate-700" x-show="open" x-transition x-cloak>
                            <li>
                                <a href="{{ route('overtime.create') }}"
                                   class="flex items-center gap-3 px-3 py-2 rounded-lg transition duration-150 text-sm font-medium
                                   {{ request()->routeIs('overtime.create') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-700/70 hover:text-white' }}">
                                    <i class="fas fa-plus w-4 text-center"></i>
                                    <span>OT Entry</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('events.list') }}"
                                   class="flex items-center gap-3 px-3 py-2 rounded-lg transition duration-150 text-sm font-medium
                                   {{ request()->routeIs('events.list') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-700/70 hover:text-white' }}">
                                    <i class="fas fa-calendar-check w-4 text-center"></i>
                                    <span>Event OT Entry</span>
                                </a>
                            </li>
                            @if(userCan('overtime.entry'))
                            <li>
                                <a href="{{ route('overtime.my') }}"
                                   class="flex items-center gap-3 px-3 py-2 rounded-lg transition duration-150 text-sm font-medium
                                   {{ request()->routeIs('overtime.my') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-700/70 hover:text-white' }}">
                                    <i class="fas fa-user-clock w-4 text-center"></i>
                                    <span>मेरो OT Records</span>
                                </a>
                            </li>
                            @endif
                            @if(userCan('overtime.entry.all'))
                            <li>
                                <a href="{{ route('overtime.list') }}"
                                   class="flex items-center gap-3 px-3 py-2 rounded-lg transition duration-150 text-sm font-medium
                                   {{ request()->routeIs('overtime.list') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-700/70 hover:text-white' }}">
                                    <i class="fas fa-list w-4 text-center"></i>
                                    <span>सबै OT Records</span>
                                </a>
                            </li>
                            @endif
                            @if(userCan('overtime.verify'))
                            <li>
                                <a href="{{ route('overtime.pending') }}"
                                   class="flex items-center gap-3 px-3 py-2 rounded-lg transition duration-150 text-sm font-medium
                                   {{ request()->routeIs('overtime.pending') ? 'bg-amber-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-700/70 hover:text-white' }}">
                                    <i class="fas fa-clock w-4 text-center"></i>
                                    <span>सिफारिस बाँकी OT</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('overtime.recommended') }}"
                                   class="flex items-center gap-3 px-3 py-2 rounded-lg transition duration-150 text-sm font-medium
                                   {{ request()->routeIs('overtime.recommended') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-700/70 hover:text-white' }}">
                                    <i class="fas fa-user-check w-4 text-center"></i>
                                    <span>स्वीकृति बाँकी OT</span>
                                </a>
                            </li>
                            @endif
                            @if(userCan('overtime.unverify'))
                            <li>
                                <a href="{{ route('overtime.verified') }}"
                                   class="flex items-center gap-3 px-3 py-2 rounded-lg transition duration-150 text-sm font-medium
                                   {{ request()->routeIs('overtime.verified') ? 'bg-blue-700 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-700/70 hover:text-white' }}">
                                    <i class="fas fa-check-double w-4 text-center"></i>
                                    <span>Verified OT</span>
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>

                    <!-- Petrol / Repair Section -->
                    <div class="mb-5" x-data="{ open: {{ request()->routeIs('petrol.*') || request()->routeIs('repair.*') ? 'true' : 'false' }} }">
                        <button @click="open = !open" class="w-full flex items-center justify-between px-3 mb-2 text-xs uppercase text-slate-400 font-semibold tracking-wider hover:text-blue-400 transition">
                            <span>Petrol / Repair</span>
                            <i class="fas fa-chevron-down text-[10px] transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                        </button>
                        <ul class="space-y-1 pl-2 border-l-2 border-slate-700" x-show="open" x-transition x-cloak>
                            <li>
                                <a href="{{ route('petrol.bills.create') }}"
                                   class="flex items-center gap-3 px-3 py-2 rounded-lg transition duration-150 text-sm font-medium
                                   {{ request()->routeIs('petrol.bills.create') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-700/70 hover:text-white' }}">
                                    <i class="fas fa-plus w-4 text-center"></i>
                                    <span>Petrol Bill Entry</span>
                                </a>
                            </li>
                            @if(userCan('petrol.months.manage'))
                            <li>
                                <a href="{{ route('petrol.months.index') }}"
                                   class="flex items-center gap-3 px-3 py-2 rounded-lg transition duration-150 text-sm font-medium
                                   {{ request()->routeIs('petrol.months.*') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-700/70 hover:text-white' }}">
                                    <i class="fas fa-calendar-alt w-4 text-center"></i>
                                    <span>Petrol Months</span>
                                </a>
                            </li>
                            @endif
                            <li>
                                <a href="{{ route('repair.expenses.create') }}"
                                   class="flex items-center gap-3 px-3 py-2 rounded-lg transition duration-150 text-sm font-medium
                                   {{ request()->routeIs('repair.expenses.create') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-700/70 hover:text-white' }}">
                                    <i class="fas fa-wrench w-4 text-center"></i>
                                    <span>Repair Expense Entry</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Staff / HR Section -->
                    @if(userCan('employees.manage') || userCan('positions.manage') || userCan('events.manage') || userCan('users.manage') || userCan('settings.manage') || userCan('hr.sync'))
                    <div x-data="{ open: {{ request()->routeIs('users.*') || request()->routeIs('employees.*') || request()->routeIs('positions.*') || request()->routeIs('purposes.*') || request()->routeIs('shifts.*') || request()->routeIs('settings.*') || request()->routeIs('hr-sync.*') ? 'true' : 'false' }} }">
                        <button @click="open = !open" class="w-full flex items-center justify-between px-3 mb-2 text-xs uppercase text-slate-400 font-semibold tracking-wider hover:text-blue-400 transition">
                            <span>Staff / HR</span>
                            <i class="fas fa-chevron-down text-[10px] transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                        </button>
                        <ul class="space-y-1 pl-2 border-l-2 border-slate-700" x-show="open" x-transition x-cloak>
                            @if(userCan('users.manage'))
                            <li>
                                <a href="{{ route('users.index') }}"
                                   class="flex items-center gap-3 px-3 py-2 rounded-lg transition duration-150 text-sm font-medium
                                   {{ request()->routeIs('users.*') ? 'bg-blue-700 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-700/70 hover:text-white' }}">
                                    <i class="fas fa-user-shield w-4 text-center"></i>
                                    <span>Users</span>
                                </a>
                            </li>
                            @endif
                            @if(userCan('hr.sync'))
                            <li>
                                <a href="{{ route('hr-sync.index') }}"
                                   class="flex items-center gap-3 px-3 py-2 rounded-lg transition duration-150 text-sm font-medium
                                   {{ request()->routeIs('hr-sync.*') ? 'bg-blue-700 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-700/70 hover:text-white' }}">
                                    <i class="fas fa-sync w-4 text-center"></i>
                                    <span>HR Sync</span>
                                </a>
                            </li>
                            @endif
                            @if(userCan('employees.manage'))
                            <li>
                                <a href="{{ route('employees.index') }}"
                                   class="flex items-center gap-3 px-3 py-2 rounded-lg transition duration-150 text-sm font-medium
                                   {{ request()->routeIs('employees.index') ? 'bg-blue-700 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-700/70 hover:text-white' }}">
                                    <i class="fas fa-users w-4 text-center"></i>
                                    <span>Staff List</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('employees.create') }}"
                                   class="flex items-center gap-3 px-3 py-2 rounded-lg transition duration-150 text-sm font-medium
                                   {{ request()->routeIs('employees.create') ? 'bg-blue-700 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-700/70 hover:text-white' }}">
                                    <i class="fas fa-user-plus w-4 text-center"></i>
                                    <span>Add Staff</span>
                                </a>
                            </li>
                            @endif
                            @if(userCan('positions.manage'))
                            <li>
                                <a href="{{ route('positions.index') }}"
                                   class="flex items-center gap-3 px-3 py-2 rounded-lg transition duration-150 text-sm font-medium
                                   {{ request()->routeIs('positions.*') ? 'bg-blue-700 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-700/70 hover:text-white' }}">
                                    <i class="fas fa-briefcase w-4 text-center"></i>
                                    <span>Position Settings</span>
                                </a>
                            </li>
                            @endif
                            @if(userCan('events.manage'))
                            <li>
                                <a href="{{ route('purposes.index') }}"
                                   class="flex items-center gap-3 px-3 py-2 rounded-lg transition duration-150 text-sm font-medium
                                   {{ request()->routeIs('purposes.*') ? 'bg-blue-700 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-700/70 hover:text-white' }}">
                                    <i class="fas fa-tasks w-4 text-center"></i>
                                    <span>Purpose Settings</span>
                                </a>
                            </li>
                            @endif
                            @if(userCan('settings.manage'))
                            <li>
                                <a href="{{ route('shifts.index') }}"
                                   class="flex items-center gap-3 px-3 py-2 rounded-lg transition duration-150 text-sm font-medium
                                   {{ request()->routeIs('shifts.*') ? 'bg-blue-700 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-700/70 hover:text-white' }}">
                                    <i class="fas fa-business-time w-4 text-center"></i>
                                    <span>Shift Settings</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('holidays.index') }}"
                                   class="flex items-center gap-3 px-3 py-2 rounded-lg transition duration-150 text-sm font-medium
                                   {{ request()->routeIs('holidays.*') ? 'bg-blue-700 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-700/70 hover:text-white' }}">
                                    <i class="fas fa-umbrella-beach w-4 text-center"></i>
                                    <span>Holiday Settings</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('settings.snack') }}"
                                   class="flex items-center gap-3 px-3 py-2 rounded-lg transition duration-150 text-sm font-medium
                                   {{ request()->routeIs('settings.snack') ? 'bg-blue-700 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-700/70 hover:text-white' }}">
                                    <i class="fas fa-utensils w-4 text-center"></i>
                                    <span>Lunch Settings</span>
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>
                    @endif

                    <!-- Events Section -->
                    <div x-data="{ open: {{ request()->routeIs('events.*') ? 'true' : 'false' }} }">
                        <button @click="open = !open" class="w-full flex items-center justify-between px-3 mb-2 text-xs uppercase text-slate-400 font-semibold tracking-wider hover:text-blue-400 transition">
                            <span>Events</span>
                            <i class="fas fa-chevron-down text-[10px] transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                        </button>
                        <ul class="space-y-1 pl-2 border-l-2 border-slate-700" x-show="open" x-transition x-cloak>
                            @if(userCan('events.manage'))
                            <li>
                                <a href="{{ route('events.create') }}"
                                   class="flex items-center gap-3 px-3 py-2 rounded-lg transition duration-150 text-sm font-medium
                                   {{ request()->routeIs('events.create') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-700/70 hover:text-white' }}">
                                    <i class="fas fa-calendar-plus w-4 text-center"></i>
                                    <span>नयाँ Event</span>
                                </a>
                            </li>
                            @endif
                            <li>
                                <a href="{{ route('events.list') }}"
                                   class="flex items-center gap-3 px-3 py-2 rounded-lg transition duration-150 text-sm font-medium
                                   {{ request()->routeIs('events.list') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-700/70 hover:text-white' }}">
                                    <i class="fas fa-calendar-days w-4 text-center"></i>
                                    <span>Events List</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Reports Section -->
                    @if(userCan('reports.view'))
                    <div x-data="{ open: {{ request()->routeIs('reports.*') ? 'true' : 'false' }} }">
                        <button @click="open = !open" class="w-full flex items-center justify-between px-3 mb-2 text-xs uppercase text-slate-400 font-semibold tracking-wider hover:text-blue-400 transition">
                            <span>Report</span>
                            <i class="fas fa-chevron-down text-[10px] transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                        </button>
                        <ul class="space-y-1 pl-2 border-l-2 border-slate-700" x-show="open" x-transition x-cloak>
                            <li>
                                <a href="{{ route('reports.index') }}"
                                   class="flex items-center gap-3 px-3 py-2 rounded-lg transition duration-150 text-sm font-medium
                                   {{ request()->routeIs('reports.index') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-700/70 hover:text-white' }}">
                                    <i class="fas fa-chart-column w-4 text-center"></i>
                                    <span>Main Report</span>
                                </a>
                            </li>
                            @if(userCan('petrol.bills.view'))
                            <li>
                                <a href="{{ route('petrol.bills.index') }}"
                                   class="flex items-center gap-3 px-3 py-2 rounded-lg transition duration-150 text-sm font-medium
                                   {{ request()->routeIs('petrol.bills.index') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-700/70 hover:text-white' }}">
                                    <i class="fas fa-gas-pump w-4 text-center"></i>
                                    <span>Petrol Bills</span>
                                </a>
                            </li>
                            @endif
                            @if(userCan('repair.expenses.view'))
                            <li>
                                <a href="{{ route('repair.expenses.index') }}"
                                   class="flex items-center gap-3 px-3 py-2 rounded-lg transition duration-150 text-sm font-medium
                                   {{ request()->routeIs('repair.expenses.index') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-700/70 hover:text-white' }}">
                                    <i class="fas fa-wrench w-4 text-center"></i>
                                    <span>Repair Expenses</span>
                                </a>
                            </li>
                            @endif
                            <li>
                                <a href="{{ route('reports.summary') }}"
                                   class="flex items-center gap-3 px-3 py-2 rounded-lg transition duration-150 text-sm font-medium
                                   {{ request()->routeIs('reports.summary') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-700/70 hover:text-white' }}">
                                    <i class="fas fa-table w-4 text-center"></i>
                                    <span>Summary</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('reports.finance') }}"
                                   class="flex items-center gap-3 px-3 py-2 rounded-lg transition duration-150 text-sm font-medium
                                   {{ request()->routeIs('reports.finance') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-700/70 hover:text-white' }}">
                                    <i class="fas fa-sack-dollar w-4 text-center"></i>
                                    <span>Finance</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    @endif

                    <!-- Administration Section -->
                    @if(auth()->user()->role === 'admin')
                    <div>
                        <h3 class="px-3 mb-2 text-xs uppercase text-slate-400 font-semibold tracking-wider">
                            Administration
                        </h3>
                        <ul class="space-y-1">
                            <li>
                                <a href="{{ route('permissions.index') }}"
                                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition duration-150 text-sm font-medium
                                   {{ request()->routeIs('permissions.*') ? 'bg-rose-700 text-white shadow-md' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                                    <i class="fas fa-lock w-5 text-center text-rose-400"></i>
                                    <span>Role Permissions</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    @endif

                </nav>
            </div>

            <!-- Sidebar Footer: User Profile -->
            <div class="p-4 border-t border-slate-700/80 bg-slate-900/80 flex-shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-blue-600 text-white flex items-center justify-center flex-shrink-0 text-sm font-semibold uppercase shadow">
                        {{ substr(auth()->user()->name ?? '?', 0, 1) }}
                    </div>
                    <div class="leading-tight overflow-hidden">
                        <p class="text-sm font-medium text-slate-100 truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-blue-400 capitalize">{{ auth()->user()->role }}</p>
                    </div>
                </div>
            </div>

        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden">

            <!-- Top Header Bar -->
            <header class="bg-white shadow-sm border-b border-slate-200 px-4 sm:px-6 py-3 flex items-center justify-between flex-shrink-0">
                <div class="flex items-center gap-3 lg:hidden">
                    <button @click="mobileSidebarOpen = !mobileSidebarOpen" class="p-2 text-slate-600 hover:text-slate-900 focus:outline-none">
                        <i class="fas fa-bars text-2xl"></i>
                    </button>
                </div>
                <div class="w-full">
                    @include('layouts.navigation')
                </div>
            </header>

            <main class="p-4 sm:p-6 lg:p-8 flex-1 overflow-y-auto">
                <div class="max-w-screen-2xl mx-auto space-y-4">

                    <!-- Flash Message Notifications -->
                    @if (session('success'))
                        <div x-data="{ show: true }" x-show="show" class="p-4 bg-emerald-100 border-l-4 border-emerald-500 text-emerald-800 rounded-r shadow-sm flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-check-circle text-emerald-600"></i>
                                <span>{{ session('success') }}</span>
                            </div>
                            <button @click="show = false" class="text-emerald-700 hover:text-emerald-900"><i class="fas fa-times"></i></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div x-data="{ show: true }" x-show="show" class="p-4 bg-rose-100 border-l-4 border-rose-500 text-rose-800 rounded-r shadow-sm flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-exclamation-circle text-rose-600"></i>
                                <span>{{ session('error') }}</span>
                            </div>
                            <button @click="show = false" class="text-rose-700 hover:text-rose-900"><i class="fas fa-times"></i></button>
                        </div>
                    @endif

                    <!-- Page Container -->
                    <div class="bg-white text-slate-800 rounded-xl shadow-sm border border-slate-200/80 p-5 sm:p-6">
                        {{ $slot ?? '' }}
                        @yield('content')
                    </div>

                </div>
            </main>

        </div>

    </div>
</body>
</html>