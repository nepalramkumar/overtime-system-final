@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto bg-white p-6 rounded shadow">
        <h1 class="text-2xl font-bold mb-6">Position सेटिङ्स (Designation List & OT Rate)</h1>

        @if(session('success'))
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4">{{ session('success') }}</div>  
        @endif
        @if(session('error'))
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">{{ session('error') }}</div>  
        @endif

        <!-- Search Box -->
        <div class="mb-4">
            <input type="text" id="positionSearch" placeholder="पद (Position) खोज्नुहोस्..." class="border p-2 rounded w-full text-sm" onkeyup="filterPositions()">
        </div>

        <table id="positionTable" class="w-full border-collapse border border-gray-200 mb-8">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border p-2">पद (Position)</th>  
                    <th class="border p-2">Level (Hierarchy) & OT रेट</th>  
                    <th class="border p-2">कार्य</th>  
                </tr>
            </thead>
            <tbody>
                @foreach($positions as $item)
                <tr class="pos-row">
                    <td class="border p-2 text-center font-semibold pos-name">{{ $item->name }}  </td>
                    <td class="border p-2 text-center">
                        <form action="{{ route('positions.updateRate', $item->id) }}" method="POST" class="flex justify-center gap-2">
                            @csrf @method('PUT')
                            <input type="number" name="level" value="{{ $item->level }}" class="border p-1 w-20 text-center" placeholder="0">  
                            <input type="number" step="0.01" name="ot_rate" value="{{ $item->ot_rate }}" class="border p-1 w-24 text-center">  
                            <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 text-sm">Update</button>  
                        </form>
                    </td>
                    <td class="border p-2 text-center">
                        <form action="{{ route('positions.destroy', $item->id) }}" method="POST" onsubmit="return confirm('के तपाईं पक्का डिलिट गर्न चाहनुहुन्छ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 text-sm">Delete</button>  
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="max-w-4xl mx-auto bg-gray-100 p-4 rounded mb-6 mt-4">
        <h3 class="font-bold mb-2">नयाँ Position थप्नुहोस्</h3>
        <form action="{{ route('positions.store') }}" method="POST" class="flex gap-2">
            @csrf
            <input type="text" name="name" placeholder="Position नाम (जस्तै: Senior Developer)" class="border p-2 w-full" required>  
            <input type="number" name="level" placeholder="Level (जस्तै: 7)" class="border p-2 w-32">  
            <input type="number" step="0.01" name="ot_rate" placeholder="OT रेट (रु.)" class="border p-2 w-full">  
            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 whitespace-nowrap">थप्नुहोस्</button>  
        </form>
    </div>

    <script>
    function filterPositions() {
        let input = document.getElementById('positionSearch').value.toLowerCase();
        let rows = document.querySelectorAll('.pos-row');
        rows.forEach(row => {
            let text = row.querySelector('.pos-name').innerText.toLowerCase();
            row.style.display = text.includes(input) ? '' : 'none';
        });
    }
    </script>
@endsection