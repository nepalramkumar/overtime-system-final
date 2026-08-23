@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto bg-white p-6 rounded shadow">
        <h1 class="text-2xl font-bold mb-2">बिदा (Holiday) व्यवस्थापन</h1>
        <p class="text-xs text-slate-500 mb-6">यहाँ थपिएको मिति OT Entry गर्दा स्वतः "बिदाको दिन" मानिन्छ (शनि/आइतबार पहिल्यै Shift सेटिङबाट auto-detect हुन्छ, यो टेबल घोषित बिदा जस्तै दशैं, जयन्ती आदिको लागि हो)।</p>

        <!-- Add Holiday Form -->
        <form action="{{ route('holidays.store') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-4 gap-2 mb-6 bg-blue-50 p-4 rounded items-end">
            @csrf
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 mb-1">मिति</label>
                @include('partials.bs-date-input', [
                    'name' => 'date',
                    'value' => old('date'),
                    'required' => true,
                ])
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">बिदाको नाम</label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="जस्तै: नयाँ वर्ष" class="border p-2 w-full rounded text-sm" required>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">BS वर्ष (Optional)</label>
                <input type="number" name="bs_year" value="{{ old('bs_year') }}" placeholder="2083" class="border p-2 w-full rounded text-sm">
            </div>
            <div class="sm:col-span-4">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">थप्नुहोस्</button>
            </div>
        </form>

        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm p-3 rounded mb-4">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="bg-rose-50 border border-rose-200 text-rose-700 text-sm p-3 rounded mb-4">
                @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
            </div>
        @endif

        <!-- Year Filter -->
        @if($years->count() > 0)
        <div class="mb-4 flex items-center gap-2">
            <span class="text-xs font-semibold text-slate-600">वर्ष अनुसार फिल्टर:</span>
            <a href="{{ route('holidays.index') }}" class="text-xs px-2 py-1 rounded {{ !$year ? 'bg-slate-800 text-white' : 'bg-slate-100 text-slate-600' }}">सबै</a>
            @foreach($years as $y)
                <a href="{{ route('holidays.index', ['bs_year' => $y]) }}" class="text-xs px-2 py-1 rounded {{ (string)$year === (string)$y ? 'bg-slate-800 text-white' : 'bg-slate-100 text-slate-600' }}">{{ $y }}</a>
            @endforeach
        </div>
        @endif

        <!-- Holidays Table -->
        <table class="w-full border-collapse border border-gray-200 text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border p-2">मिति (AD)</th>
                    <th class="border p-2">मिति (BS)</th>
                    <th class="border p-2">बिदाको नाम</th>
                    <th class="border p-2">BS वर्ष</th>
                    <th class="border p-2">कार्य</th>
                </tr>
            </thead>
            <tbody>
                @forelse($holidays as $holiday)
                <tr>
                    <td class="border p-2 text-center whitespace-nowrap">{{ $holiday->date->format('Y-m-d') }}</td>
                    <td class="border p-2 text-center whitespace-nowrap">{{ function_exists('adToBs') ? adToBs($holiday->date->format('Y-m-d')) : '-' }}</td>
                    <td class="border p-2">{{ $holiday->name }}</td>
                    <td class="border p-2 text-center">{{ $holiday->bs_year ?: '-' }}</td>
                    <td class="border p-2 text-center">
                        <form action="{{ route('holidays.destroy', $holiday->id) }}" method="POST" onsubmit="return confirm('पक्का हटाउने हो?')" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded text-xs hover:bg-red-700">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="border p-4 text-center text-slate-400">कुनै बिदा थपिएको छैन।</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <script src="{{ asset('js/bs-datepicker.js') }}"></script>
@endsection
