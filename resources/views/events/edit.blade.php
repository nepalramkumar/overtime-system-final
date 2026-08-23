@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <!-- Page Header -->
    <div>
        <h2 class="text-2xl font-bold text-slate-800">कार्यक्रम (Event) सम्पादन गर्नुहोस्</h2>
        <p class="text-xs text-slate-500 mt-1">कार्यक्रमको अवधी, सिफारिसकर्ता तथा स्वीकृतकर्ताको विवरण अद्यावधिक गर्नुहोस्</p>
    </div>

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
                    @include('partials.bs-date-input', [
                        'name' => 'start_date',
                        'value' => old('start_date', $event->start_date),
                        'class' => 'w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none cursor-pointer bg-white',
                    ])
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">सकिने मिति</label>
                    @include('partials.bs-date-input', [
                        'name' => 'end_date',
                        'value' => old('end_date', $event->end_date),
                        'class' => 'w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none cursor-pointer bg-white',
                    ])
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

            <div class="flex items-start gap-2.5 bg-amber-50 border border-amber-200 p-3.5 rounded-lg">
                <input type="checkbox" name="recalc_verified" value="1" {{ old('recalc_verified') ? 'checked' : '' }} class="w-4 h-4 mt-0.5 text-amber-600 rounded border-slate-300 focus:ring-amber-500">
                <label class="text-xs text-slate-700">
                    <span class="font-semibold">माथिको खाजा सेटिङ बदलिएमा:</span>
                    पहिले नै <span class="font-semibold">Verified</span> भइसकेका OT record हरूको खाजा रकम पनि पुनः गणना गर्ने?
                    <br><span class="text-slate-500">(नकोरे — Verified नभएका record हरू मात्र अपडेट हुनेछन्।)</span>
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
<script src="{{ asset('js/bs-datepicker.js') }}"></script>
@endsection