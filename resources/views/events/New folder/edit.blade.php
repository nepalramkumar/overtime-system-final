@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <!-- Page Header -->
    <div>
        <h2 class="text-2xl font-bold text-slate-800">कार्यक्रम (Event) सम्पादन गर्नुहोस्</h2>
        <p class="text-xs text-slate-500 mt-1">कार्यक्रमको अवधी, सिफारिसकर्ता तथा स्वीकृतकर्ताको विवरण अद्यावधिक गर्नुहोस्</p>
    </div>

    <!-- Verified Records Warning / Confirmation Alert -->
    @if(session('warning_verified_records'))
        <div class="bg-amber-50 border border-amber-300 rounded-xl p-4 shadow-sm space-y-3">
            <div class="flex items-start gap-3">
                <i class="fas fa-exclamation-triangle text-amber-600 text-lg mt-0.5"></i>
                <div>
                    <h3 class="text-sm font-bold text-amber-800">चेतावनी: Verified रेकर्डहरू फेला परे</h3>
                    <p class="text-xs text-amber-700 mt-0.5">
                        यो इभेन्टसँग जोडिएका <strong>{{ count(session('warning_verified_records')) }}</strong> वटा रेकर्डहरू पहिले नै Verified भइसकेका छन्। के तिनलाई पनि नयाँ दर अनुसार अपडेट गर्ने हो?
                    </p>
                </div>
            </div>

            <form action="{{ route('events.update', session('event_id')) }}" method="POST" class="space-y-3">
                @csrf
                @method('PUT')
                
                <!-- पुरानो data सुरक्षित राख्न hidden fields हरू -->
                <input type="hidden" name="event_name" value="{{ session('event_data')['event_name'] ?? $event->event_name }}">
                <input type="hidden" name="department" value="{{ session('event_data')['department'] ?? $event->department }}">
                <input type="hidden" name="start_date" value="{{ session('event_data')['start_date'] ?? $event->start_date }}">
                <input type="hidden" name="end_date" value="{{ session('event_data')['end_date'] ?? $event->end_date }}">
                <input type="hidden" name="start_time" value="{{ session('event_data')['start_time'] ?? $event->start_time }}">
                <input type="hidden" name="end_time" value="{{ session('event_data')['end_time'] ?? $event->end_time }}">
                <input type="hidden" name="approver_employee_id" value="{{ session('event_data')['approver_employee_id'] ?? $event->approver_employee_id }}">
                <input type="hidden" name="recommender_employee_id" value="{{ session('event_data')['recommender_employee_id'] ?? $event->recommender_employee_id }}">
                @if(isset(session('event_data')['is_tiffin_eligible']))
                    <input type="hidden" name="is_tiffin_eligible" value="1">
                @endif

                <input type="hidden" name="update_verified" value="1">
                <input type="hidden" name="checked_verified" value="1">

  <div class="bg-white border border-amber-200 rounded-lg p-3 max-h-40 overflow-y-auto text-xs space-y-1">
    @foreach(session('warning_verified_records') as $rec)
        <div class="text-slate-700 border-b border-slate-100 pb-1 flex justify-between">
            <span>
                <strong>{{ $rec->employee->name ?? 'N/A' }}</strong> 
                ({{ $rec->employee->employee_code ?? 'No Code' }}) 
                <!-- यहाँ adToBs() function प्रयोग गरिएको छ -->
                - मिति: {{ adToBs($rec->ot_date) }} वि.सं.
            </span>
            <span class="font-semibold text-amber-800">Verified</span>
        </div>
    @endforeach
</div>

                <div class="flex items-center gap-2 pt-1">
                    <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white font-medium text-xs px-4 py-2 rounded-lg shadow-sm transition">
                        हो, Verified समेत अपडेट गर्ने
                    </button>
                    <a href="{{ route('events.list') }}" class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-medium text-xs px-4 py-2 rounded-lg transition">
                        पर्दैन (छोड्ने)
                    </a>
                </div>
            </form>
        </div>
    @endif

    <!-- Form Card -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <form action="{{ route('events.update', $event->id) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">कार्यक्रमको नाम</label>
                    <input type="text" name="event_name" value="{{ old('event_name', $event->event_name) }}" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none" required>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">विभाग</label>
                    <select name="department" id="department-select" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none">
                        <option value="">-- छान्नुहोस् --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->name }}" {{ old('department', $event->department) == $dept->name ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">सुरु हुने मिति</label>
                    <input type="date" name="start_date" value="{{ old('start_date', $event->start_date) }}" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">सकिने मिति</label>
                    <input type="date" name="end_date" value="{{ old('end_date', $event->end_date) }}" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">सुरु हुने समय</label>
                    <input type="time" name="start_time" value="{{ old('start_time', $event->start_time) }}" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">सकिने समय</label>
                    <input type="time" name="end_time" value="{{ old('end_time', $event->end_time) }}" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">स्वीकृत गर्ने (निर्देशनालय प्रमुख)</label>
                <select name="approver_employee_id" id="approver-select" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none">
                    <option value="">-- छान्नुहोस् --</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ $event->approver_employee_id == $emp->id ? 'selected' : '' }}>
                            {{ $emp->name }} — {{ $emp->position->name ?? '' }} ({{ $emp->department }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">सिफारिस गर्ने</label>
                <select name="recommender_employee_id" id="recommender-select" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none">
                    <option value="">-- छान्नुहोस् --</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ $event->recommender_employee_id == $emp->id ? 'selected' : '' }}>
                            {{ $emp->name }} — {{ $emp->position->name ?? '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2.5 bg-slate-50 border border-slate-200 p-3.5 rounded-lg">
                <input type="checkbox" name="is_tiffin_eligible" value="1" {{ old('is_tiffin_eligible', $event->is_tiffin_eligible) ? 'checked' : '' }} class="w-4 h-4 text-purple-600 rounded border-slate-300 focus:ring-purple-500">
                <label class="text-xs font-semibold text-slate-700">
                    यस कार्यक्रमको OT दाबी गर्दा खाजा खर्च गणना गर्ने हो?
                </label>
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-medium text-xs py-2.5 rounded-lg shadow-sm transition flex items-center justify-center gap-1.5">
                    <i class="fas fa-sync-alt"></i>
                    <span>अपडेट गर्नुहोस्</span>
                </button>
            </div>
        </form>
    </div>
</div>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        ['department-select', 'recommender-select', 'approver-select'].forEach(function (id) {
            const el = document.getElementById(id);
            if (el) {
                new TomSelect('#' + id, {
                    create: false,
                    sortField: { field: "text", direction: "asc" }
                });
            }
        });
    });
</script>
@endsection