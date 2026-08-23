<x-app-layout>
    <div class="p-6">
        
        <!-- सफलताको सन्देश देखाउने सेक्सन -->
        @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative shadow-sm">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative shadow-sm">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <div class="mb-4 text-right">
            <a href="{{ route('users.create') }}" class="bg-gray-600 text-white px-4 py-2 rounded font-bold hover:bg-gray-700">
                New User
            </a>
        </div>
        <h2 class="text-xl font-bold mb-4">प्रयोगकर्ता सूची</h2>
        <table class="w-full border bg-white shadow-sm rounded-lg">
            <thead>
                <tr class="bg-gray-200">
                    <th class="p-2 border">नाम</th>
                    <th class="p-2 border">इमेल</th>
                    <th class="p-2 border">रोल</th>
                    <th class="p-2 border">एक्सन</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td class="p-2 border">{{ $user->name }}</td>
                    <td class="p-2 border">{{ $user->email }}</td>
                    <td class="p-2 border">{{ $user->role }}</td>
                    <td class="p-2 border flex gap-2">
                        <a href="{{ route('users.edit', $user->id) }}" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 text-sm">
                            Edit
                        </a>

                        <form action="{{ route('users.destroy', $user->id) }}"
                              method="POST"
                              class="inline"
                              onsubmit="return confirm('के तपाईं यो युजर हटाउन निश्चित हुनुहुन्छ? अडचणी आउन सक्छ।');">
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
</x-app-layout>