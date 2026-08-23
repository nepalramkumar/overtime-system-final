@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto space-y-6">

    <!-- Page Header -->
    <div>
        <h2 class="text-2xl font-bold text-slate-800">कर्मचारीको विवरण थप्नुहोस्</h2>
        <p class="text-xs text-slate-500 mt-1">नयाँ कर्मचारीको व्यक्तिगत तथा आधिकारिक विवरण दर्ता गर्नुहोस्</p>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <form action="{{ route('employees.store') }}" method="POST" class="space-y-4">
            @csrf
            
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">कर्मचारीको नाम</label>
                <input type="text" name="name" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none" required placeholder="पूरा नाम">  
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Employee Code</label>
                <input type="text" name="employee_code" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none font-mono" required placeholder="जस्तै: EMP001">  
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">विभाग (Department)</label>
                <input type="text" name="department" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none" required placeholder="जस्तै: HR Department">  
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">पद (Position)</label>
                <select name="position_id" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none" required>
                    <option value="">-- Position छान्नुहोस् --</option>  
                    @foreach($positions as $position)
                        <option value="{{ $position->id }}">{{ $position->name }}</option>  
                    @endforeach
                </select>
            </div>

            <div class="pt-4 flex items-center gap-3">
                <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white font-medium text-xs px-4 py-2.5 rounded-lg shadow-sm transition flex items-center gap-1.5">
                    <i class="fas fa-save"></i>
                    <span>सेभ गर्नुहोस्</span>
                </button>
                <a href="{{ route('employees.index') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-xs px-4 py-2.5 rounded-lg border border-slate-300 transition text-center">
                    रद्द गर्नुहोस्
                </a>
            </div>
        </form>
    </div>
</div>
@endsection