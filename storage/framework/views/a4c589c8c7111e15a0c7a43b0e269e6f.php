

<?php $__env->startSection('content'); ?>
<div class="max-w-2xl mx-auto space-y-6">

    <!-- Page Header -->
    <div>
        <h2 class="text-2xl font-bold text-slate-800">कार्यक्रम (Event) दर्ता गर्नुहोस्</h2>
        <p class="text-xs text-slate-500 mt-1">नयाँ कार्यक्रम वा प्रोजेक्टको विवरण र समय सीमा दर्ता गर्नुहोस्</p>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <form action="<?php echo e(route('events.store')); ?>" method="POST" class="space-y-4">
            <?php echo csrf_field(); ?>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">कार्यक्रमको नाम</label>
                    <input type="text" name="event_name" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none" required>  
                </div>

   <div>
    <label class="block text-xs font-semibold text-slate-600 mb-1">विभाग</label>
    <select name="department" id="department-select" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none">
        <option value="">-- छान्नुहोस् --</option>
        <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($dept->name); ?>"><?php echo e($dept->name); ?></option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
</div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">सुरु हुने मिति</label>
                    <?php echo $__env->make('partials.bs-date-input', [
                        'name' => 'start_date',
                        'value' => old('start_date'),
                        'class' => 'w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none cursor-pointer bg-white',
                    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">सकिने मिति</label>
                    <?php echo $__env->make('partials.bs-date-input', [
                        'name' => 'end_date',
                        'value' => old('end_date'),
                        'class' => 'w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none cursor-pointer bg-white',
                    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">सुरु हुने समय</label>
                    <input type="time" name="start_time" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none">  
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">सकिने समय</label>
                    <input type="time" name="end_time" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none">  
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">सिफारिस गर्ने</label>
                <select name="recommender_employee_id" id="recommender-select" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none">
                    <option value="">-- छान्नुहोस् --</option>  
                    <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($emp->id); ?>">
                            <?php echo e($emp->name); ?> — <?php echo e($emp->position->name ?? ''); ?>

                        </option>  
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">स्वीकृत गर्ने (निर्देशनालय प्रमुख)</label>
                <select name="approver_employee_id" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none">
                    <option value="">-- छान्नुहोस् --</option>  
                    <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($emp->id); ?>">
                            <?php echo e($emp->name); ?> — <?php echo e($emp->position->name ?? ''); ?> (<?php echo e($emp->department); ?>)
                        </option>  
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            
            <div class="flex items-center gap-2.5 bg-slate-50 border border-slate-200 p-3.5 rounded-lg">
                <input type="checkbox" name="is_tiffin_eligible" value="1" class="w-4 h-4 text-purple-600 rounded border-slate-300 focus:ring-purple-500">  
                <label class="text-xs font-semibold text-slate-700">
                    यस कार्यक्रमको OT दाबी गर्दा खाजा खर्च गणना गर्ने हो?
                </label>  
            </div>
            
            <div class="pt-2">
                <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-medium text-xs py-2.5 rounded-lg shadow-sm transition flex items-center justify-center gap-1.5">
                    <i class="fas fa-save"></i>
                    <span>दर्ता गर्नुहोस्</span>
                </button>
            </div>
        </form>
    </div>
</div>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        ['department-select', 'recommender-select', 'approver-select'].forEach(function (id) {
            const el = document.getElementById(id);
            if (el) {
                new TomSelect('#' + id, {
                    create: false,
                    sortField: { field: "text", direction: "asc" }
                });
            }
        });
    });
</script>
<script src="<?php echo e(asset('js/bs-datepicker.js')); ?>"></script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp\htdocs\overtime-system\resources\views/events/create.blade.php ENDPATH**/ ?>