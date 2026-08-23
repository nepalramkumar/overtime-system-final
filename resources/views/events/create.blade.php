@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <!-- Page Header -->
    <div>
        <h2 class="text-2xl font-bold text-slate-800">कार्यक्रम (Event) दर्ता गर्नुहोस्</h2>
        <p class="text-xs text-slate-500 mt-1">नयाँ कार्यक्रम वा प्रोजेक्टको विवरण र समय सीमा दर्ता गर्नुहोस्</p>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <form action="{{ route('events.store') }}" method="POST" class="space-y-4">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">कार्यक्रमको नाम</label>
                    <input type="text" name="event_name" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none" required>  
                </div>

   <div>
    <label class="block text-xs font-semibold text-slate-600 mb-1">विभाग</label>
    <select name="department" id="department-select" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none">
        <option value="">-- छान्नुहोस् --</option>
        @foreach($departments as $dept)
            <option value="{{ $dept->name }}">{{ $dept->name }}</option>
        @endforeach
    </select>
</div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">सुरु हुने मिति</label>
                    @include('partials.bs-date-input', [
                        'name' => 'start_date',
                        'value' => old('start_date'),
                        'class' => 'w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none cursor-pointer bg-white',
                    ])
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">सकिने मिति</label>
                    @include('partials.bs-date-input', [
                        'name' => 'end_date',
                        'value' => old('end_date'),
                        'class' => 'w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none cursor-pointer bg-white',
                    ])
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">सुरु हुने समय</label>
                    <input type="time" name="start_time" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none">  
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">सकिने समय</label>
                    <input type="time" name="end_time" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none">  
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">सिफारिस गर्ने</label>
                <select name="recommender_employee_id" id="recommender-select" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none">
                    <option value="">-- छान्नुहोस् --</option>  
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}">
                            {{ $emp->name }} — {{ $emp->position->name ?? '' }}
                        </option>  
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">स्वीकृत गर्ने (निर्देशनालय प्रमुख)</label>
                <select name="approver_employee_id" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none">
                    <option value="">-- छान्नुहोस् --</option>  
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}">
                            {{ $emp->name }} — {{ $emp->position->name ?? '' }} ({{ $emp->department }})
                        </option>  
                    @endforeach
                </select>
            </div>
            
            <div class="flex items-center gap-2.5 bg-slate-50 border border-slate-200 p-3.5 rounded-lg">
                <input type="checkbox" name="is_tiffin_eligible" value="1" class="w-4 h-4 text-purple-600 rounded border-slate-300 focus:ring-purple-500">  
                <label class="text-xs font-semibold text-slate-700">
                    यस कार्यक्रमको OT दाबी गर्दा खाजा खर्च गणना गर्ने हो?
                </label>  
            </div>
            
            <div class="pt-2">
                <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-medium text-xs py-2.5 rounded-lg shadow-sm transition flex items-center justify-center gap-1.5">
                    <i class="fas fa-save"></i>
                    <span>दर्ता गर्नुहोस्</span>
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