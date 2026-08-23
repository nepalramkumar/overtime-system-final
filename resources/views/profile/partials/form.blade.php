@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto bg-white p-8 rounded-xl shadow-md">
    <h2 class="text-2xl font-bold mb-6 text-gray-800 text-center">
        {{ $expense ? 'Repair Expense Edit गर्नुहोस्' : 'नयाँ Repair Expense' }}
    </h2>

    @if(session('error'))
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
            {{ session('error') }}
            @if(session('vehicle_missing_employee_id'))
                <a href="{{ route('employees.edit', session('vehicle_missing_employee_id')) }}" target="_blank" class="underline font-semibold ml-1">यहाँबाट Vehicle No थप्नुहोस् →</a>
            @endif
        </div>
    @endif
    @if($errors->any())
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ $expense ? route('repair.expenses.update', $expense->id) : route('repair.expenses.store') }}" method="POST">
        @csrf
        @if($expense)
            @method('PUT')
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 font-semibold mb-1">कर्मचारी</label>
                @if($expense)
                    <div class="w-full p-2 border rounded bg-gray-100 text-gray-700 font-semibold">
                        {{ $expense->employee->name ?? 'N/A' }}
                    </div>
                @else
                    <select name="employee_id" id="employee_id" class="w-full p-2 border rounded" required>
                        <option value="">-- छान्नुहोस् --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" data-vehicle="{{ $emp->vehicle_no }}">{{ $emp->name }} ({{ $emp->employee_code }})</option>
                        @endforeach
                    </select>
                    <p id="vehicle-warning" class="text-xs text-red-600 mt-1 hidden">
                        ⚠ यस कर्मचारीको Vehicle No थपिएको छैन।
                        <a href="#" id="vehicle-warning-link" target="_blank" class="underline font-semibold">यहाँबाट थप्नुहोस् →</a>
                        (थपेपछि यो पेज Refresh गरेर फेरि कर्मचारी छान्नुहोस्)
                    </p>
                @endif
            </div>
            <div>
                <label class="block text-gray-700 font-semibold mb-1">FY Year</label>
                @if($expense)
                    <div class="w-full p-2 border rounded bg-gray-100 text-gray-700 font-semibold">
                        {{ $expense->fy_year }}
                    </div>
                @else
                    <select name="fy_year" class="w-full p-2 border rounded" required>
                        <option value="">-- छान्नुहोस् --</option>
                        @foreach($fyOptions as $fy)
                            <option value="{{ $fy }}">{{ $fy }}</option>
                        @endforeach
                    </select>
                @endif
            </div>
        </div>

        <h3 class="font-semibold text-gray-700 mb-2">Repair विवरण</h3>
        <table class="w-full border-collapse mb-2" id="rows-table">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border p-2 text-sm">मिति</th>
                    <th class="border p-2 text-sm">विवरण (के मर्मत गरियो)</th>
                    <th class="border p-2 text-sm">रकम</th>
                    <th class="border p-2 text-sm w-12"></th>
                </tr>
            </thead>
            <tbody id="rows-body">
                @php
                    $existingDates = $expense ? $expense->date : [now()->format('Y-m-d')];
                    $existingDesc  = $expense ? $expense->description : [''];
                    $existingAmt   = $expense ? $expense->amount : [''];
                @endphp
                @foreach($existingDates as $i => $d)
                <tr>
                    <td class="border p-1"><input type="date" name="date[]" value="{{ $d }}" class="w-full p-1 border rounded row-date" required></td>
                    <td class="border p-1"><input type="text" name="description[]" value="{{ $existingDesc[$i] ?? '' }}" class="w-full p-1 border rounded row-desc" required></td>
                    <td class="border p-1"><input type="number" step="0.01" name="amount[]" value="{{ $existingAmt[$i] ?? '' }}" class="w-full p-1 border rounded row-amount" required></td>
                    <td class="border p-1 text-center"><button type="button" class="text-red-600 font-bold remove-row">✕</button></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <button type="button" id="add-row" class="bg-gray-200 text-gray-700 px-3 py-1 rounded text-sm hover:bg-gray-300 mb-4">
            + थप पंक्ति थप्नुहोस्
        </button>

        <div class="mb-4">
            <label class="block text-gray-700 font-semibold mb-1">कैफियत</label>
            <textarea name="remarks" class="w-full p-2 border rounded" rows="2">{{ $expense->remarks ?? '' }}</textarea>
        </div>

        <button type="submit" class="w-full bg-blue-600 text-white p-2 rounded font-bold hover:bg-blue-700 transition">
            {{ $expense ? 'Update गर्नुहोस्' : 'Submit गर्नुहोस्' }}
        </button>
    </form>
</div>

<script>
document.getElementById('add-row').addEventListener('click', function () {
    const tbody = document.getElementById('rows-body');
    const row = document.createElement('tr');
    row.innerHTML = `
        <td class="border p-1"><input type="date" name="date[]" class="w-full p-1 border rounded row-date" required></td>
        <td class="border p-1"><input type="text" name="description[]" class="w-full p-1 border rounded row-desc" required></td>
        <td class="border p-1"><input type="number" step="0.01" name="amount[]" class="w-full p-1 border rounded row-amount" required></td>
        <td class="border p-1 text-center"><button type="button" class="text-red-600 font-bold remove-row">✕</button></td>
    `;
    tbody.appendChild(row);
});

document.getElementById('rows-body').addEventListener('click', function (e) {
    if (e.target.classList.contains('remove-row')) {
        if (document.querySelectorAll('#rows-body tr').length > 1) {
            e.target.closest('tr').remove();
        }
    }
});

// Employee छान्नासाथ Vehicle No नभएको जाँच गर्ने (create फारममा मात्र लागू हुन्छ)
const employeeSelect = document.getElementById('employee_id');
if (employeeSelect) {
    const vehicleWarning = document.getElementById('vehicle-warning');
    const vehicleWarningLink = document.getElementById('vehicle-warning-link');
    const submitBtn = document.querySelector('button[type="submit"]');
    const employeesEditBaseUrl = "{{ url('/employees') }}";

    employeeSelect.addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];
        const vehicleNo = selected ? selected.getAttribute('data-vehicle') : '';

        if (this.value && !vehicleNo) {
            vehicleWarning.classList.remove('hidden');
            vehicleWarningLink.href = employeesEditBaseUrl + '/' + this.value + '/edit';
            if (submitBtn) submitBtn.disabled = true;
            if (submitBtn) submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
        } else {
            vehicleWarning.classList.add('hidden');
            if (submitBtn) submitBtn.disabled = false;
            if (submitBtn) submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    });
}
</script>
@endsection
