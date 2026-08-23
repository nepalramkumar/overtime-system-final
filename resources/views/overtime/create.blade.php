@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto">
    <!-- Page Header -->
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-bold text-gray-800">Overtime & Tiffin Entry</h2>
        <p class="text-xs text-gray-500 mt-1">ओभरटाइम तथा खाजा खर्च प्रविष्टि फारम</p>
    </div>

    <!-- Card Container -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
        <form action="{{ route('overtime.store') }}" method="POST" class="space-y-4">
            @csrf

            <!-- Employee Select -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Select Employee <span class="text-red-500">*</span>
                </label>
                @if($canSelectAny)
                    <select name="employee_id" id="employee-select" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm shadow-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" required>
                        <option value="">-- नाम वा ID टाइप गरेर खोज्नुहोस् --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->name }} (ID: {{ $emp->id }}) - {{ $emp->designation ?? 'N/A' }}
                            </option>
                        @endforeach
                    </select>
                @else
                    <div class="w-full px-3 py-2 border border-gray-200 rounded-lg bg-gray-50 text-gray-700 font-semibold text-sm flex items-center justify-between">
                        <span>{{ $lockedEmployee->name ?? 'N/A' }} ({{ $lockedEmployee->employee_code ?? 'ID: ' . ($lockedEmployee->id ?? '') }})</span>
                        <i class="fas fa-lock text-gray-400"></i>
                    </div>
                    <input type="hidden" name="employee_id" value="{{ $lockedEmployee->id ?? '' }}">
                @endif
                @error('employee_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Event / OT Category Banner -->
            @if(!empty($selectedEventId) && isset($events))
                @php
                    $currentEvent = $events->firstWhere('id', $selectedEventId);
                @endphp
                @if($currentEvent)
                    <div class="bg-emerald-50 p-3.5 rounded-lg border border-emerald-200">
                        <label class="block text-emerald-800 font-bold text-xs uppercase tracking-wide">Selected Event / Project</label>
                        <span class="text-gray-800 font-semibold text-base block mt-0.5">{{ $currentEvent->event_name }} ({{ $currentEvent->department }})</span>
                        <input type="hidden" name="event_id" value="{{ $currentEvent->id }}">
                    </div>
                @endif
            @else
                <div class="bg-gray-50 p-3.5 rounded-lg border border-gray-200">
                    <label class="block text-gray-500 font-bold text-xs uppercase tracking-wide">OT Category</label>
                    <span class="text-gray-700 font-semibold text-sm block mt-0.5">सामान्य प्रयोजन (General Purpose OT)</span>
                    <input type="hidden" name="event_id" value="">
                </div>

                <!-- Individual OT: सिफारिस/स्वीकृति गर्ने (Event मा जस्तै) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            सिफारिस गर्ने (Recommender) <span class="text-red-500">*</span>
                        </label>
                        <select name="recommender_employee_id" id="recommender-select" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm shadow-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" required>
                            <option value="">-- छान्नुहोस् --</option>
                            @foreach($allEmployees as $emp)
                                <option value="{{ $emp->id }}" {{ old('recommender_employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                            @endforeach
                        </select>
                        @error('recommender_employee_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            स्वीकृति गर्ने (Approver) <span class="text-red-500">*</span>
                        </label>
                        <select name="approver_employee_id" id="approver-select" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm shadow-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" required>
                            <option value="">-- छान्नुहोस् --</option>
                            @foreach($allEmployees as $emp)
                                <option value="{{ $emp->id }}" {{ old('approver_employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                            @endforeach
                        </select>
                        @error('approver_employee_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            @endif

            <!-- Date Input -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Date <span class="text-red-500">*</span>
                </label>
                @include('partials.bs-date-input', [
                    'name' => 'ot_date',
                    'value' => old('ot_date', date('Y-m-d')),
                    'class' => 'w-full px-3 py-2 border border-gray-300 rounded-lg text-sm shadow-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none cursor-pointer bg-white',
                    'required' => true,
                ])
                @error('ot_date')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Holiday: अब auto-calculate हुन्छ (Shift table + Holiday table अनुसार), Admin ले मात्र override गर्न पाउने -->
            @if(auth()->user()->role === 'admin')
                <div class="bg-amber-50 p-3 rounded-lg border border-amber-200/80">
                    <label class="block text-xs font-semibold text-amber-800 mb-1">बिदा (Holiday) — Admin Override</label>
                    <select name="is_holiday_override" class="w-full px-3 py-2 border border-amber-300 rounded-lg text-sm bg-white">
                        <option value="">Auto (Shift/Holiday table अनुसार गणना गर्ने)</option>
                        <option value="1">Force: यो बिदाको दिन हो</option>
                        <option value="0">Force: यो बिदाको दिन होइन</option>
                    </select>
                    <p class="text-[11px] text-amber-700 mt-1">सामान्यतया "Auto" नै छोड्नुहोस् — शनि/आइतबार र Holiday Settings मा थपिएका मिति स्वतः पत्ता लाग्छन्।</p>
                </div>
            @endif

            <!-- Time Inputs -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        From Time (सुरुको समय) <span class="text-red-500">*</span>
                    </label>
                    <input type="time" name="from_time" value="{{ old('from_time') }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm shadow-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" required>
                    @error('from_time')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        To Time (सकिने समय) <span class="text-red-500">*</span>
                    </label>
                    <input type="time" name="to_time" value="{{ old('to_time') }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm shadow-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" required>
                    @error('to_time')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Remarks -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Remarks (कैफियत)</label>
                <textarea name="remarks" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm shadow-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" rows="2" placeholder="कामको संक्षिप्त विवरण...">{{ old('remarks') }}</textarea>
                @error('remarks')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Button -->
            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-2.5 px-4 rounded-lg shadow-sm transition duration-150 flex items-center justify-center gap-2 text-sm">
                <i class="fas fa-paper-plane"></i>
                <span>Submit Overtime</span>
            </button>
        </form>
    </div>
</div>

<!-- TomSelect Integration -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        ['employee-select', 'recommender-select', 'approver-select'].forEach(function (id) {
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
<script src="{{ asset('js/bs-datepicker.js') }}"></script>
@endsection