@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto space-y-6">

    <!-- Page Header -->
    <div>
        <h2 class="text-2xl font-bold text-slate-800">कर्मचारीको विवरण Edit गर्नुहोस्</h2>
        <p class="text-xs text-slate-500 mt-1">कर्मचारीको अनुमति, सवारी साधन तथा पेट्रोल/मर्मत सीमा व्यवस्थापन</p>
    </div>

    @if($errors->any())
        <div class="bg-rose-50 border border-rose-200 p-4 rounded-xl shadow-sm text-sm flex flex-col gap-1 text-rose-800">
            <div class="flex items-center gap-2 font-semibold">
                <i class="fas fa-exclamation-circle text-rose-600 text-base"></i>
                <span>कृपया निम्न त्रुटिहरू सच्याउनुहोस्:</span>
            </div>
            <ul class="list-disc list-inside text-xs pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>                @endforeach
            </ul>
        </div>
    @endif

    <!-- Form Card -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <form action="{{ route('employees.update', $employee->id) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="text-xs text-slate-500 bg-slate-50 border border-slate-200 p-3 rounded-lg leading-relaxed">
                ℹ️ नाम, विभाग, र पद भविष्यमा External API बाट स्वतः Sync हुने भएकोले, यहाँबाट सम्पादन गर्न मिल्दैन।            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">कर्मचारीको नाम</label>
                <div class="w-full border border-slate-200 bg-slate-100 rounded-lg px-3 py-2 text-sm text-slate-700 font-semibold">{{ $employee->name }}</div>            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Employee Code</label>
                <div class="w-full border border-slate-200 bg-slate-100 rounded-lg px-3 py-2 text-sm text-slate-700 font-mono font-semibold">{{ $employee->employee_code }}</div>            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">विभाग (Department)</label>
                <div class="w-full border border-slate-200 bg-slate-100 rounded-lg px-3 py-2 text-sm text-slate-700 font-semibold">{{ $employee->department }}</div>            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">पद (Position)</label>
                <div class="w-full border border-slate-200 bg-slate-100 rounded-lg px-3 py-2 text-sm text-slate-700 font-semibold">{{ $employee->position->name ?? 'N/A' }}</div>            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Hierarchy (Level भित्र क्रम)</label>
                <input type="number" name="hierarchy" value="{{ old('hierarchy', $employee->hierarchy) }}" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none" min="1">                <p class="text-[11px] text-slate-500 mt-1">एउटै Position Level भित्र, कुन कर्मचारी पहिले देखिने भन्ने क्रम (सानो number = माथि)।</p>            </div>

            <hr class="border-slate-200 my-6">

            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Petrol Bill / Repair Expense सम्बन्धी विवरण</h3>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Vehicle No</label>
                <input type="text" name="vehicle_no" value="{{ old('vehicle_no', $employee->vehicle_no) }}" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none uppercase" placeholder="जस्तै: बा १२ प ३४५६">                @if(empty($employee->vehicle_no))
                    <p class="text-[11px] text-rose-600 mt-1 flex items-center gap-1 font-medium">
                        <i class="fas fa-exclamation-triangle"></i> हाल Vehicle No खाली छ — यो नथपेसम्म यस कर्मचारीको Petrol Bill दर्ता गर्न मिल्दैन।
                    </p>                @endif
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Petrol Quantity Limit (महिनाको, लिटरमा)</label>
                <input type="number" name="petrol_quantity_limit" value="{{ old('petrol_quantity_limit', $employee->petrol_quantity_limit) }}" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none" min="0">            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Repair Expense Limit (FY Year को, रुपैयाँमा)</label>
                <input type="number" name="repair_expense_limit" value="{{ old('repair_expense_limit', $employee->repair_expense_limit) }}" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none" min="0">            </div>

            <div class="pt-4 flex items-center gap-3">
                <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white font-medium text-xs px-4 py-2.5 rounded-lg shadow-sm transition flex items-center gap-1.5">
                    <i class="fas fa-sync-alt"></i>
                    <span>अपडेट गर्नुहोस्</span>
                </button>
                <a href="{{ route('employees.index') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-xs px-4 py-2.5 rounded-lg border border-slate-300 transition text-center">
                    रद्द गर्नुहोस्
                </a>
            </div>
        </form>
    </div>
</div>
@endsection