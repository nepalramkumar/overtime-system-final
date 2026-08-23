@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    <!-- Page Header & Create Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">📋 ओभरटाइम रेकर्डहरू</h2>
            <p class="text-xs text-gray-500 mt-1">सिस्टममा प्रविष्टि गरिएका सम्पूर्ण ओभरटाइम तथा खाजा खर्च विवरणहरू</p>
        </div>
        <a href="{{ route('overtime.create') }}" class="inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium px-4 py-2.5 rounded-lg shadow-sm transition text-sm">
            <i class="fas fa-plus"></i>
            <span>नयाँ OT थप्नुहोस्</span>
        </a>
    </div>

    <!-- Data Table Card -->
    <div class="bg-white rounded-lg border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm text-gray-700">
                <thead class="bg-gray-100 border-b border-gray-200 text-xs font-semibold text-gray-700 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3">मिति (Date)</th>
                        <th class="px-6 py-3">कर्मचारी (Employee)</th>
                        <th class="px-6 py-3">समय (Time)</th>
                        <th class="px-6 py-3 text-center">घण्टा (Hours)</th>
                        <th class="px-6 py-3 text-right">टिफिन (Amount)</th>
                        <th class="px-6 py-3 text-center">एक्सन (Action)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($records as $rec)
                        <tr class="hover:bg-gray-50/80 transition">
                            <!-- Date -->
                            <td class="px-6 py-4 font-medium text-gray-800 whitespace-nowrap">
                                <i class="far fa-calendar-alt text-gray-400 mr-1.5"></i>
                                {{ adToBs($rec->ot_date) }}
                            </td>

                            <!-- Employee Info -->
                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-800">{{ $rec->employee->name ?? 'N/A' }}</div>
                                @if(isset($rec->employee->employee_code))
                                    <div class="text-xs text-gray-500">ID: {{ $rec->employee->employee_code }}</div>
                                @endif
                            </td>

                            <!-- Time Slot -->
                            <td class="px-6 py-4 text-gray-600 whitespace-nowrap">
                                <i class="far fa-clock text-gray-400 mr-1"></i>
                                {{ $rec->from_time }} - {{ $rec->to_time }}
                            </td>

                            <!-- Total Hours Badge -->
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    {{ number_format($rec->total_hours, 2) }} hrs
                                </span>
                            </td>

                            <!-- Tiffin Amount -->
                            <td class="px-6 py-4 text-right font-semibold text-gray-800 whitespace-nowrap">
                                रु. {{ number_format($rec->tiffin_amount, 0) }}
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1">
                                    <!-- Edit Icon Button -->
                                    <a href="{{ route('overtime.edit', $rec->id) }}" 
                                       class="p-2 text-gray-500 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition" 
                                       title="Edit Record">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <!-- Delete Icon Form -->
                                    <form action="{{ route('overtime.destroy', $rec->id) }}" method="POST" class="inline">
                                        @csrf 
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition" 
                                                onclick="return confirm('के तपाईं पक्का यो रेकर्ड हटाउन चाहनुहुन्छ?')"
                                                title="Delete Record">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-gray-400">
                                <i class="fas fa-folder-open text-3xl mb-2 block text-gray-300"></i>
                                कुनै पनि ओभरटाइम रेकर्ड भेटिएन।
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Links -->
        @if(method_exists($records, 'links'))
            <div class="p-4 border-t border-gray-200 bg-gray-50">
                {{ $records->links() }}
            </div>
        @endif
    </div>

</div>
@endsection