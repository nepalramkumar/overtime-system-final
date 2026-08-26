

<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">📋 सिफारिस बाँकी रहेका Individual OT Records</h2>
            <p class="text-xs text-slate-500 mt-1">कर्मचारीहरूले पेश गरेका Individual (Event नभएका) ओभरटाइम सिफारिस वा अस्वीकृत गर्नुहोस्। Event-based OT यहाँ देखिँदैन — त्यो Events page बाटै batch मा हुन्छ।</p>
        </div>
    </div>

    <!-- Flash Notifications -->
    <?php if(session('success')): ?>
        <div class="bg-emerald-50 border border-emerald-200 p-4 rounded-xl shadow-sm text-sm flex items-center gap-2 text-emerald-800">
            <i class="fas fa-check-circle text-emerald-600 text-base"></i>
            <span><?php echo e(session('success')); ?></span>
        </div>
    <?php endif; ?>
    <?php if(session('error')): ?>
        <div class="bg-rose-50 border border-rose-200 p-4 rounded-xl shadow-sm text-sm flex items-center gap-2 text-rose-800">
            <i class="fas fa-exclamation-circle text-rose-600 text-base"></i>
            <span><?php echo e(session('error')); ?></span>
        </div>
    <?php endif; ?>

    <!-- Search & Filter Form -->
    <form action="<?php echo e(route('overtime.pending')); ?>" method="GET" class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">From Date</label>
                <?php echo $__env->make('partials.bs-date-input', [
                    'name' => 'from_date',
                    'value' => request('from_date'),
                    'class' => 'w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none cursor-pointer bg-white',
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">To Date</label>
                <?php echo $__env->make('partials.bs-date-input', [
                    'name' => 'to_date',
                    'value' => request('to_date'),
                    'class' => 'w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none cursor-pointer bg-white',
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">कर्मचारी</label>
                <select name="employee_id" id="employee-select" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                    <option value="">-- सबै --</option>
                    <?php $__currentLoopData = \App\Models\Employee::all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($emp->id); ?>" <?php echo e(request('employee_id') == $emp->id ? 'selected' : ''); ?>>
                            <?php echo e($emp->name); ?> <?php echo e($emp->employee_code ? '('.$emp->employee_code.')' : ''); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-medium text-xs px-4 py-2.5 rounded-lg shadow-sm transition flex items-center justify-center gap-1.5">
                    <i class="fas fa-search"></i>
                    <span>खोज्नुहोस्</span>
                </button>
                <a href="<?php echo e(route('overtime.pending')); ?>" class="w-full bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-xs px-4 py-2.5 rounded-lg border border-slate-300 transition text-center">
                    Reset
                </a>
            </div>
        </div>
    </form>

    <!-- Data Table Card -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm text-slate-700">
                <thead class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="p-3.5 text-center">सि.नं.</th>
                        <th class="p-3.5">कोड</th>
                        <th class="p-3.5">कर्मचारी</th>
                        <th class="p-3.5">पद</th>
                        <th class="p-3.5">मिति</th>
                        <th class="p-3.5 text-center">समय</th>
                        <th class="p-3.5 text-center">घण्टा</th>
                        <th class="p-3.5">कारण</th>
                        <th class="p-3.5 text-center">कार्य</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php $sn = 1; ?>
                    <?php $__empty_1 = true; $__currentLoopData = $records; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="p-3.5 text-center font-medium text-slate-500"><?php echo e($sn++); ?></td>
                            <td class="p-3.5 font-mono text-xs text-slate-600"><?php echo e($rec->employee->employee_code ?? '-'); ?></td>
                            <td class="p-3.5 font-semibold text-slate-800"><?php echo e($rec->employee->name ?? 'N/A'); ?></td>
                            <td class="p-3.5 text-slate-600"><?php echo e($rec->employee->position->name ?? 'N/A'); ?></td>
                            <td class="p-3.5 font-medium text-slate-800 whitespace-nowrap"><?php echo e(adToBs($rec->ot_date)); ?></td>
                            <td class="p-3.5 text-center text-slate-600 whitespace-nowrap text-xs"><?php echo e($rec->from_time); ?> - <?php echo e($rec->to_time); ?></td>
                            <td class="p-3.5 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-700">
                                    <?php echo e(number_format($rec->total_hours, 2)); ?> hrs
                                </span>
                            </td>
                            <td class="p-3.5 text-slate-800"><?php echo e($rec->remarks ?: '-'); ?></td>
                            <td class="p-3.5 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-2">
                                    <!-- Recommend Button -->
                                    <form action="<?php echo e(route('overtime.recommend', $rec->id)); ?>" method="POST" class="inline" onsubmit="return confirm('के तपाईं यो रेकर्ड सिफारिस गर्न चाहनुहुन्छ?')">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="inline-flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg text-xs font-medium transition shadow-sm">
                                            <i class="fas fa-check"></i> सिफारिस
                                        </button>
                                    </form>

                                    <!-- Reject Button Trigger -->
                                    <button type="button" onclick="document.getElementById('reject-modal-<?php echo e($rec->id); ?>').style.display='flex'"
                                            class="inline-flex items-center gap-1 bg-rose-600 hover:bg-rose-700 text-white px-3 py-1.5 rounded-lg text-xs font-medium transition shadow-sm">
                                        <i class="fas fa-times"></i> Reject
                                    </button>

                                    <!-- Reject Modal -->
                                    <div id="reject-modal-<?php echo e($rec->id); ?>" style="display:none;" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 items-center justify-center p-4">
                                        <div class="bg-white rounded-xl max-w-sm w-full p-5 shadow-xl text-left border border-slate-100">
                                            <h3 class="text-sm font-bold text-slate-800 mb-2">❌ Reject गर्ने कारण लेख्नुहोस्</h3>
                                            <form action="<?php echo e(route('overtime.reject', $rec->id)); ?>" method="POST">
                                                <?php echo csrf_field(); ?>
                                                <textarea name="reason" rows="3" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs focus:ring-2 focus:ring-rose-500 focus:border-rose-500 outline-none mb-4" placeholder="अस्वीकृत गर्नुको कारण..." required></textarea>
                                                <div class="flex justify-end gap-2">
                                                    <button type="button" onclick="document.getElementById('reject-modal-<?php echo e($rec->id); ?>').style.display='none'"
                                                            class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-1.5 rounded-lg text-xs font-medium transition">रद्द</button>
                                                    <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white px-3 py-1.5 rounded-lg text-xs font-medium transition">Reject</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="9" class="p-8 text-center text-slate-400">
                                <i class="fas fa-clipboard-check text-3xl mb-2 block text-slate-300"></i>
                                कुनै पनि सिफारिस बाँकी OT रेकर्ड भेटिएन।
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination Links -->
        <?php if(method_exists($records, 'links')): ?>
            <div class="p-4 border-t border-slate-200 bg-slate-50">
                <?php echo e($records->links()); ?>

            </div>
        <?php endif; ?>
    </div>

</div>

<!-- TomSelect Scripts -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Employee Select Initialization
        const empSelect = document.getElementById('employee-select');
        if (empSelect) {
            new TomSelect("#employee-select", {
                create: false,
                placeholder: "-- कर्मचारी छान्नुहोस् --",
                allowEmptyOption: true,
                sortField: { field: "text", direction: "asc" }
            });
        }
    });
</script>
<script src="<?php echo e(asset('js/bs-datepicker.js')); ?>"></script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp\htdocs\overtime-system\resources\views/overtime/pending.blade.php ENDPATH**/ ?>