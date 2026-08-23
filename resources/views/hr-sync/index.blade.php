@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto bg-white p-6 rounded shadow">
    <h1 class="text-2xl font-bold mb-4">HR System Sync</h1>
    <p class="text-gray-600 mb-6">यसले बाहिरी HR system बाट Staff, Department, र Position data तानेर हाम्रो system सँग मिलाउँछ।</p>

    <form action="{{ route('hr-sync.run') }}" method="POST" onsubmit="return confirm('सबै (Department + Position + Employee) Sync सुरु गर्ने? यसले केही समय लिन सक्छ।')" class="mb-3">
        @csrf
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold">
            🔄 सबै Sync गर्नुहोस् (Full Sync)
        </button>
    </form>

    <div class="flex flex-wrap gap-2 mb-6">
        <form action="{{ route('hr-sync.run-departments') }}" method="POST" onsubmit="return confirm('Departments मात्र Sync गर्ने?')">
            @csrf
            <button type="submit" class="bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 px-4 py-2 rounded-lg text-sm font-medium">
                Departments मात्र
            </button>
        </form>
        <form action="{{ route('hr-sync.run-positions') }}" method="POST" onsubmit="return confirm('Positions मात्र Sync गर्ने?')">
            @csrf
            <button type="submit" class="bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 px-4 py-2 rounded-lg text-sm font-medium">
                Positions मात्र
            </button>
        </form>
        <form action="{{ route('hr-sync.run-employees') }}" method="POST" onsubmit="return confirm('Employees मात्र Sync गर्ने? (यसले Department/Position पनि आवश्यक परे आफैं बनाउँछ)')">
            @csrf
            <button type="submit" class="bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 px-4 py-2 rounded-lg text-sm font-medium">
                Employees मात्र
            </button>
        </form>
    </div>

    @if(session('summary'))
        @php $s = session('summary'); @endphp
        <div class="mt-6 border-t pt-6">
            <h2 class="font-bold text-lg mb-3">Sync परिणाम @if(session('ran'))<span class="text-sm font-normal text-slate-500">({{ session('ran') }} चलाइयो)</span>@endif</h2>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div class="bg-blue-50 p-3 rounded"><span class="font-semibold">Departments Synced:</span> {{ $s['departments_synced'] }}</div>
                <div class="bg-blue-50 p-3 rounded"><span class="font-semibold">Positions Synced:</span> {{ $s['positions_synced'] }}</div>
                <div class="bg-green-50 p-3 rounded"><span class="font-semibold">नयाँ Employee:</span> {{ $s['employees_created'] }}</div>
                <div class="bg-yellow-50 p-3 rounded"><span class="font-semibold">Update भएको Employee:</span> {{ $s['employees_updated'] }}</div>
                <div class="bg-green-50 p-3 rounded"><span class="font-semibold">नयाँ User Account:</span> {{ $s['users_created'] }}</div>
            </div>

            @if(count($s['errors']) > 0)
                <div class="mt-4 bg-red-50 border border-red-200 p-3 rounded">
                    <p class="font-semibold text-red-700 mb-2">Errors ({{ count($s['errors']) }}):</p>
                    <ul class="text-sm text-red-600 list-disc list-inside">
                        @foreach($s['errors'] as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    @endif
</div>
@endsection