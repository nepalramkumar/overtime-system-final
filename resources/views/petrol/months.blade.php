@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Petrol Month सेटिङ्स</h1>
            <p class="text-xs text-slate-500 mt-1">सिस्टममा प्रयोग हुने पेट्रोल भौचर महिना तथा वर्ष व्यवस्थापन गर्नुहोस्</p>
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-3.5 rounded-xl text-xs font-medium flex items-center gap-2 shadow-sm">
            <i class="fas fa-check-circle text-emerald-600 text-base"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-rose-50 border border-rose-200 text-rose-800 p-3.5 rounded-xl text-xs font-medium flex items-center gap-2 shadow-sm">
            <i class="fas fa-exclamation-circle text-rose-600 text-base"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Months List Table -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
            <h2 class="font-bold text-slate-700 text-sm">थपिएका महिनाहरूको सूची</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left text-sm text-slate-700">
                <thead class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="p-4">Month (महिना)</th>
                        <th class="p-4">Year (वर्ष)</th>
                        <th class="p-4 text-center w-32">Status</th>
                        <th class="p-4 text-center w-48">कार्य</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($months as $item)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="p-4 font-semibold text-slate-800">{{ $item->month }}</td>
                        <td class="p-4 font-mono text-xs text-slate-600">{{ $item->year }}</td>
                        <td class="p-4 text-center">
                            @if($item->status)
                                <span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-1 rounded-full text-xs font-semibold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Enabled
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 bg-slate-100 text-slate-600 border border-slate-200 px-2.5 py-1 rounded-full text-xs font-semibold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Disabled
                                </span>
                            @endif
                        </td>
                        <td class="p-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <form action="{{ route('petrol.months.toggleStatus', $item->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" 
                                            class="px-3 py-1.5 rounded-lg text-xs font-semibold text-white transition shadow-2xs {{ $item->status ? 'bg-amber-600 hover:bg-amber-700' : 'bg-emerald-600 hover:bg-emerald-700' }}">
                                        {{ $item->status ? 'Disable' : 'Enable' }}
                                    </button>
                                </form>
                                <form action="{{ route('petrol.months.destroy', $item->id) }}" method="POST" class="inline" onsubmit="return confirm('के तपाईं पक्का डिलिट गर्न चाहनुहुन्छ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition shadow-2xs">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="p-8 text-center text-slate-400">
                            <i class="fas fa-calendar-times text-2xl mb-2 block text-slate-300"></i>
                            कुनै Month थपिएको छैन।
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add New Month Form (With Searchable Select) -->
    <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm space-y-4">
        <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
            <i class="fas fa-plus-circle text-emerald-600"></i>
            <span>नयाँ Month थप्नुहोस्</span>
        </h3>

        <form action="{{ route('petrol.months.store') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">महिना छान्नुहोस् <span class="text-rose-500">*</span></label>
                <select name="month" id="select_month" class="w-full text-xs" required>
                    <option value="">-- महिना छान्नुहोस् --</option>
                    @foreach($bsMonths as $m)
                        <option value="{{ $m }}" {{ old('month') == $m ? 'selected' : '' }}>{{ $m }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">वर्ष छान्नुहोस् <span class="text-rose-500">*</span></label>
                <select name="year" id="select_year" class="w-full text-xs" required>
                    <option value="">-- वर्ष छान्नुहोस् --</option>
                    @foreach($yearOptions as $y)
                        <option value="{{ $y }}" {{ old('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs px-4 py-2.5 rounded-lg transition shadow-sm flex items-center justify-center gap-1.5 h-[38px]">
                <i class="fas fa-plus"></i>
                <span>थप्नुहोस्</span>
            </button>
        </form>
    </div>

</div>

<!-- TomSelect CSS & JS for Search Functionality -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tom-select/2.2.2/css/tom-select.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/tom-select/2.2.2/js/tom-select.complete.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof TomSelect !== 'undefined') {
        new TomSelect('#select_month', {
            create: false,
            placeholder: "-- महिना खोज्नुहोस् --",
            allowEmptyOption: true
        });

        new TomSelect('#select_year', {
            create: false,
            placeholder: "-- वर्ष खोज्नुहोस् --",
            allowEmptyOption: true
        });
    }
});
</script>
@endsection