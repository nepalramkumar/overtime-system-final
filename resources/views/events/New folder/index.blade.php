@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Active Events / Projects</h2>
            <p class="text-xs text-slate-500 mt-1">सबै कार्यक्रम / Project हरूको OT claim स्थिति यहाँ हेर्नुहोस्।</p>  
        </div>
        <a href="{{ route('overtime.create') }}"
           class="inline-flex items-center justify-center gap-2 bg-slate-700 text-white px-4 py-2.5 rounded-lg font-medium text-xs hover:bg-slate-800 transition shadow-sm">
            <i class="fas fa-plus-circle"></i>
            <span>Log General OT (सामान्य प्रयोजन)</span>
        </a>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-xs flex items-center gap-2 shadow-sm">
            <i class="fas fa-check-circle text-emerald-600 text-base"></i>
            <span>{{ session('success') }}</span>  
        </div>
    @endif

    {{-- Card wrapper --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm text-slate-700">
                <thead class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="p-4">Event Name</th>  
                        <th class="p-4">Department</th>  
                        <th class="p-4">Date Range</th>  
                        <th class="p-4">Status</th>  
                        <th class="p-4 text-right">Action</th>  
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($events as $event)
                        <tr class="hover:bg-slate-50/80 transition {{ !$event->is_active ? 'bg-slate-50/60' : '' }}">  

                            {{-- Event name + OT badges --}}
                            <td class="p-4 align-top">
                                <div class="font-semibold text-slate-800 {{ !$event->is_active ? 'text-slate-400' : '' }}">
                                    {{ $event->event_name }}  
                                </div>

                                <div class="flex flex-wrap gap-1.5 mt-2">
                                    @forelse($event->status_summary as $status => $count)
                                        @php
                                            $colors = match(strtolower($status)) {
                                                'pending'  => ['bg' => 'bg-amber-500', 'text' => 'text-amber-700', 'light' => 'bg-amber-50 border-amber-200'],
                                                'rejected' => ['bg' => 'bg-rose-500',  'text' => 'text-rose-700',  'light' => 'bg-rose-50 border-rose-200'],
                                                'verified' => ['bg' => 'bg-emerald-500','text' => 'text-emerald-700','light' => 'bg-emerald-50 border-emerald-200'],
                                                default    => ['bg' => 'bg-blue-500',  'text' => 'text-blue-700',  'light' => 'bg-blue-50 border-blue-200'],
                                            };
                                        @endphp
                                        <div class="flex items-center gap-1.5 {{ $colors['light'] }} border rounded-full pl-2.5 pr-1 py-0.5 shadow-2xs">
                                            <span class="text-[10px] font-bold {{ $colors['text'] }} uppercase tracking-wide">
                                                {{ $status }}  
                                            </span>
                                            <span class="{{ $colors['bg'] }} text-white text-[10px] font-bold w-4 h-4 flex items-center justify-center rounded-full">
                                                {{ $count }}  
                                            </span>
                                        </div>
                                    @empty
                                        <span class="text-[10px] text-slate-400 italic">कुनै claim छैन</span>  
                                    @endforelse
                                </div>
                            </td>

                            <td class="p-4 align-top text-xs text-slate-600">{{ $event->department }}</td>  

                            <td class="p-4 align-top text-xs text-slate-600 whitespace-nowrap">
                                {{ adToBs($event->start_date) }}
                                <span class="text-slate-300 mx-1">→</span>
                                {{ adToBs($event->end_date) }}  
                            </td>

                            <td class="p-4 align-top">
                                @if($event->is_active)
                                    <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-1 rounded-full text-xs font-semibold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                    </span>  
                                @else
                                    <span class="inline-flex items-center gap-1.5 bg-rose-50 text-rose-700 border border-rose-200 px-2.5 py-1 rounded-full text-xs font-semibold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Disabled
                                    </span>  
                                @endif
                            </td>

                            <td class="p-4 align-top text-right">
                                <div class="flex flex-wrap items-center justify-end gap-1.5">
                                    @if($event->is_active)
                                        <a href="{{ route('overtime.create', ['event_id' => $event->id]) }}"
                                           class="bg-blue-600 text-white px-2.5 py-1.5 rounded-lg text-xs font-semibold hover:bg-blue-700 transition shadow-2xs">
                                            Entry Overtime
                                        </a>  
                                    @else
                                        <span class="bg-slate-100 text-slate-400 px-2.5 py-1.5 rounded-lg text-xs font-semibold cursor-not-allowed border border-slate-200">
                                            Entry Overtime
                                        </span>  
                                    @endif

                                    <a href="{{ route('events.print', $event->id) }}" target="_blank"
                                       class="bg-purple-600 text-white px-2.5 py-1.5 rounded-lg text-xs font-semibold hover:bg-purple-700 transition shadow-2xs">
                                        Print
                                    </a>  
                                    
                                    <a href="{{ route('events.edit', $event->id) }}"
                                       class="bg-purple-600 text-white px-2.5 py-1.5 rounded-lg text-xs font-semibold hover:bg-purple-700 transition shadow-2xs">
                                        Edit
                                    </a>

                                    <form action="{{ route('events.toggle', $event->id) }}" method="POST" class="inline"
                                          onsubmit="return confirm('के तपाईं यो कार्यक्रमको Status बदल्न चाहनुहुन्छ?')">  
                                        @csrf
                                        <button type="submit"
                                            class="{{ $event->is_active ? 'bg-amber-600 hover:bg-amber-700' : 'bg-emerald-600 hover:bg-emerald-700' }} text-white px-2.5 py-1.5 rounded-lg text-xs font-semibold transition shadow-2xs">
                                            {{ $event->is_active ? 'Disable' : 'Enable' }}
                                        </button>  
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-10 text-center text-slate-400">
                                <i class="fas fa-calendar-times text-2xl mb-2 block text-slate-300"></i>
                                अहिले कुनै पनि सक्रिय कार्यक्रम छैनन्।
                            </td>  
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection