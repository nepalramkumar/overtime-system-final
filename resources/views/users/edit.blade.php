<x-app-layout>
    <div class="max-w-3xl mx-auto py-8">
        <div class="bg-white shadow-xl rounded-2xl overflow-hidden">

            <!-- Header -->
            <div class="bg-green-600 px-6 py-4">
                <h2 class="text-2xl font-bold text-white">
                    प्रयोगकर्ता सम्पादन
                </h2>
                <p class="text-green-100 text-sm">
                    युजरको विवरण अद्यावधिक गर्नुहोस्
                </p>
            </div>

            <div class="p-6">
                <form action="{{ route('users.update', $user->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                        <!-- Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                नाम
                            </label>
                            <input type="text"
                                   name="name"
                                   value="{{ old('name', $user->name) }}"
                                   required
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-500">
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                इमेल
                            </label>
                            <input type="email"
                                   name="email"
                                   value="{{ old('email', $user->email) }}"
                                   required
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-500">
                        </div>

                        <!-- Role -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                भूमिका
                            </label>
                            <select name="role"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-500">
                                <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="manager" {{ old('role', $user->role) == 'manager' ? 'selected' : '' }}>Manager</option>
                                <option value="employee" {{ old('role', $user->role) == 'employee' ? 'selected' : '' }}>Employee</option>
                                <option value="account" {{ old('role', $user->role) == 'account' ? 'selected' : '' }}>Account</option>
                            </select>
                        </div>

                        <!-- Employee -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                कर्मचारी
                            </label>
                            <select name="employee_id"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-500">
                                <option value="">-- Non-Staff --</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}" {{ old('employee_id', $user->employee_id) == $emp->id ? 'selected' : '' }}>
                                        {{ $emp->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Password -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                नयाँ पासवर्ड
                            </label>
                            <input type="password"
                                   name="password"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-500"
                                   placeholder="परिवर्तन गर्न मात्र नयाँ पासवर्ड लेख्नुहोस्">
                            <p class="text-xs text-gray-500 mt-1">
                                खाली छोडेमा पुरानो पासवर्ड यथावत रहनेछ।
                            </p>
                        </div>

                    </div>

                    <!-- Buttons -->
                    <div class="flex justify-end gap-3 mt-8">
                        <a href="{{ route('users.index') }}"
                           class="px-5 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                            फिर्ता
                        </a>
                        <button type="submit"
                                class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 shadow-md">
                            ✓ अपडेट गर्नुहोस्
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </div>
</x-app-layout>