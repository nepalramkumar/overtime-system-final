

<?php $__env->startSection('content'); ?>
<div class="max-w-xl mx-auto">
    <!-- Page Header -->
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-bold text-gray-800">Overtime & Tiffin Entry</h2>
        <p class="text-xs text-gray-500 mt-1">ओभरटाइम तथा खाजा खर्च प्रविष्टि फारम</p>
    </div>

    <!-- Card Container -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
        <form action="<?php echo e(route('overtime.store')); ?>" method="POST" class="space-y-4">
            <?php echo csrf_field(); ?>

            <!-- Employee Select -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Select Employee <span class="text-red-500">*</span>
                </label>
                <?php if($canSelectAny): ?>
                    <select name="employee_id" id="employee-select" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm shadow-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" required>
                        <option value="">-- नाम वा ID टाइप गरेर खोज्नुहोस् --</option>
                        <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($emp->id); ?>" <?php echo e(old('employee_id') == $emp->id ? 'selected' : ''); ?>>
                                <?php echo e($emp->name); ?> (ID: <?php echo e($emp->id); ?>) - <?php echo e($emp->designation ?? 'N/A'); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                <?php else: ?>
                    <div class="w-full px-3 py-2 border border-gray-200 rounded-lg bg-gray-50 text-gray-700 font-semibold text-sm flex items-center justify-between">
                        <span><?php echo e($lockedEmployee->name ?? 'N/A'); ?> (<?php echo e($lockedEmployee->employee_code ?? 'ID: ' . ($lockedEmployee->id ?? '')); ?>)</span>
                        <i class="fas fa-lock text-gray-400"></i>
                    </div>
                    <input type="hidden" name="employee_id" value="<?php echo e($lockedEmployee->id ?? ''); ?>">
                <?php endif; ?>
                <?php $__errorArgs = ['employee_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <!-- Event / OT Category Banner -->
            <?php if(!empty($selectedEventId) && isset($events)): ?>
                <?php
                    $currentEvent = $events->firstWhere('id', $selectedEventId);
                ?>
                <?php if($currentEvent): ?>
                    <div class="bg-emerald-50 p-3.5 rounded-lg border border-emerald-200">
                        <label class="block text-emerald-800 font-bold text-xs uppercase tracking-wide">Selected Event / Project</label>
                        <span class="text-gray-800 font-semibold text-base block mt-0.5"><?php echo e($currentEvent->event_name); ?> (<?php echo e($currentEvent->department); ?>)</span>
                        <input type="hidden" name="event_id" value="<?php echo e($currentEvent->id); ?>">
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="bg-gray-50 p-3.5 rounded-lg border border-gray-200">
                    <label class="block text-gray-500 font-bold text-xs uppercase tracking-wide">OT Category</label>
                    <span class="text-gray-700 font-semibold text-sm block mt-0.5">सामान्य प्रयोजन (General Purpose OT)</span>
                    <input type="hidden" name="event_id" value="">
                </div>

                <!-- Individual OT: सिफारिस/स्वीकृति गर्ने (Event मा जस्तै) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            सिफारिस गर्ने (Recommender) <span class="text-red-500">*</span>
                        </label>
                        <select name="recommender_employee_id" id="recommender-select" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm shadow-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" required>
                            <option value="">-- छान्नुहोस् --</option>
                            <?php $__currentLoopData = $allEmployees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($emp->id); ?>" <?php echo e(old('recommender_employee_id') == $emp->id ? 'selected' : ''); ?>><?php echo e($emp->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['recommender_employee_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            स्वीकृति गर्ने (Approver) <span class="text-red-500">*</span>
                        </label>
                        <select name="approver_employee_id" id="approver-select" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm shadow-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" required>
                            <option value="">-- छान्नुहोस् --</option>
                            <?php $__currentLoopData = $allEmployees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($emp->id); ?>" <?php echo e(old('approver_employee_id') == $emp->id ? 'selected' : ''); ?>><?php echo e($emp->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['approver_employee_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Date Input -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Date <span class="text-red-500">*</span>
                </label>
                <?php echo $__env->make('partials.bs-date-input', [
                    'name' => 'ot_date',
                    'value' => old('ot_date', date('Y-m-d')),
                    'class' => 'w-full px-3 py-2 border border-gray-300 rounded-lg text-sm shadow-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none cursor-pointer bg-white',
                    'required' => true,
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php $__errorArgs = ['ot_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <!-- Holiday: अब auto-calculate हुन्छ (Shift table + Holiday table अनुसार), Admin ले मात्र override गर्न पाउने -->
            <?php if(auth()->user()->role === 'admin'): ?>
                <div class="bg-amber-50 p-3 rounded-lg border border-amber-200/80">
                    <label class="block text-xs font-semibold text-amber-800 mb-1">बिदा (Holiday) — Admin Override</label>
                    <select name="is_holiday_override" class="w-full px-3 py-2 border border-amber-300 rounded-lg text-sm bg-white">
                        <option value="">Auto (Shift/Holiday table अनुसार गणना गर्ने)</option>
                        <option value="1">Force: यो बिदाको दिन हो</option>
                        <option value="0">Force: यो बिदाको दिन होइन</option>
                    </select>
                    <p class="text-[11px] text-amber-700 mt-1">सामान्यतया "Auto" नै छोड्नुहोस् — शनि/आइतबार र Holiday Settings मा थपिएका मिति स्वतः पत्ता लाग्छन्।</p>
                </div>
            <?php endif; ?>

            <!-- Time Inputs -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        From Time (सुरुको समय) <span class="text-red-500">*</span>
                    </label>
                    <input type="time" name="from_time" value="<?php echo e(old('from_time')); ?>" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm shadow-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" required>
                    <?php $__errorArgs = ['from_time'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        To Time (सकिने समय) <span class="text-red-500">*</span>
                    </label>
                    <input type="time" name="to_time" value="<?php echo e(old('to_time')); ?>" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm shadow-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" required>
                    <?php $__errorArgs = ['to_time'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>

            <!-- Remarks -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Remarks (कैफियत)</label>
                <textarea name="remarks" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm shadow-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" rows="2" placeholder="कामको संक्षिप्त विवरण..."><?php echo e(old('remarks')); ?></textarea>
                <?php $__errorArgs = ['remarks'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-2.5 px-4 rounded-lg shadow-sm transition duration-150 flex items-center justify-center gap-2 text-sm">
                <i class="fas fa-paper-plane"></i>
                <span>Submit Overtime</span>
            </button>
        </form>
    </div>
</div>

<!-- TomSelect Integration -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        ['employee-select', 'recommender-select', 'approver-select'].forEach(function (id) {
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
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp\htdocs\overtime-system\resources\views/overtime/create.blade.php ENDPATH**/ ?>