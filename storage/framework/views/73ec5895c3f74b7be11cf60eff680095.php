

<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Filter Section -->
    <form action="<?php echo e(route('reports.index')); ?>" method="GET" class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-6 gap-3 items-end">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">रिपोर्ट प्रकार</label>
                <select name="group_by" class="w-full border border-slate-300 rounded-lg text-xs p-2.5">
                    <option value="employee" <?php echo e(request('group_by') == 'employee' ? 'selected' : ''); ?>>कर्मचारी अनुसार</option>
                    <option value="event" <?php echo e(request('group_by') == 'event' ? 'selected' : ''); ?>>कार्यक्रम अनुसार</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">From Date</label>
                <?php echo $__env->make('partials.bs-date-input', [
                    'name' => 'from_date',
                    'value' => request('from_date'),
                    'class' => 'w-full border border-slate-300 rounded-lg text-xs p-2.5 cursor-pointer bg-white',
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">To Date</label>
                <?php echo $__env->make('partials.bs-date-input', [
                    'name' => 'to_date',
                    'value' => request('to_date'),
                    'class' => 'w-full border border-slate-300 rounded-lg text-xs p-2.5 cursor-pointer bg-white',
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">कर्मचारी (नाम वा पद)</label>
                <input type="text" name="employee_search" list="employees_list" value="<?php echo e(request('employee_search')); ?>" placeholder="नाम वा पद टाइप गर्नुहोस्..." class="w-full border border-slate-300 rounded-lg text-xs p-2.5">
                <datalist id="employees_list">
                    <?php $__currentLoopData = \App\Models\Employee::with('position')->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($emp->name); ?> - <?php echo e($emp->position->name ?? 'N/A'); ?> (कोड: <?php echo e($emp->employee_code); ?>)">
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </datalist>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">कार्यक्रम (Event)</label>
                <input type="text" name="event_search" list="events_list" value="<?php echo e(request('event_search')); ?>" placeholder="कार्यक्रमको नाम टाइप गर्नुहोस्..." class="w-full border border-slate-300 rounded-lg text-xs p-2.5">
                <datalist id="events_list">
                    <?php $__currentLoopData = \App\Models\Event::orderBy('id', 'desc')->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($event->event_name); ?>">
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </datalist>
            </div>
            <div class="flex gap-2 items-center">
                <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white text-xs px-4 py-2.5 rounded-lg transition font-medium flex items-center gap-1 shadow-2xs">
                    <i class="fas fa-search"></i>
                    <span>खोज</span>
                </button>
                <a href="<?php echo e(route('reports.index')); ?>" class="bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 text-xs px-4 py-2.5 rounded-lg transition font-medium">
                    Reset
                </a>
            </div>
        </div>
    </form>

    <!-- View Toggle -->
    <div class="flex gap-2">
        <button type="button" id="btn-normal-view" onclick="showView('normal')"
            class="bg-slate-800 text-white px-4 py-2 rounded-lg text-xs font-semibold shadow-sm transition inline-flex items-center gap-1.5">
            <i class="fas fa-file-alt"></i> सामान्य View
        </button>
        <button type="button" id="btn-pivot-view" onclick="showView('pivot')"
            class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 px-4 py-2 rounded-lg text-xs font-semibold shadow-sm transition inline-flex items-center gap-1.5">
            <i class="fas fa-table"></i> Pivot View
        </button>
    </div>

    <!-- ============================== -->
    <!-- सामान्य (Normal) Table View -->
    <!-- ============================== -->
    <div id="normal-view">
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden mb-4">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left text-xs text-slate-700">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase tracking-wider font-semibold">
                        <tr>
                            <th class="p-3 text-center w-12">सि.नं.</th>
                            <th class="p-3">मिति</th>
                            <th class="p-3">कर्मचारी कोड</th>
                            <th class="p-3">कर्मचारी</th>
                            <th class="p-3">पद</th>
                            <th class="p-3">कार्यक्रम / कारण</th>
                            <th class="p-3 text-center">समय (From-To)</th>
                            <th class="p-3 text-center">घण्टा (HH:MM)</th>
                            <th class="p-3 text-center">घण्टा (Decimal)</th>
                            <th class="p-3 text-center">खाजा</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php $sn = 1; ?>
                        <?php $__empty_1 = true; $__currentLoopData = $groupedData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $empGroup): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php $__currentLoopData = $empGroup['events']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $eventGroup): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php $__currentLoopData = $eventGroup['records']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="p-3 text-center text-slate-500 font-medium"><?php echo e($sn++); ?></td>
                                    <td class="p-3 text-slate-700"><?php echo e(adToBs($rec->ot_date)); ?></td>
                                    <td class="p-3 font-mono text-slate-700"><?php echo e($empGroup['employee']->employee_code ?? '-'); ?></td>
                                    <td class="p-3 font-semibold text-slate-800"><?php echo e($empGroup['employee']->name ?? 'N/A'); ?></td>
                                    <td class="p-3 text-slate-600"><?php echo e($empGroup['employee']->position->name ?? 'N/A'); ?></td>
                                    <td class="p-3 text-slate-700">
                                        <?php echo e($rec->event->event_name ?? ($rec->remarks ?: 'सामान्य (General)')); ?>

                                        <?php if($rec->event): ?>
                                            <br><span class="text-[11px] text-slate-400">(<?php echo e(adToBs($rec->event->start_date)); ?> - <?php echo e(adToBs($rec->event->end_date)); ?>)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-3 text-center font-mono text-slate-700"><?php echo e($rec->from_time); ?> - <?php echo e($rec->to_time); ?></td>
                                    <td class="p-3 text-center font-mono text-slate-700"><?php echo e(hoursToHm($rec->total_hours)); ?></td>
                                    <td class="p-3 text-center font-mono text-slate-700"><?php echo e(number_format($rec->total_hours, 2)); ?></td>
                                    <td class="p-3 text-center font-mono text-slate-700"><?php echo e(number_format($rec->tiffin_amount, 2)); ?></td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="10" class="p-8 text-center text-slate-400">कुनै डेटा भेटिएन।</td></tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-800 text-white font-semibold">
                            <td colspan="8" class="p-3 text-right">कुल जम्मा (Grand Total)</td>
                            <td class="p-3 text-center font-mono"><?php echo e(number_format($totalHoursDecimalSum, 2)); ?></td>
                            <td class="p-3 text-center font-mono">रु <?php echo e(number_format($totalAmountSum, 2)); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div>
            <a href="<?php echo e(route('reports.excel', request()->all())); ?>" class="bg-emerald-600 hover:bg-emerald-700 text-white px-3.5 py-2 rounded-lg text-xs font-semibold shadow-sm transition inline-flex items-center gap-1.5">
                <i class="fas fa-file-excel"></i> Excel डाउनलोड
            </a>
        </div>
    </div>

    <!-- ============================== -->
    <!-- Pivot Table View -->
    <!-- ============================== -->
    <div id="pivot-view" style="display:none;" class="space-y-6">
        <?php if(count($pivotColumns) == 0): ?>
            <div class="bg-white border border-slate-200 rounded-xl p-8 text-center text-slate-400 shadow-sm">
                कुनै डेटा भेटिएन।
            </div>
        <?php else: ?>
            <div>
                <h3 class="text-sm font-bold text-slate-800 mb-2">कार्यक्रम अनुसार OT Hours Decimal (Programme-wise OT Hours Decimal)</h3>
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse text-xs text-slate-700">
                            <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase tracking-wider font-semibold">
                                <tr>
                                    <th class="p-2.5 border-r text-center w-12">सि.नं.</th>
                                    <!-- <th class="p-2.5 border-r text-center">मिति</th> -->
                                    <th class="p-2.5 border-r">कर्मचारी कोड</th>
                                    <th class="p-2.5 border-r">कर्मचारी</th>
                                    <th class="p-2.5 border-r">पद</th>
                                    <?php $__currentLoopData = $pivotColumns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $col): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <th class="p-2.5 border-r text-center"><?php echo e($col); ?></th>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php $sn = 1; ?>
                                <?php $__empty_1 = true; $__currentLoopData = $groupedData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $empId => $empGroup): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="p-2.5 border-r text-center text-slate-500"><?php echo e($sn++); ?></td>
                                    <!-- <td class="p-2.5 border-r text-center">-</td> -->
                                    <td class="p-2.5 border-r font-mono"><?php echo e($empGroup['employee']->employee_code ?? '-'); ?></td>
                                    <td class="p-2.5 border-r font-semibold text-slate-800"><?php echo e($empGroup['employee']->name ?? 'N/A'); ?></td>
                                    <td class="p-2.5 border-r text-slate-600"><?php echo e($empGroup['employee']->position->name ?? 'N/A'); ?></td>
                                    <?php $__currentLoopData = $pivotColumns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $col): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <td class="p-2.5 border-r text-center font-mono text-slate-700">
                                            <?php echo e(isset($pivotHours[$empId][$col]) ? number_format($pivotHours[$empId][$col], 2) : ''); ?>

                                        </td>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr><td colspan="<?php echo e(5 + count($pivotColumns)); ?>" class="p-8 text-center text-slate-400">कुनै डेटा भेटिएन।</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div>
                <h3 class="text-sm font-bold text-slate-800 mb-2">कार्यक्रम अनुसार खाजा रकम (Programme-wise Lunch Amount)</h3>
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse text-xs text-slate-700">
                            <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase tracking-wider font-semibold">
                                <tr>
                                    <th class="p-2.5 border-r text-center w-12">सि.नं.</th>
                                    <th class="p-2.5 border-r">कर्मचारी कोड</th>
                                    <th class="p-2.5 border-r">कर्मचारी</th>
                                    <th class="p-2.5 border-r">पद</th>
                                    <?php $__currentLoopData = $pivotColumns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $col): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <th class="p-2.5 border-r text-center"><?php echo e($col); ?></th>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php $sn = 1; ?>
                                <?php $__empty_1 = true; $__currentLoopData = $groupedData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $empId => $empGroup): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="p-2.5 border-r text-center text-slate-500"><?php echo e($sn++); ?></td>
                                    <td class="p-2.5 border-r font-mono"><?php echo e($empGroup['employee']->employee_code ?? '-'); ?></td>
                                    <td class="p-2.5 border-r font-semibold text-slate-800"><?php echo e($empGroup['employee']->name ?? 'N/A'); ?></td>
                                    <td class="p-2.5 border-r text-slate-600"><?php echo e($empGroup['employee']->position->name ?? 'N/A'); ?></td>
                                    <?php $__currentLoopData = $pivotColumns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $col): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <td class="p-2.5 border-r text-center font-mono text-slate-700">
                                            <?php echo e(isset($pivotLunch[$empId][$col]) ? number_format($pivotLunch[$empId][$col], 2) : ''); ?>

                                        </td>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr><td colspan="<?php echo e(4 + count($pivotColumns)); ?>" class="p-8 text-center text-slate-400">कुनै डेटा भेटिएन।</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div>
                <a href="<?php echo e(route('reports.exportPivot', request()->all())); ?>" class="bg-emerald-600 hover:bg-emerald-700 text-white px-3.5 py-2 rounded-lg text-xs font-semibold shadow-sm transition inline-flex items-center gap-1.5">
                    <i class="fas fa-file-excel"></i> Excel डाउनलोड (Pivot)
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function showView(view) {
    const normalDiv = document.getElementById('normal-view');
    const pivotDiv = document.getElementById('pivot-view');
    const btnNormal = document.getElementById('btn-normal-view');
    const btnPivot = document.getElementById('btn-pivot-view');

    if (view === 'pivot') {
        normalDiv.style.display = 'none';
        pivotDiv.style.display = 'block';
        btnPivot.className = "bg-slate-800 text-white px-4 py-2 rounded-lg text-xs font-semibold shadow-sm transition inline-flex items-center gap-1.5";
        btnNormal.className = "bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 px-4 py-2 rounded-lg text-xs font-semibold shadow-sm transition inline-flex items-center gap-1.5";
    } else {
        pivotDiv.style.display = 'none';
        normalDiv.style.display = 'block';
        btnNormal.className = "bg-slate-800 text-white px-4 py-2 rounded-lg text-xs font-semibold shadow-sm transition inline-flex items-center gap-1.5";
        btnPivot.className = "bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 px-4 py-2 rounded-lg text-xs font-semibold shadow-sm transition inline-flex items-center gap-1.5";
    }
}
</script>
<script src="<?php echo e(asset('js/bs-datepicker.js')); ?>"></script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp\htdocs\overtime-system\resources\views/reports/index.blade.php ENDPATH**/ ?>