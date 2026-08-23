@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Header & Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">🔧 Repair Expense सूची</h2>
            <p class="text-xs text-slate-500 mt-1">सबै मर्मत खर्च भौचर तथा प्रविष्टिको सूची</p>
        </div>
        <div class="flex items-center gap-2">
            <!-- Excel Download Button -->
            <a href="{{ route('repair.expenses.index', array_merge(request()->all(), ['export' => 'excel'])) }}" 
               class="bg-emerald-600 hover:bg-emerald-700 text-white px-3.5 py-2 rounded-lg text-xs font-semibold shadow-sm transition inline-flex items-center gap-1.5">
                <i class="fas fa-file-excel"></i>
                <span>Excel डाउनलोड</span>
            </a>
            
            <!-- Create Button -->
            <a href="{{ route('repair.expenses.create') }}" 
               class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg text-xs font-semibold shadow-sm transition inline-flex items-center gap-1.5">
                <i class="fas fa-plus"></i>
                <span>नयाँ Repair Expense थप्नुहोस्</span>
            </a>
        </div>
    </div>

    <!-- Flash Alerts -->
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

    <!-- Searchable Filter Form -->
    <form action="{{ route('repair.expenses.index') }}" method="GET" class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
        <div class="flex flex-col sm:flex-row gap-3 items-end">
            <div class="w-full sm:w-56">
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">FY Year</label>
                <select name="fy_year" class="w-full border border-slate-300 rounded-lg text-xs p-2">
                    <option value="">सबै</option>
                    @foreach($fyList as $fy)
                        <option value="{{ $fy }}" {{ request('fy_year') == $fy ? 'selected' : '' }}>{{ $fy }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full sm:w-56">
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Month</label>
                <select name="bs_month" class="w-full border border-slate-300 rounded-lg text-xs p-2">
                    <option value="">सबै</option>
                    @foreach($monthList as $m)
                        <option value="{{ $m }}" {{ request('bs_month') == $m ? 'selected' : '' }}>{{ $m }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white text-xs px-4 py-2 rounded-lg transition font-medium flex items-center gap-1 shadow-2xs">
                    <i class="fas fa-search"></i>
                    <span>खोज</span>
                </button>
                <a href="{{ route('repair.expenses.index') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 text-xs px-4 py-2 rounded-lg transition font-medium">
                    Reset
                </a>
            </div>
        </div>
    </form>

    <!-- Table -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left text-sm text-slate-700">
                <thead class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="p-3 text-center w-12">सि.नं.</th>
                        <th class="p-3">कर्मचारी कोड</th>
                        <th class="p-3">कर्मचारी</th>
                        <th class="p-3">पद</th>
                        <th class="p-3">FY Year</th>
                        <th class="p-3">Month</th>
                        <th class="p-3">मिति (BS)</th>
                        <th class="p-3">विवरण</th>
                        <th class="p-3 text-right">रकम</th>
                        <th class="p-3 text-center">Edit अनुमति</th>
                        <th class="p-3 text-center">कार्य</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @php $sn = 1; @endphp
                    @forelse($rows as $row)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="p-3 text-center text-slate-500 font-medium">{{ $sn++ }}</td>
                        <td class="p-3 font-mono text-slate-700">{{ $row['employee']->employee_code ?? '-' }}</td>
                        <td class="p-3 font-semibold text-slate-800">{{ $row['employee']->name ?? 'N/A' }}</td>
                        <td class="p-3 text-slate-600">{{ $row['employee']->position->name ?? 'N/A' }}</td>
                        <td class="p-3 text-slate-700">{{ $row['fy_year'] }}</td>
                        <td class="p-3 text-slate-700">{{ $row['bs_month'] }}</td>
                        <td class="p-3 text-slate-700">{{ $row['bs_date'] }}</td>
                        <td class="p-3 text-slate-700">{{ $row['description'] }}</td>
                        <td class="p-3 text-right font-semibold text-slate-900">रु {{ number_format($row['amount'], 2) }}</td>
                        <td class="p-3 text-center">
                            @if($row['isEdit'])
                                <span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-1 rounded-full text-xs font-semibold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> खुला
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 bg-slate-100 text-slate-600 border border-slate-200 px-2.5 py-1 rounded-full text-xs font-semibold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> बन्द
                                </span>
                            @endif
                        </td>
                        <td class="p-3 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('repair.expenses.print', $row['expense_id']) }}" target="_blank"
                                   class="bg-purple-600 text-white px-2.5 py-1.5 rounded-lg text-xs font-semibold hover:bg-purple-700 transition shadow-2xs" title="Print">
                                    Print
                                </a>
                                @if(auth()->user()->role === 'admin' || \App\Models\RolePermission::where('role', auth()->user()->role)->where('permission', 'repair.expenses.manage')->exists() || $row['isEdit'])
                                    <a href="{{ route('repair.expenses.edit', $row['expense_id']) }}"
                                       class="bg-sky-600 text-white px-2.5 py-1.5 rounded-lg text-xs font-semibold hover:bg-sky-700 transition shadow-2xs" title="Edit">
                                        Edit
                                    </a>
                                @endif
                                @if(auth()->user()->role === 'admin' || \App\Models\RolePermission::where('role', auth()->user()->role)->where('permission', 'repair.expenses.manage')->exists())
                                    <form action="{{ route('repair.expenses.toggleEdit', $row['expense_id']) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="{{ $row['isEdit'] ? 'bg-amber-600 hover:bg-amber-700' : 'bg-emerald-600 hover:bg-emerald-700' }} text-white px-2.5 py-1.5 rounded-lg text-xs font-semibold transition shadow-2xs">
                                            {{ $row['isEdit'] ? 'Edit बन्द' : 'Edit खोल्नुहोस्' }}
                                        </button>
                                    </form>
                                    <form action="{{ route('repair.expenses.destroy', $row['expense_id']) }}" method="POST" class="inline" onsubmit="return confirm('के तपाईं पक्का डिलिट गर्न चाहनुहुन्छ? (यसले यो पूरा entry - सबै मिति सहित - हटाउँछ)')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white px-2.5 py-1.5 rounded-lg text-xs font-semibold transition shadow-2xs">Delete</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="p-8 text-center text-slate-400">
                            <i class="fas fa-tools text-2xl mb-2 block text-slate-300"></i>
                            कुनै Repair Expense भेटिएन।
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection