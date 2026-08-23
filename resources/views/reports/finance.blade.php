@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">💰 Finance रिपोर्ट</h2>
            <p class="text-xs text-slate-500 mt-1">ओभरटाइम खर्च तथा वित्तीय विवरणको सूची</p>
        </div>
        <div>
            <a href="{{ route('reports.exportFinanceExcel', request()->all()) }}" 
               class="bg-emerald-600 hover:bg-emerald-700 text-white px-3.5 py-2 rounded-lg text-xs font-semibold shadow-sm transition inline-flex items-center gap-1.5">
                <i class="fas fa-file-excel"></i>
                <span>Excel डाउनलोड (Finance)</span>
            </a>
        </div>
    </div>

    <!-- Warning Alert -->
    @if(session('warning'))
        <div class="bg-amber-50 border border-amber-200 text-amber-800 p-4 rounded-xl shadow-sm text-sm flex items-center justify-between">
            <div class="flex items-center gap-2 font-medium">
                <i class="fas fa-exclamation-circle text-amber-600"></i>
                <span>चेतावनी! {{ session('warning') }}</span>
            </div>
            <a href="{{ route('employees.index') }}" class="underline font-semibold text-amber-900 hover:text-black text-xs">यहाँ क्लिक गर्नुहोस् →</a>
        </div>
    @endif

    <!-- Filter Form -->
    <form action="{{ route('reports.finance') }}" method="GET" class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">From Date</label>
                @include('partials.bs-date-input', [
                    'name' => 'from_date',
                    'value' => request('from_date'),
                    'class' => 'w-full border border-slate-300 rounded-lg text-xs p-2.5 cursor-pointer bg-white',
                ])
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">To Date</label>
                @include('partials.bs-date-input', [
                    'name' => 'to_date',
                    'value' => request('to_date'),
                    'class' => 'w-full border border-slate-300 rounded-lg text-xs p-2.5 cursor-pointer bg-white',
                ])
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">कर्मचारी (नाम वा पद)</label>
                <input type="text" name="employee_search" list="employees_list" value="{{ request('employee_search') }}" placeholder="नाम वा पद टाइप गर्नुहोस्..." class="w-full border border-slate-300 rounded-lg text-xs p-2.5">
                <datalist id="employees_list">
                    @foreach(\App\Models\Employee::with('position')->get() as $emp)
                        <option value="{{ $emp->name }} - {{ $emp->position->name ?? 'N/A' }} (कोड: {{ $emp->employee_code }})">
                    @endforeach
                </datalist>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">कार्यक्रम (Event)</label>
                <input type="text" name="event_search" list="events_list" value="{{ request('event_search') }}" placeholder="कार्यक्रमको नाम टाइप गर्नुहोस्..." class="w-full border border-slate-300 rounded-lg text-xs p-2.5">
                <datalist id="events_list">
                    @foreach(\App\Models\Event::orderBy('id', 'desc')->get() as $event)
                        <option value="{{ $event->event_name }}">
                    @endforeach
                </datalist>
            </div>
            <div class="flex gap-2 items-center">
                <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white text-xs px-4 py-2.5 rounded-lg transition font-medium flex items-center gap-1 shadow-2xs">
                    <i class="fas fa-search"></i>
                    <span>खोज</span>
                </button>
                <a href="{{ route('reports.finance') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 text-xs px-4 py-2.5 rounded-lg transition font-medium">
                    Reset
                </a>
            </div>
        </div>
    </form>

    <!-- Table Container -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left text-xs text-slate-700">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase tracking-wider font-semibold">
                    <tr>
                        <th class="p-3 text-center w-12">सि.नं.</th>
                        <th class="p-3">कर्मचारी कोड</th>
                        <th class="p-3">Name</th>
                        <th class="p-3">पद</th>
                        <th class="p-3">कार्यक्रम</th>
                        <th class="p-3 text-center">Total Hours (HH:MM)</th>
                        <th class="p-3 text-center">Total Hours (Decimal)</th>
                        <th class="p-3 text-center">OT Rate</th>
                        <th class="p-3 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @php $sn = 1; @endphp
                    @forelse($financeData as $data)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="p-3 text-center text-slate-500 font-medium">{{ $sn++ }}</td>
                        <td class="p-3 font-mono text-slate-700">{{ $data->employee->employee_code ?? '-' }}</td>
                        <td class="p-3 font-semibold text-slate-800">{{ $data->employee->name ?? 'N/A' }}</td>
                        <td class="p-3 text-slate-600">{{ $data->employee->position->name ?? 'N/A' }}</td>
                        <td class="p-3 text-slate-700">
                            {{ $data->event->event_name ?? 'N/A' }}
                            @if($data->event)
                                <br><span class="text-[11px] text-slate-400">({{ adToBs($data->event->start_date) }} - {{ adToBs($data->event->end_date) }})</span>
                            @endif
                        </td>
                        <td class="p-3 text-center font-mono text-slate-700">{{ hoursToHm($data->total_hours) }}</td>
                        <td class="p-3 text-center font-mono text-slate-700">{{ number_format($data->total_hours, 2) }}</td>
                        <td class="p-3 text-center text-slate-700">{{ $data->employee->position->ot_rate ?? 'N/A' }}</td>
                        <td class="p-3 text-right font-semibold text-slate-900">
                            रु {{ number_format($data->total_hours * ($data->employee->position->ot_rate ?? 0), 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="p-8 text-center text-slate-400">
                            <i class="fas fa-folder-open text-2xl mb-2 block text-slate-300"></i>
                            कुनै डेटा भेटिएन।
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<script src="{{ asset('js/bs-datepicker.js') }}"></script>
@endsection