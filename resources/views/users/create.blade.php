<x-app-layout>
    <div class="max-w-3xl mx-auto py-8">
        <div class="bg-white shadow-xl rounded-2xl overflow-hidden p-6">
            <h2 class="text-2xl font-bold mb-6">नयाँ प्रयोगकर्ता दर्ता</h2>

            @if ($errors->any())
                <div class="bg-red-100 text-red-600 p-3 rounded mb-4">
                    <ul>@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
                </div>
            @endif

            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label>नाम</label>
                        <input type="text" name="name" class="w-full border rounded-lg p-2" required>
                    </div>
                    <div>
                        <label>इमेल</label>
                        <input type="email" name="email" class="w-full border rounded-lg p-2" required>
                    </div>
                    <div>
                        <label>पासवर्ड</label>
                        <input type="password" name="password" class="w-full border rounded-lg p-2" required>
                    </div>
                    <div>
                        <label>भूमिका (Role)</label>
                        <select name="role" class="w-full border rounded-lg p-2">
                            <option value="employee">Employee</option>
                            <option value="manager">Manager</option>
                            <option value="admin">Admin</option>
                            <option value="account">Account</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label>कर्मचारी (छान्नुहोस्)</label>
                        <select name="employee_id" class="w-full border rounded-lg p-2">
                            <option value="">-- Non-Staff / Admin --</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <button type="submit" class="mt-6 px-6 py-2 bg-blue-600 text-white rounded-lg">युजर बनाउनुहोस्</button>
            </form>
        </div>
    </div>
</x-app-layout>