

<?php $__env->startSection('content'); ?>
    <?php
        $dayOptions = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    ?>
    <div class="max-w-4xl mx-auto bg-white p-6 rounded shadow">
        <h1 class="text-2xl font-bold mb-6">कार्यालय सिफ्ट सेटिङ्स</h1>

        <!-- Add Shift Form -->
        <form action="<?php echo e(route('shifts.store')); ?>" method="POST" class="flex gap-2 mb-6 bg-blue-50 p-4 rounded">
            <?php echo csrf_field(); ?>
            <select name="day_name" class="border p-2 w-full rounded" required>
                <option value="">-- दिन छान्नुहोस् --</option>
                <?php $__currentLoopData = $dayOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($day); ?>"><?php echo e($day); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <input type="time" name="start_time" class="border p-2 rounded" required>
            <input type="time" name="end_time" class="border p-2 rounded" required>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 whitespace-nowrap">थप्नुहोस्</button>
        </form>

        <!-- Search Box -->
        <div class="mb-4">
            <input type="text" id="shiftSearch" placeholder="वार (Day) खोज्नुहोस्..." class="border p-2 rounded w-full text-sm">
        </div>

        <!-- Shifts Table -->
        <table id="shiftTable" class="w-full border-collapse border border-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border p-2">दिन</th>
                    <th class="border p-2">सुरु</th>
                    <th class="border p-2">अन्त्य</th>
                    <th class="border p-2">कार्य</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $shifts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shift): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="shift-row">
                    <td colspan="4" class="border p-0">
                        <div class="grid grid-cols-4 gap-2 p-2 items-center">
                            <!-- Update Form -->
                            <form action="<?php echo e(route('shifts.update', $shift->id)); ?>" method="POST" class="contents">
                                <?php echo csrf_field(); ?> 
                                <?php echo method_field('PUT'); ?>
                                <div>
                                    <select name="day_name" class="w-full p-1 border rounded shift-day" required>
                                        <?php $__currentLoopData = $dayOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($day); ?>" <?php echo e($shift->day_name === $day ? 'selected' : ''); ?>><?php echo e($day); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                                <div>
                                    <input type="time" name="start_time" value="<?php echo e(date('H:i', strtotime($shift->start_time))); ?>" class="w-full p-1 border rounded">
                                </div>
                                <div>
                                    <input type="time" name="end_time" value="<?php echo e(date('H:i', strtotime($shift->end_time))); ?>" class="w-full p-1 border rounded">
                                </div>
                                <div class="flex gap-2">
                                    <button type="submit" class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700">Save</button>
                            </form>
                                    <!-- Delete Form -->
                                    <form action="<?php echo e(route('shifts.destroy', $shift->id)); ?>" method="POST" onsubmit="return confirm('पक्का हो?')" class="inline">
                                        <?php echo csrf_field(); ?> 
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700">Delete</button>
                                    </form>
                                </div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>

    <script>
    document.getElementById('shiftSearch').addEventListener('keyup', function() {
        let input = this.value.toLowerCase();
        let rows = document.querySelectorAll('.shift-row');
        rows.forEach(row => {
            let selectBox = row.querySelector('.shift-day');
            let text = selectBox.value.toLowerCase();
            row.style.display = text.includes(input) ? '' : 'none';
        });
    });
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp\htdocs\overtime-system\resources\views/settings/shift.blade.php ENDPATH**/ ?>