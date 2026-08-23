@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Page Header & Action Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">कर्मचारी व्यवस्थापन</h2>
            <p class="text-xs text-slate-500 mt-1">संस्थाका कर्मचारीहरूको विवरण तथा उनीहरूको पद/विभाग सूची</p>
        </div>
        <a href="{{ route('employees.create') }}" class="inline-flex items-center justify-center gap-2 bg-amber-600 hover:bg-amber-700 text-white font-medium px-4 py-2.5 rounded-lg shadow-sm transition text-sm">
            <i class="fas fa-user-plus"></i>
            <span>नयाँ कर्मचारी थप्नुहोस्</span>
        </a>
    </div>

    <!-- Data Table Card -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm text-slate-700">
                <thead class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th scope="col" class="p-4">नाम</th>  
                        <th scope="col" class="p-4">पद (Designation)</th>  
                        <th scope="col" class="p-4">विभाग (Department)</th>  
                        <th scope="col" class="p-4 text-center">कार्य (Action)</th>  
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($employees as $emp)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="p-4 font-semibold text-slate-800">
                            {{ $emp->name ?? ($emp->user->name ?? 'N/A') }}  
                        </td>
                        <td class="p-4 text-slate-600">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-slate-100 text-slate-700">
                                {{ $emp->position->name ?? '—' }}  
                            </span>
                        </td>
                        <td class="p-4 text-slate-600">{{ $emp->department }}</td>  
                        <td class="p-4 text-center whitespace-nowrap">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('employees.edit', $emp->id) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 text-amber-600 hover:text-amber-800 hover:bg-amber-50 rounded-lg text-xs font-semibold transition" title="Edit">
                                    <i class="fas fa-edit"></i>
                                    <span>Edit</span>
                                </a>
                                <form action="{{ route('employees.destroy', $emp->id) }}" method="POST" class="inline" onsubmit="return confirm('के तपाईं पक्का हुनुहुन्छ?')">  
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1.5 text-rose-600 hover:text-rose-800 hover:bg-rose-50 rounded-lg text-xs font-semibold transition" title="Delete">
                                        <i class="fas fa-trash-alt"></i>
                                        <span>Delete</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="p-8 text-center text-slate-400">
                            <i class="fas fa-users-slash text-2xl mb-2 block text-slate-300"></i>
                            कुनै पनि कर्मचारीको विवरण फेला परेन।
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection