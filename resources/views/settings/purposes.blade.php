@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto bg-white p-6 rounded shadow">
        <h1 class="text-2xl font-bold mb-6">Purpose सेटिङ्स (Multi-day General OT को लागि)</h1>

        @if(session('success'))
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4">{{ session('success') }}</div>  
        @endif
        @if(session('error'))
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">{{ session('error') }}</div>  
        @endif

        <!-- Search Box -->
        <div class="mb-4">
            <input type="text" id="purposeSearch" placeholder="Purpose नाम खोज्नुहोस्..." class="border p-2 rounded w-full text-sm" onkeyup="filterPurposes()">
        </div>

        <table id="purposeTable" class="w-full border-collapse border border-gray-200 mb-8">
            <thead class="bg-gray-100">
               <tr>
                    <th class="border p-2 text-left">Purpose नाम</th>  
                    <th class="border p-2">Status</th>  
                    <th class="border p-2 w-48">कार्य</th>  
                </tr>
            </thead>
            <tbody>
                @forelse($purposes as $item)
                <tr class="purp-row {{ !$item->is_active ? 'bg-gray-100 opacity-60' : '' }}">
                    <td class="border p-2 purp-name">{{ $item->name }}  </td>
                    <td class="border p-2 text-center">
                        @if($item->is_active)
                            <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-semibold">Active</span>  
                        @else
                            <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs font-semibold">Disabled</span>  
                        @endif
                    </td>
                    <td class="border p-2 text-center">
                        <a href="{{ route('purposes.print', $item->id) }}" target="_blank"
                           class="bg-purple-600 text-white px-3 py-1 rounded hover:bg-purple-700 text-sm inline-block mb-1">
                            Print
                        </a>  
                        <form action="{{ route('purposes.toggle', $item->id) }}" method="POST" class="inline" onsubmit="return confirm('के तपाईं यो Purpose को Status बदल्न चाहनुहुन्छ?')">
                            @csrf
                            <button type="submit" class="{{ $item->is_active ? 'bg-orange-500 hover:bg-orange-600' : 'bg-green-600 hover:bg-green-700' }} text-white px-3 py-1 rounded text-sm">
                                {{ $item->is_active ? 'Disable' : 'Enable' }}  
                            </button>
                        </form>
                        <form action="{{ route('purposes.destroy', $item->id) }}" method="POST" class="inline" onsubmit="return confirm('के तपाईं पक्का डिलिट गर्न चाहनुहुन्छ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 text-sm">Delete</button>  
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="3" class="text-center p-4 text-gray-500">कुनै Purpose थपिएको छैन।</td></tr>  
                @endforelse
            </tbody>
        </table>

        <div class="bg-gray-100 p-4 rounded">
            <h3 class="font-bold mb-2">नयाँ Purpose थप्नुहोस्</h3>
            <form action="{{ route('purposes.store') }}" method="POST" class="flex gap-2">
                @csrf
                <input type="text" name="name" placeholder="जस्तै: New ICAN ERP requirement analysis" class="border p-2 w-full" required>  
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 whitespace-nowrap">थप्नुहोस्</button>  
            </form>
        </div>
    </div>

    <script>
    function filterPurposes() {
        let input = document.getElementById('purposeSearch').value.toLowerCase();
        let rows = document.querySelectorAll('.purp-row');
        rows.forEach(row => {
            let text = row.querySelector('.purp-name').innerText.toLowerCase();
            row.style.display = text.includes(input) ? '' : 'none';
        });
    }
    </script>
@endsection