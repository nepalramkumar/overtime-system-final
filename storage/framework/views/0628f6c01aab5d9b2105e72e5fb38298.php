<?php $__env->startSection('content'); ?>
    <div class="max-w-4xl mx-auto bg-white p-6 rounded shadow">
        <h1 class="text-2xl font-bold mb-2">बिदा (Holiday) व्यवस्थापन</h1>
        <p class="text-xs text-slate-500 mb-6">यहाँ थपिएको मिति OT Entry गर्दा स्वतः "बिदाको दिन" मानिन्छ (शनि/आइतबार पहिल्यै Shift सेटिङबाट auto-detect हुन्छ, यो टेबल घोषित बिदा जस्तै दशैं, जयन्ती आदिको लागि हो)।</p>

        <!-- Add Holiday Form -->
        <form action="<?php echo e(route('holidays.store')); ?>" method="POST" class="grid grid-cols-1 sm:grid-cols-4 gap-2 mb-6 bg-blue-50 p-4 rounded items-end">
            <?php echo csrf_field(); ?>
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 mb-1">मिति</label>
                <?php echo $__env->make('partials.bs-date-input', [
                    'name' => 'date',
                    'value' => old('date'),
                    'required' => true,
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">बिदाको नाम</label>
                <input type="text" name="name" value="<?php echo e(old('name')); ?>" placeholder="जस्तै: नयाँ वर्ष" class="border p-2 w-full rounded text-sm" required>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">BS वर्ष (Optional)</label>
                <input type="number" name="bs_year" value="<?php echo e(old('bs_year')); ?>" placeholder="2083" class="border p-2 w-full rounded text-sm">
            </div>
            <div class="sm:col-span-4">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">थप्नुहोस्</button>
            </div>
        </form>

        <?php if(session('success')): ?>
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm p-3 rounded mb-4"><?php echo e(session('success')); ?></div>
        <?php endif; ?>
        <?php if($errors->any()): ?>
            <div class="bg-rose-50 border border-rose-200 text-rose-700 text-sm p-3 rounded mb-4">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <div><?php echo e($e); ?></div> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>

        <!-- Year Filter -->
        <?php if($years->count() > 0): ?>
        <div class="mb-4 flex items-center gap-2">
            <span class="text-xs font-semibold text-slate-600">वर्ष अनुसार फिल्टर:</span>
            <a href="<?php echo e(route('holidays.index')); ?>" class="text-xs px-2 py-1 rounded <?php echo e(!$year ? 'bg-slate-800 text-white' : 'bg-slate-100 text-slate-600'); ?>">सबै</a>
            <?php $__currentLoopData = $years; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $y): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('holidays.index', ['bs_year' => $y])); ?>" class="text-xs px-2 py-1 rounded <?php echo e((string)$year === (string)$y ? 'bg-slate-800 text-white' : 'bg-slate-100 text-slate-600'); ?>"><?php echo e($y); ?></a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <?php endif; ?>

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
                <?php $__empty_1 = true; $__currentLoopData = $holidays; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $holiday): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td class="border p-2 text-center whitespace-nowrap"><?php echo e($holiday->date->format('Y-m-d')); ?></td>
                    <td class="border p-2 text-center whitespace-nowrap"><?php echo e(function_exists('adToBs') ? adToBs($holiday->date->format('Y-m-d')) : '-'); ?></td>
                    <td class="border p-2"><?php echo e($holiday->name); ?></td>
                    <td class="border p-2 text-center"><?php echo e($holiday->bs_year ?: '-'); ?></td>
                    <td class="border p-2 text-center">
                        <form action="<?php echo e(route('holidays.destroy', $holiday->id)); ?>" method="POST" onsubmit="return confirm('पक्का हटाउने हो?')" class="inline">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded text-xs hover:bg-red-700">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="5" class="border p-4 text-center text-slate-400">कुनै बिदा थपिएको छैन।</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script src="<?php echo e(asset('js/bs-datepicker.js')); ?>"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp\htdocs\overtime-system\resources\views/settings/holidays.blade.php ENDPATH**/ ?>