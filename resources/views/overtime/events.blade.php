@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Page Header & Action Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Active Events / Projects</h2>
            <p class="text-xs text-slate-500 mt-1">हाल सञ्चालनमा रहेका कार्यक्रम तथा प्रोजेक्टहरूको विवरण</p>
        </div>
        <a href="{{ route('overtime.create') }}" class="inline-flex items-center justify-center gap-2 bg-slate-800 hover:bg-slate-900 text-white font-medium px-4 py-2.5 rounded-lg shadow-sm transition text-sm">
            <i class="fas fa-plus"></i>
            <span>Log General OT (सामान्य प्रयोजन)</span>
        </a>
    </div>

    <!-- Responsive Event Table Card -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm text-slate-700">
                <thead class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="p-4">Event Name</th>
                        <th class="p-4">Department</th>
                        <th class="p-4">Date Range</th>
                        <th class="p-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($events as $event)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="p-4 font-semibold text-slate-800">
                                {{ $event->event_name }}
                            </td>
                            <td class="p-4 text-slate-600">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-slate-100 text-slate-700">
                                    {{ $event->department }}
                                </span>
                            </td>
                            <td class="p-4 text-slate-600 text-xs">
                                <i class="far fa-calendar-alt text-slate-400 mr-1"></i>
                                {{ adToBs($event->start_date) }} <span class="text-slate-400 font-bold mx-1">→</span> {{ adToBs($event->end_date) }}
                            </td>
                            <td class="p-4 text-right">
                                <a href="{{ route('overtime.create', ['event_id' => $event->id]) }}" 
                                   class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white px-3.5 py-1.5 rounded-lg text-xs font-semibold shadow-sm transition">
                                    <i class="fas fa-clock"></i>
                                    <span>Entry Overtime</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-8 text-center text-slate-400">
                                <i class="fas fa-calendar-times text-2xl mb-2 block text-slate-300"></i>
                                अहिले कुनै पनि सक्रिय कार्यक्रम (Active Events) छैनन्।
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection