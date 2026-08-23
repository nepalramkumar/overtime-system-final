

<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">📋 मेरो OT Records</h2>
            <p class="text-xs text-gray-500 mt-1">तपाईंले प्रविष्टि गर्नुभएका ओभरटाइम विवरण तथा स्वीकृतिको अवस्था</p>
        </div>
        <a href="<?php echo e(route('overtime.create')); ?>" class="inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium px-4 py-2.5 rounded-lg shadow-sm transition text-sm">
            <i class="fas fa-plus"></i>
            <span>नयाँ OT Entry गर्नुहोस्</span>
        </a>
    </div>

    <!-- Flash Notifications -->
    <?php if(session('success')): ?>
        <div class="bg-emerald-50 border border-emerald-200 p-4 rounded-lg shadow-sm text-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-emerald-800">
            <div class="flex items-center gap-2">
                <i class="fas fa-check-circle text-emerald-600 text-base"></i>
                <span><?php echo e(session('success')); ?></span>
            </div>
            <?php if(session('last_event_id')): ?>
                <a href="<?php echo e(route('overtime.create', ['event_id' => session('last_event_id')])); ?>"
                   class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-lg text-xs font-semibold shadow-sm transition whitespace-nowrap">
                    <i class="fas fa-redo"></i>
                    <span>यही कार्यक्रममा अर्को Entry थप्नुहोस्</span>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="bg-red-50 border border-red-200 p-4 rounded-lg shadow-sm text-sm flex items-center gap-2 text-red-800">
            <i class="fas fa-exclamation-circle text-red-600 text-base"></i>
            <span><?php echo e(session('error')); ?></span>
        </div>
    <?php endif; ?>

    <!-- Search & Filter Form -->
    <form action="<?php echo e(route('overtime.my')); ?>" method="GET" class="bg-white border border-gray-100 rounded-lg p-4 shadow-sm">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">From Date</label>
                <?php echo $__env->make('partials.bs-date-input', [
                    'name' => 'from_date',
                    'value' => request('from_date'),
                    'class' => 'w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none cursor-pointer bg-white',
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">To Date</label>
                <?php echo $__env->make('partials.bs-date-input', [
                    'name' => 'to_date',
                    'value' => request('to_date'),
                    'class' => 'w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none cursor-pointer bg-white',
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">कार्यक्रम (Event)</label>
                <select name="event_id" id="event-select" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <option value="">-- सबै छान्नुहोस् --</option>
                    <?php $__currentLoopData = \App\Models\Event::orderBy('id', 'desc')->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($event->id); ?>" <?php echo e(request('event_id') == $event->id ? 'selected' : ''); ?>>
                            <?php echo e($event->event_name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="w-full bg-gray-800 hover:bg-gray-900 text-white font-medium text-xs px-4 py-2.5 rounded-lg shadow-sm transition flex items-center justify-center gap-1.5">
                    <i class="fas fa-search"></i>
                    <span>खोज्नुहोस्</span>
                </button>
                <a href="<?php echo e(route('overtime.my')); ?>" class="w-full bg-white hover:bg-gray-50 text-gray-700 font-medium text-xs px-4 py-2.5 rounded-lg border border-gray-300 transition text-center shadow-sm">
                    Reset
                </a>
            </div>
        </div>
    </form>

    <!-- Data Table Card -->
    <div class="bg-white rounded-lg border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm text-gray-700">
                <thead class="bg-gray-100 border-b border-gray-200 text-xs font-semibold text-gray-700 uppercase tracking-wider">
                    <tr>
                        <th class="p-3.5 text-center">सि.नं.</th>
                        <th class="p-3.5">कोड</th>
                        <th class="p-3.5">पद</th>
                        <th class="p-3.5">मिति</th>
                        <th class="p-3.5">कार्यक्रम / कारण</th>
                        <th class="p-3.5 text-center">समय</th>
                        <th class="p-3.5 text-center">घण्टा</th>
                        <th class="p-3.5 text-right">खाजा खर्च</th>
                        <th class="p-3.5 text-center">Status</th>
                        <th class="p-3.5 text-center">कार्य</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php $sn = 1; ?>
                    <?php $__empty_1 = true; $__currentLoopData = $records; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50/80 transition <?php echo e(session('highlight_id') == $rec->id ? 'bg-emerald-50/80 ring-2 ring-emerald-400' : ''); ?>">
                            <!-- Serial No -->
                            <td class="p-3.5 text-center font-medium text-gray-500"><?php echo e($sn++); ?></td>

                            <!-- Employee Code -->
                            <td class="p-3.5 font-mono text-xs text-gray-600">
                                <?php echo e(auth()->user()->employee->employee_code ?? '-'); ?>

                            </td>

                            <!-- Position -->
                            <td class="p-3.5 text-gray-600">
                                <?php echo e(auth()->user()->employee->position->name ?? 'N/A'); ?>

                            </td>

                            <!-- Date -->
                            <td class="p-3.5 font-medium text-gray-800 whitespace-nowrap">
                                <?php echo e(adToBs($rec->ot_date)); ?>

                            </td>

                            <!-- Event / Remarks -->
                            <td class="p-3.5 text-gray-800">
                                <?php echo e($rec->event->event_name ?? ($rec->remarks ?: 'सामान्य')); ?>

                            </td>

                            <!-- Time Slot -->
                            <td class="p-3.5 text-center text-gray-600 whitespace-nowrap text-xs">
                                <?php echo e($rec->from_time); ?> - <?php echo e($rec->to_time); ?>

                            </td>

                            <!-- Total Hours -->
                            <td class="p-3.5 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">
                                    <?php echo e(number_format($rec->total_hours, 2)); ?> hrs
                                </span>
                            </td>

                            <!-- Tiffin Amount -->
                            <td class="p-3.5 text-right font-semibold text-gray-800 whitespace-nowrap">
                                रु. <?php echo e(number_format($rec->tiffin_amount, 2)); ?>

                            </td>

                            <!-- Status Badge -->
                            <td class="p-3.5 text-center whitespace-nowrap">
                                <?php if($rec->status == 'Verified'): ?>
                                    <span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-1 rounded-full text-xs font-semibold">
                                        <i class="fas fa-check-circle text-xs"></i> Verified
                                    </span>
                                <?php elseif($rec->status == 'Rejected'): ?>
                                    <span class="inline-flex items-center gap-1 bg-red-50 text-red-700 border border-red-200 px-2.5 py-1 rounded-full text-xs font-semibold">
                                        <i class="fas fa-times-circle text-xs"></i> Rejected
                                    </span>
                                    <?php if($rec->rejection_reason): ?>
                                        <div class="text-[11px] text-red-600 mt-1 max-w-[150px] truncate" title="<?php echo e($rec->rejection_reason); ?>">
                                            कारण: <?php echo e($rec->rejection_reason); ?>

                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1 bg-amber-50 text-amber-700 border border-amber-200 px-2.5 py-1 rounded-full text-xs font-semibold">
                                        <i class="fas fa-clock text-xs"></i> Pending
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- Actions -->
                            <td class="p-3.5 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- Print -->
                                    <a href="<?php echo e(route('overtime.print', $rec->id)); ?>" target="_blank" 
                                       class="p-1.5 text-purple-600 hover:text-purple-800 hover:bg-purple-50 rounded-lg transition" 
                                       title="Print">
                                        <i class="fas fa-print"></i>
                                    </a>

                                    <?php if(in_array($rec->status, ['Pending', 'Rejected'])): ?>
                                        <!-- Edit -->
                                        <a href="<?php echo e(route('overtime.edit', $rec->id)); ?>" 
                                           class="p-1.5 text-emerald-600 hover:text-emerald-800 hover:bg-emerald-50 rounded-lg transition" 
                                           title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <!-- Delete -->
                                        <form action="<?php echo e(route('overtime.destroy', $rec->id)); ?>" method="POST" class="inline">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" 
                                                    class="p-1.5 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition" 
                                                    onclick="return confirm('के तपाईं पक्का यो रेकर्ड हटाउन चाहनुहुन्छ?')" 
                                                    title="Delete">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="10" class="p-8 text-center text-gray-400">
                                <i class="fas fa-folder-open text-3xl mb-2 block text-gray-300"></i>
                                कुनै पनि OT रेकर्ड भेटिएन।
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if(method_exists($records, 'links')): ?>
            <div class="p-4 border-t border-gray-200 bg-gray-50">
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
        const eventSelect = document.getElementById('event-select');
        if (eventSelect) {
            new TomSelect("#event-select", {
                create: false,
                placeholder: "-- सबै छान्नुहोस् --",
                allowEmptyOption: true,
                sortField: {
                    field: "text",
                    direction: "asc"
                }
            });
        }
    });
</script>
<script src="<?php echo e(asset('js/bs-datepicker.js')); ?>"></script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp\htdocs\overtime-system\resources\views/overtime/my.blade.php ENDPATH**/ ?>