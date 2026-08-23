<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'OT System') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Font Awesome Icons -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="flex min-h-screen">

        <!-- Sidebar -->
        <aside class="w-64 bg-gray-800 text-white flex-shrink-0 shadow-xl flex flex-col">

            <!-- Logo / Brand -->
            <div class="p-5 border-b border-gray-700 flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-600 flex items-center justify-center shadow-md flex-shrink-0">
                    <i class="fas fa-clock text-white text-lg"></i>
                </div>
                <div class="leading-tight">
                    <h2 class="text-lg font-bold">OT System</h2>
                    <p class="text-xs text-gray-400">व्यवस्थापन प्रणाली</p>
                </div>
            </div>

            <nav class="p-4 flex-1 overflow-y-auto">

                <!-- Main -->
                <div class="mb-5">
                    <ul class="space-y-1">
                        <li>
                            <a href="{{ route('dashboard') }}"
                               class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition duration-150 text-sm font-medium
                               {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white shadow-md' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                                <i class="fas fa-gauge-high w-5 text-center"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Overtime -->
                <div class="mb-5" x-data="{ open: true }">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-3 mb-2 text-xs uppercase text-gray-400 font-semibold tracking-wider">
                        <span>Overtime</span>
                        <i class="fas fa-chevron-down text-[10px] transition-transform" :class="{ 'rotate-180': !open }"></i>
                    </button>
                    <ul class="space-y-1" x-show="open" x-transition>
                        @if(userCan('overtime.entry'))
                        <li>
                            <a href="{{ route('overtime.create') }}"
                               class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition duration-150 text-sm font-medium
                               {{ request()->routeIs('overtime.create') ? 'bg-emerald-600 text-white shadow-md' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                                <i class="fas fa-plus w-5 text-center"></i>
                                <span>OT Entry</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('overtime.my') }}"
                               class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition duration-150 text-sm font-medium
                               {{ request()->routeIs('overtime.my') ? 'bg-emerald-600 text-white shadow-md' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                                <i class="fas fa-user-clock w-5 text-center"></i>
                                <span>मेरो OT Records</span>
                            </a>
                        </li>
                        @endif
                        @if(userCan('overtime.entry.all'))
                        <li>
                            <a href="{{ route('overtime.list') }}"
                               class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition duration-150 text-sm font-medium
                               {{ request()->routeIs('overtime.list') ? 'bg-emerald-600 text-white shadow-md' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                                <i class="fas fa-list w-5 text-center"></i>
                                <span>सबै OT Records</span>
                            </a>
                        </li>
                        @endif
                        @if(userCan('overtime.verify'))
                        <li>
                            <a href="{{ route('overtime.pending') }}"
                               class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition duration-150 text-sm font-medium
                               {{ request()->routeIs('overtime.pending') ? 'bg-emerald-600 text-white shadow-md' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                                <i class="fas fa-clock w-5 text-center"></i>
                                <span>Pending OT</span>
                            </a>
                        </li>
                        @endif
                        @if(userCan('overtime.unverify'))
                        <li>
                            <a href="{{ route('overtime.verified') }}"
                               class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition duration-150 text-sm font-medium
                               {{ request()->routeIs('overtime.verified') ? 'bg-emerald-600 text-white shadow-md' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                                <i class="fas fa-check-double w-5 text-center"></i>
                                <span>Verified OT (Unverify)</span>
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>

               <!-- Petrol -->
                @if(userCan('petrol.bills.view') || userCan('petrol.months.manage') || userCan('repair.expenses.view'))
                <div class="mb-5" x-data="{ open: true }">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-3 mb-2 text-xs uppercase text-gray-400 font-semibold tracking-wider">
                        <span>Petrol</span>
                        <i class="fas fa-chevron-down text-[10px] transition-transform" :class="{ 'rotate-180': !open }"></i>
                    </button>
                    <ul class="space-y-1" x-show="open" x-transition>
                        @if(userCan('petrol.bills.view'))
                        <li>
                            <a href="{{ route('petrol.bills.index') }}"
                               class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition duration-150 text-sm font-medium
                               {{ request()->routeIs('petrol.bills.*') ? 'bg-orange-600 text-white shadow-md' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                                <i class="fas fa-gas-pump w-5 text-center"></i>
                                <span>Petrol Bills</span>
                            </a>
                        </li>
                        @endif
                        @if(userCan('petrol.months.manage'))
                        <li>
                            <a href="{{ route('petrol.months.index') }}"
                               class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition duration-150 text-sm font-medium
                               {{ request()->routeIs('petrol.months.*') ? 'bg-orange-600 text-white shadow-md' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                                <i class="fas fa-calendar-alt w-5 text-center"></i>
                                <span>Petrol Months</span>
                            </a>
                        </li>
                        @endif
                        @if(userCan('repair.expenses.view'))
                        <li>
                            <a href="{{ route('repair.expenses.index') }}"
                               class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition duration-150 text-sm font-medium
                               {{ request()->routeIs('repair.expenses.*') ? 'bg-orange-600 text-white shadow-md' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                                <i class="fas fa-wrench w-5 text-center"></i>
                                <span>Repair Expenses</span>
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>
                @endif

                <!-- Staff / HR -->
                @if(userCan('employees.manage') || userCan('positions.manage') || userCan('events.manage') || userCan('users.manage') || userCan('settings.manage'))
                <div class="mb-5" x-data="{ open: true }">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-3 mb-2 text-xs uppercase text-gray-400 font-semibold tracking-wider">
                        <span>Staff / HR</span>
                        <i class="fas fa-chevron-down text-[10px] transition-transform" :class="{ 'rotate-180': !open }"></i>
                    </button>
                    <ul class="space-y-1" x-show="open" x-transition>
                        @if(userCan('users.manage'))
                        <li>
                            <a href="{{ route('users.index') }}"
                               class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition duration-150 text-sm font-medium
                               {{ request()->routeIs('users.*') ? 'bg-amber-600 text-white shadow-md' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                                <i class="fas fa-user-shield w-5 text-center"></i>
                                <span>Users</span>
                            </a>
                        </li>
                        @endif
                        @if(userCan('employees.manage'))
                        <li>
                            <a href="{{ route('employees.index') }}"
                               class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition duration-150 text-sm font-medium
                               {{ request()->routeIs('employees.index') ? 'bg-amber-600 text-white shadow-md' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                                <i class="fas fa-users w-5 text-center"></i>
                                <span>Staff</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('employees.create') }}"
                               class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition duration-150 text-sm font-medium
                               {{ request()->routeIs('employees.create') ? 'bg-amber-600 text-white shadow-md' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                                <i class="fas fa-user-plus w-5 text-center"></i>
                                <span>Add Staff</span>
                            </a>
                        </li>
                        @endif
                        @if(userCan('positions.manage'))
                        <li>
                            <a href="{{ route('positions.index') }}"
                               class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition duration-150 text-sm font-medium
                               {{ request()->routeIs('positions.*') ? 'bg-amber-600 text-white shadow-md' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                                <i class="fas fa-briefcase w-5 text-center"></i>
                                <span>Position Settings</span>
                            </a>
                        </li>
                        @endif
                        @if(userCan('events.manage'))
                        <li>
                            <a href="{{ route('purposes.index') }}"
                               class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition duration-150 text-sm font-medium
                               {{ request()->routeIs('purposes.*') ? 'bg-amber-600 text-white shadow-md' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                                <i class="fas fa-tasks w-5 text-center"></i>
                                <span>Purpose Settings</span>
                            </a>
                        </li>
                        @endif
                        @if(userCan('settings.manage'))
                        <li>
                            <a href="{{ route('shifts.index') }}"
                               class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition duration-150 text-sm font-medium
                               {{ request()->routeIs('shifts.*') ? 'bg-amber-600 text-white shadow-md' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                                <i class="fas fa-business-time w-5 text-center"></i>
                                <span>Shift Settings</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('settings.snack') }}"
                               class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition duration-150 text-sm font-medium
                               {{ request()->routeIs('settings.snack') ? 'bg-amber-600 text-white shadow-md' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                                <i class="fas fa-utensils w-5 text-center"></i>
                                <span>Lunch Settings</span>
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>
                @endif

                <!-- Events -->
                @if(userCan('events.manage') || userCan('overtime.entry'))
                <div class="mb-5" x-data="{ open: true }">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-3 mb-2 text-xs uppercase text-gray-400 font-semibold tracking-wider">
                        <span>Events</span>
                        <i class="fas fa-chevron-down text-[10px] transition-transform" :class="{ 'rotate-180': !open }"></i>
                    </button>
                    <ul class="space-y-1" x-show="open" x-transition>
                        @if(userCan('events.manage'))
                        <li>
                            <a href="{{ route('events.create') }}"
                               class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition duration-150 text-sm font-medium
                               {{ request()->routeIs('events.create') ? 'bg-purple-600 text-white shadow-md' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                                <i class="fas fa-calendar-plus w-5 text-center"></i>
                                <span>नयाँ Event</span>
                            </a>
                        </li>
                        @endif
                        <li>
                            <a href="{{ route('events.list') }}"
                               class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition duration-150 text-sm font-medium
                               {{ request()->routeIs('events.list') ? 'bg-purple-600 text-white shadow-md' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                                <i class="fas fa-calendar-days w-5 text-center"></i>
                                <span>Events List</span>
                            </a>
                        </li>
                    </ul>
                </div>
                @endif

                <!-- Reports -->
                @if(userCan('reports.view'))
                <div class="mb-5" x-data="{ open: true }">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-3 mb-2 text-xs uppercase text-gray-400 font-semibold tracking-wider">
                        <span>Report</span>
                        <i class="fas fa-chevron-down text-[10px] transition-transform" :class="{ 'rotate-180': !open }"></i>
                    </button>
                    <ul class="space-y-1" x-show="open" x-transition>
                        <li>
                            <a href="{{ route('reports.index') }}"
                               class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition duration-150 text-sm font-medium
                               {{ request()->routeIs('reports.index') ? 'bg-indigo-600 text-white shadow-md' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                                <i class="fas fa-chart-column w-5 text-center"></i>
                                <span>Main Report</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('reports.summary') }}"
                               class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition duration-150 text-sm font-medium
                               {{ request()->routeIs('reports.summary') ? 'bg-indigo-600 text-white shadow-md' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                                <i class="fas fa-table w-5 text-center"></i>
                                <span>Summary</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('reports.finance') }}"
                               class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition duration-150 text-sm font-medium
                               {{ request()->routeIs('reports.finance') ? 'bg-indigo-600 text-white shadow-md' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                                <i class="fas fa-sack-dollar w-5 text-center"></i>
                                <span>Finance</span>
                            </a>
                        </li>
                    </ul>
                </div>
                @endif

                <!-- Administration -->
                @if(auth()->user()->role === 'admin')
                <div class="mb-5">
                    <h3 class="px-3 mb-2 text-xs uppercase text-gray-400 font-semibold tracking-wider">
                        Administration
                    </h3>
                    <ul class="space-y-1">
                        <li>
                            <a href="{{ route('permissions.index') }}"
                               class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition duration-150 text-sm font-medium
                               {{ request()->routeIs('permissions.*') ? 'bg-red-600 text-white shadow-md' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                                <i class="fas fa-lock w-5 text-center"></i>
                                <span>Role Permissions</span>
                            </a>
                        </li>
                    </ul>
                </div>
                @endif

            </nav>

            <!-- Sidebar Footer: Logged-in User -->
            <div class="p-4 border-t border-gray-700 flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-gray-600 flex items-center justify-center flex-shrink-0 text-sm font-semibold uppercase">
                    {{ substr(auth()->user()->name ?? '?', 0, 1) }}
                </div>
                <div class="leading-tight overflow-hidden">
                    <p class="text-sm font-medium truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-gray-400 capitalize">{{ auth()->user()->role }}</p>
                </div>
            </div>
        </aside>

        <!-- Content Area -->
        <div class="flex-1 flex flex-col">

            <header>
                @include('layouts.navigation')
            </header>

            <main class="p-8">
                <div class="bg-white p-6 rounded shadow">
                    {{ $slot ?? '' }}
                    @yield('content')
                </div>
            </main>

        </div>

    </div>
</body>
</html>