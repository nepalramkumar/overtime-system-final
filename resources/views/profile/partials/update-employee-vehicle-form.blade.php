<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('मेरो कर्मचारी विवरण') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('यी जानकारी हेर्न मात्र मिल्छ। परिवर्तन गर्नुपर्ने भए Admin लाई सम्पर्क गर्नुहोस्।') }}
        </p>
    </header>

    <div class="mt-6 space-y-4">
        <div>
            <x-input-label :value="__('Employee Code')" />
            <div class="mt-1 block w-full border rounded-md px-3 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                {{ $employee->employee_code ?? '-' }}
            </div>
        </div>

        <div>
            <x-input-label :value="__('विभाग (Department)')" />
            <div class="mt-1 block w-full border rounded-md px-3 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                {{ $employee->department ?? '-' }}
            </div>
        </div>

        <div>
            <x-input-label :value="__('पद (Position)')" />
            <div class="mt-1 block w-full border rounded-md px-3 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                {{ $employee->position->name ?? '-' }}
            </div>
        </div>

        <div>
            <x-input-label :value="__('Level')" />
            <div class="mt-1 block w-full border rounded-md px-3 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                {{ $employee->position->level ?? '-' }}
            </div>
        </div>

        <div>
            <x-input-label :value="__('Hierarchy')" />
            <div class="mt-1 block w-full border rounded-md px-3 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                {{ $employee->hierarchy ?? '-' }}
            </div>
        </div>
    </div>

    <hr class="my-6 border-gray-200 dark:border-gray-700">

    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Vehicle / Petrol / Repair विवरण') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('आफ्नो Vehicle No यहाँबाट अपडेट गर्न सकिन्छ। Limit चाहिं Admin/अधिकार प्राप्त व्यक्तिले मात्र परिवर्तन गर्न सक्नुहुन्छ।') }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.vehicle.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="vehicle_no" :value="__('Vehicle No')" />
            <x-text-input id="vehicle_no" name="vehicle_no" type="text" class="mt-1 block w-full" :value="old('vehicle_no', $employee->vehicle_no)" placeholder="जस्तै: बा १२ प ३४५६" />
            <x-input-error class="mt-2" :messages="$errors->get('vehicle_no')" />
        </div>

        <div>
            <x-input-label :value="__('Petrol Quantity Limit (महिनाको, लिटरमा)')" />
            <div class="mt-1 block w-full border rounded-md px-3 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                {{ $employee->petrol_quantity_limit }} लिटर
            </div>
        </div>

        <div>
            <x-input-label :value="__('Repair Expense Limit (FY Year को, रुपैयाँमा)')" />
            <div class="mt-1 block w-full border rounded-md px-3 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                रु {{ number_format($employee->repair_expense_limit) }}
            </div>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'vehicle-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-gray-400"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
