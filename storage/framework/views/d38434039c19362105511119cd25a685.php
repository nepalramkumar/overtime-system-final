<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">
                <?php echo e($expense ? '✏️ Repair Expense Edit गर्नुहोस्' : '🔧 नयाँ Repair Expense Entry'); ?>

            </h2>
            <p class="text-xs text-slate-500 mt-1">गाडी मर्मत खर्चको भौचर तथा विवरण प्रविष्टि</p>
        </div>
    </div>

    <!-- Flash & Error Alerts -->
    <?php if(session('error')): ?>
        <div class="bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-xl shadow-sm text-sm">
            <div class="flex items-center gap-2 font-medium">
                <i class="fas fa-exclamation-circle text-rose-600"></i>
                <span><?php echo e(session('error')); ?></span>
            </div>
            <?php if(session('vehicle_missing_employee_id')): ?>
                <div class="mt-2 text-xs">
                    <a href="<?php echo e(route('employees.edit', session('vehicle_missing_employee_id'))); ?>" target="_blank"
                       class="inline-flex items-center gap-1 font-semibold text-rose-700 underline hover:text-rose-900">
                        यहाँबाट Vehicle No थप्नुहोस् →
                    </a>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-xl shadow-sm text-sm">
            <div class="font-semibold mb-1 text-xs">कृपया तलका त्रुटिहरू सच्याउनुहोस्:</div>
            <ul class="list-disc list-inside space-y-1 text-xs">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Form Container -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6 sm:p-8">
        <form action="<?php echo e($expense ? route('repair.expenses.update', $expense->id) : route('repair.expenses.store')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <?php if($expense): ?>
                <?php echo method_field('PUT'); ?>
            <?php endif; ?>

            <!-- Main Inputs Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
                
                <!-- Employee Selection -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">कर्मचारी <span class="text-rose-500">*</span></label>
                    <?php if($expense): ?>
                        <div class="w-full p-2.5 bg-slate-100 border border-slate-200 rounded-lg text-slate-700 font-medium text-sm">
                            <?php echo e($expense->employee->name ?? 'N/A'); ?> (<?php echo e($expense->employee->employee_code ?? ''); ?>)
                        </div>
                        <input type="hidden" name="employee_id" value="<?php echo e($expense->employee_id); ?>">
                    <?php elseif($canSelectAny): ?>
                        <select name="employee_id" id="employee_id" class="w-full border border-slate-300 rounded-lg text-sm" required>
                            <option value="">-- कर्मचारी छान्नुहोस् --</option>
                            <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($emp->id); ?>" data-vehicle="<?php echo e($emp->vehicle_no); ?>">
                                    <?php echo e($emp->name); ?> (<?php echo e($emp->employee_code); ?>)
                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    <?php else: ?>
                        <div class="w-full p-2.5 bg-slate-100 border border-slate-200 rounded-lg text-slate-700 font-medium text-sm">
                            <?php echo e($lockedEmployee->name ?? 'N/A'); ?> (<?php echo e($lockedEmployee->employee_code ?? ''); ?>)
                        </div>
                        <input type="hidden" name="employee_id" value="<?php echo e($lockedEmployee->id ?? ''); ?>">
                    <?php endif; ?>

                    <div id="vehicle-warning" class="hidden bg-amber-50 border border-amber-200 text-amber-800 p-3 rounded-lg mt-2 text-xs flex flex-col gap-1">
                        <div class="flex items-center justify-between">
                            <span>⚠️ यस कर्मचारीको Vehicle No थपिएको छैन।</span>
                            <a id="vehicle-warning-link" href="#" target="_blank" class="underline font-bold text-amber-900 hover:text-black">यहाँबाट थप्नुहोस् →</a>
                        </div>
                        <span class="text-[11px] text-amber-700">(थपेपछि यो पेज Refresh गरेर फेरि कर्मचारी छान्नुहोस्)</span>
                    </div>
                </div>

                <!-- FY Year Selection -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">FY Year <span class="text-rose-500">*</span></label>
                    <?php if($expense): ?>
                        <div class="w-full p-2.5 bg-slate-100 border border-slate-200 rounded-lg text-slate-700 font-medium text-sm">
                            <?php echo e($expense->fy_year); ?>

                        </div>
                        <input type="hidden" name="fy_year" value="<?php echo e($expense->fy_year); ?>">
                    <?php else: ?>
                        <select name="fy_year" id="fy_year" class="w-full border border-slate-300 rounded-lg text-sm" required>
                            <option value="">-- FY Year छान्नुहोस् --</option>
                            <?php $__currentLoopData = $fyOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fy): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($fy); ?>"><?php echo e($fy); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    <?php endif; ?>
                </div>

            </div>

            <!-- Dynamic Table Header -->
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-semibold text-slate-800 text-sm">🔧 Repair विवरण</h3>
                <button type="button" id="add-row" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-xs px-3 py-1.5 rounded-lg border border-slate-300 transition inline-flex items-center gap-1 shadow-2xs">
                    <i class="fas fa-plus text-[10px]"></i>
                    <span>थप पंक्ति थप्नुहोस्</span>
                </button>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto border border-slate-200 rounded-xl shadow-sm mb-6">
                <table class="w-full border-collapse text-left text-xs text-slate-700" id="rows-table">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase tracking-wider font-semibold">
                        <tr>
                            <th class="p-3 w-36">मिति</th>
                            <th class="p-3">विवरण (के मर्मत गरियो)</th>
                            <th class="p-3 w-40">रकम (रु.)</th>
                            <th class="p-3 text-center w-12">कार्य</th>
                        </tr>
                    </thead>
                    <tbody id="rows-body" class="divide-y divide-slate-100">
                        <?php
                            $existingDates = old('date') ?? ($expense ? $expense->date : [now()->format('Y-m-d')]);
                            $existingDesc  = old('description') ?? ($expense ? $expense->description : ['']);
                            $existingAmt   = old('amount') ?? ($expense ? $expense->amount : ['']);
                        ?>
                        <?php $__currentLoopData = $existingDates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="p-2">
                                <input type="date" name="date[]" value="<?php echo e($d); ?>" class="w-full p-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none row-date" required>
                            </td>
                            <td class="p-2">
                                <input type="text" name="description[]" value="<?php echo e($existingDesc[$i] ?? ''); ?>" placeholder="विवरण लेख्नुहोस्..." class="w-full p-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none row-desc" required>
                            </td>
                            <td class="p-2">
                                <input type="number" step="0.01" name="amount[]" value="<?php echo e($existingAmt[$i] ?? ''); ?>" placeholder="0.00" class="w-full p-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none row-amount" required>
                            </td>
                            <td class="p-2 text-center">
                                <button type="button" class="text-rose-500 hover:text-rose-700 p-1.5 rounded-lg hover:bg-rose-50 transition remove-row" title="हटाउनुहोस्">
                                    <i class="fas fa-trash-alt text-xs"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>

            <!-- Remarks -->
            <div class="mb-6">
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">कैफियत (Remarks)</label>
                <textarea name="remarks" class="w-full p-2.5 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" rows="2" placeholder="आवश्यकता अनुसार कैफियत लेख्नुहोस्..."><?php echo e($expense->remarks ?? ''); ?></textarea>
            </div>

            <!-- Submit Button -->
            <button type="submit" id="submit-btn" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-medium p-3 rounded-lg text-xs shadow-sm transition flex items-center justify-center gap-1.5">
                <i class="fas fa-save"></i>
                <span><?php echo e($expense ? 'Update गर्नुहोस्' : 'Submit गर्नुहोस्'); ?></span>
            </button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add Row Logic
    const addBtn = document.getElementById('add-row');
    if (addBtn) {
        addBtn.addEventListener('click', function () {
            const tbody = document.getElementById('rows-body');
            const row = document.createElement('tr');
            row.className = 'hover:bg-slate-50/50 transition';
            row.innerHTML = `
                <td class="p-2">
                    <input type="date" name="date[]" value="<?php echo e(now()->format('Y-m-d')); ?>" class="w-full p-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none row-date" required>
                </td>
                <td class="p-2">
                    <input type="text" name="description[]" placeholder="विवरण लेख्नुहोस्..." class="w-full p-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none row-desc" required>
                </td>
                <td class="p-2">
                    <input type="number" step="0.01" name="amount[]" placeholder="0.00" class="w-full p-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none row-amount" required>
                </td>
                <td class="p-2 text-center">
                    <button type="button" class="text-rose-500 hover:text-rose-700 p-1.5 rounded-lg hover:bg-rose-50 transition remove-row" title="हटाउनुहोस्">
                        <i class="fas fa-trash-alt text-xs"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    // Remove Row
    document.getElementById('rows-body').addEventListener('click', function (e) {
        const removeBtn = e.target.closest('.remove-row');
        if (removeBtn) {
            if (document.querySelectorAll('#rows-body tr').length > 1) {
                removeBtn.closest('tr').remove();
            }
        }
    });

    // Employee Vehicle Warning Check
    const employeeSelect = document.getElementById('employee_id');
    if (employeeSelect) {
        const vehicleWarning = document.getElementById('vehicle-warning');
        const vehicleWarningLink = document.getElementById('vehicle-warning-link');
        const submitBtn = document.getElementById('submit-btn');
        const isSelfEntry = <?php echo e($canSelectAny ? 'false' : 'true'); ?>;
        const profileUrl = "<?php echo e(route('profile.edit')); ?>";
        const employeesEditBaseUrl = "<?php echo e(url('/employees')); ?>";

        employeeSelect.addEventListener('change', function () {
            const selected = this.options[this.selectedIndex];
            const vehicleNo = selected ? selected.getAttribute('data-vehicle') : '';

            if (this.value && !vehicleNo) {
                vehicleWarning.classList.remove('hidden');
                vehicleWarningLink.href = isSelfEntry ? profileUrl : (employeesEditBaseUrl + '/' + this.value + '/edit');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                }
            } else {
                vehicleWarning.classList.add('hidden');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                }
            }
        });
    }
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp\htdocs\overtime-system\resources\views/repair/expenses/form.blade.php ENDPATH**/ ?>