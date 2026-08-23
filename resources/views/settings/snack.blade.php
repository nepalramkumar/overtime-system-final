@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto bg-white p-6 rounded shadow">
        <h1 class="text-2xl font-bold mb-6">मास्टर सेटिङ्स (Master Settings)</h1>

        <!-- Search Box -->
        <div class="mb-4">
            <input type="text" id="allowanceSearch" placeholder="घण्टा वा रकम खोज्नुहोस्..." class="border p-2 rounded w-full text-sm" onkeyup="filterTable('allowanceSearch', 'allowanceTable')">
        </div>

        <h2 class="text-xl font-semibold mb-4 text-gray-700">खाजा खर्च दर (Snack Allowance)</h2>
        <table id="allowanceTable" class="w-full border-collapse border border-gray-200 mb-8">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border p-2">अवधि (घण्टा)</th>
                    <th class="border p-2">रकम (रु.)</th>
                    <th class="border p-2" colspan="2">कार्य</th>
                </tr>
            </thead>
            <tbody>
                @foreach($allowances as $item)
                <tr>
                    <td class="border p-2 text-center">{{ $item->min_hours }} - {{ $item->max_hours }} घण्टा </td>
                    <td class="border p-2 text-center">
                        <form action="{{ route('settings.updateAllowance', $item->id) }}" method="POST" class="inline-flex items-center gap-2">
                            @csrf @method('PUT')
                            <input type="number" name="amount" value="{{ $item->amount }}" class="border p-1 w-24 text-center"> 
                            <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 text-sm">Update</button> 
                        </form>
                    </td>
                    <td class="border p-2 text-center">
                        <form action="{{ route('settings.destroyAllowance', $item->id) }}" method="POST" onsubmit="return confirm('के तपाईं पक्का डिलिट गर्न चाहनुहुन्छ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 text-sm">
                                Delete
                            </button> 
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- नयाँ दर थप्ने फर्म -->
    <div class="max-w-4xl mx-auto bg-gray-100 p-4 rounded mb-6 mt-4">
        <h3 class="font-bold mb-2">नयाँ खाजा खर्च दर थप्नुहोस्</h3>
        <form action="{{ route('settings.storeAllowance') }}" method="POST" class="flex gap-2">
            @csrf
            <input type="number" name="min_hours" placeholder="न्यूनतम घण्टा" step="any" class="border p-2 w-full" required>  
            <input type="number" name="max_hours" placeholder="अधिकतम घण्टा"  class="border p-2 w-full" required>  
            <input type="number" name="amount" placeholder="रकम (रु.)" class="border p-2 w-full" required>  
            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 whitespace-nowrap">थप्नुहोस्</button>  
        </form>
    </div>

    <script>
    function filterTable(inputId, tableId) {
        let input = document.getElementById(inputId);
        let filter = input.value.toLowerCase();
        let table = document.getElementById(tableId);
        let tr = table.getElementsByTagName("tr");
        for (let i = 1; i < tr.length; i++) {
            let td = tr[i].getElementsByTagName("td")[0];
            let td1 = tr[i].getElementsByTagName("td")[1];
            if (td || td1) {
                let txtValue = td.textContent || td.innerText;
                let txtValue1 = td1.textContent || td1.innerText;
                if (txtValue.toLowerCase().indexOf(filter) > -1 || txtValue1.toLowerCase().indexOf(filter) > -1) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }
    }
    </script>
@endsection