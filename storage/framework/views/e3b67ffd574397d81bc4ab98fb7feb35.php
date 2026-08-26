

<?php $__env->startSection('content'); ?>
<div class="max-w-2xl mx-auto bg-white p-6 rounded shadow">
    <h1 class="text-2xl font-bold mb-4">HR System Sync</h1>
    <p class="text-gray-600 mb-6">यसले बाहिरी HR system बाट Staff, Department, र Position data तानेर हाम्रो system सँग मिलाउँछ।</p>

    <form action="<?php echo e(route('hr-sync.run')); ?>" method="POST" onsubmit="return confirm('सबै (Department + Position + Employee) Sync सुरु गर्ने? यसले केही समय लिन सक्छ।')" class="mb-3">
        <?php echo csrf_field(); ?>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold">
            🔄 सबै Sync गर्नुहोस् (Full Sync)
        </button>
    </form>

    <div class="flex flex-wrap gap-2 mb-6">
        <form action="<?php echo e(route('hr-sync.run-departments')); ?>" method="POST" onsubmit="return confirm('Departments मात्र Sync गर्ने?')">
            <?php echo csrf_field(); ?>
            <button type="submit" class="bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 px-4 py-2 rounded-lg text-sm font-medium">
                Departments मात्र
            </button>
        </form>
        <form action="<?php echo e(route('hr-sync.run-positions')); ?>" method="POST" onsubmit="return confirm('Positions मात्र Sync गर्ने?')">
            <?php echo csrf_field(); ?>
            <button type="submit" class="bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 px-4 py-2 rounded-lg text-sm font-medium">
                Positions मात्र
            </button>
        </form>
        <form action="<?php echo e(route('hr-sync.run-employees')); ?>" method="POST" onsubmit="return confirm('Employees मात्र Sync गर्ने? (यसले Department/Position पनि आवश्यक परे आफैं बनाउँछ)')">
            <?php echo csrf_field(); ?>
            <button type="submit" class="bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 px-4 py-2 rounded-lg text-sm font-medium">
                Employees मात्र
            </button>
        </form>
    </div>

    <div class="border-t pt-4 mb-6">
        <h3 class="font-semibold text-sm text-slate-700 mb-2">Public Holidays Import</h3>
        <form action="<?php echo e(route('hr-sync.run-holidays')); ?>" method="POST" onsubmit="return confirm('Holidays Sync गर्ने?')" class="flex items-end gap-2">
            <?php echo csrf_field(); ?>
            <div>
                <label class="block text-xs text-slate-500 mb-1">Fiscal Year (BS) - खाली छोड्न सकिन्छ</label>
                <input type="text" name="fiscal_year" placeholder="जस्तै 2083" class="border border-slate-300 rounded-lg text-sm px-3 py-2 w-40">
            </div>
            <button type="submit" class="bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 px-4 py-2 rounded-lg text-sm font-medium">
                Holidays Sync गर्नुहोस्
            </button>
        </form>
    </div>

    <?php if(session('summary')): ?>
        <?php $s = session('summary'); ?>
        <div class="mt-6 border-t pt-6">
            <h2 class="font-bold text-lg mb-3">Sync परिणाम <?php if(session('ran')): ?><span class="text-sm font-normal text-slate-500">(<?php echo e(session('ran')); ?> चलाइयो)</span><?php endif; ?></h2>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div class="bg-blue-50 p-3 rounded"><span class="font-semibold">Departments Synced:</span> <?php echo e($s['departments_synced']); ?></div>
                <div class="bg-blue-50 p-3 rounded"><span class="font-semibold">Positions Synced:</span> <?php echo e($s['positions_synced']); ?></div>
                <div class="bg-purple-50 p-3 rounded"><span class="font-semibold">Holidays Synced:</span> <?php echo e($s['holidays_synced'] ?? 0); ?></div>
                <div class="bg-green-50 p-3 rounded"><span class="font-semibold">नयाँ Employee:</span> <?php echo e($s['employees_created']); ?></div>
                <div class="bg-yellow-50 p-3 rounded"><span class="font-semibold">Update भएको Employee:</span> <?php echo e($s['employees_updated']); ?></div>
                <div class="bg-green-50 p-3 rounded"><span class="font-semibold">नयाँ User Account:</span> <?php echo e($s['users_created']); ?></div>
            </div>

            <?php if(count($s['errors']) > 0): ?>
                <div class="mt-4 bg-red-50 border border-red-200 p-3 rounded">
                    <p class="font-semibold text-red-700 mb-2">Errors (<?php echo e(count($s['errors'])); ?>):</p>
                    <ul class="text-sm text-red-600 list-disc list-inside">
                        <?php $__currentLoopData = $s['errors']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $err): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($err); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp\htdocs\overtime-system\resources\views/hr-sync/index.blade.php ENDPATH**/ ?>