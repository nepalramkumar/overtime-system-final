

<?php $__env->startSection('content'); ?>
<div class="max-w-xl mx-auto">
    <!-- Form Title -->
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-bold text-gray-800">Overtime सच्याउनुहोस् (Edit)</h2>
        <p class="text-xs text-gray-500 mt-1">दर्ता भइसकेको ओभरटाइम विवरण परिमार्जन फारम</p>
    </div>

    <!-- Rejection Alert Box -->
    <?php if($record->status == 'Rejected'): ?>
        <div class="bg-red-50 border border-red-200 p-4 rounded-lg mb-5 shadow-sm text-sm">
            <div class="flex items-center gap-2 text-red-800 font-semibold">
                <i class="fas fa-exclamation-triangle"></i>
                <span>यो Record Reject भएको थियो।</span>
            </div>
            <?php if($record->rejection_reason): ?>
                <p class="text-red-700 text-xs mt-1 pl-6"><strong>कारण:</strong> <?php echo e($record->rejection_reason); ?></p>
            <?php endif; ?>
            <p class="text-gray-500 text-xs mt-2 pl-6 italic">
                * Note: सच्याएर Update गरेपछि यो फेरि सिफारिस/स्वीकृतिको प्रक्रियामा नयाँ बाट जानेछ।
            </p>
        </div>
    <?php endif; ?>

    <!-- Card Container -->
    <div class="bg-white p-6 rounded-lg border border-gray-100 shadow-sm">
        <form action="<?php echo e(route('overtime.update', $record->id)); ?>" method="POST" class="space-y-4">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            <!-- Employee Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Employee <span class="text-red-500">*</span>
                </label>
                <?php if($canSelectAny): ?>
                    <select name="employee_id" id="employee-select" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none text-sm" required>
                        <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($emp->id); ?>" <?php echo e((old('employee_id', $record->employee_id) == $emp->id) ? 'selected' : ''); ?>>
                                <?php echo e($emp->name); ?> (<?php echo e($emp->employee_code); ?>)
                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                <?php else: ?>
                    <div class="w-full px-3 py-2 border border-gray-200 rounded-lg bg-gray-50 text-gray-700 font-semibold text-sm flex items-center justify-between">
                        <span><?php echo e($record->employee->name ?? 'N/A'); ?> (<?php echo e($record->employee->employee_code ?? ''); ?>)</span>
                        <i class="fas fa-lock text-gray-400"></i>
                    </div>
                    <input type="hidden" name="employee_id" value="<?php echo e($record->employee_id); ?>">
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

            <!-- Event Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Event / Project</label>
                <?php if($canSelectAny): ?>
                    <select name="event_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none text-sm">
                        <option value="">-- सामान्य (General Purpose) --</option>
                        <?php $__currentLoopData = \App\Models\Event::where('is_active', true)->orWhere('id', $record->event_id)->orderBy('id', 'desc')->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($event->id); ?>" <?php echo e((old('event_id', $record->event_id) == $event->id) ? 'selected' : ''); ?>>
                                <?php echo e($event->event_name); ?><?php echo e(!$event->is_active ? ' (Disabled)' : ''); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                <?php else: ?>
                    <div class="w-full px-3 py-2 border border-gray-200 rounded-lg bg-gray-50 text-gray-700 font-medium text-sm">
                        <?php echo e($record->event->event_name ?? 'सामान्य (General Purpose)'); ?>

                    </div>
                    <input type="hidden" name="event_id" value="<?php echo e($record->event_id ?? ''); ?>">
                <?php endif; ?>
                <?php $__errorArgs = ['event_id'];
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

            <!-- Individual OT: सिफारिस/स्वीकृति गर्ने (केवल सामान्य/Individual OT को लागि आवश्यक, Event भए Event बाटै आउँछ) -->
            <div id="individual-approval-fields" class="grid grid-cols-1 sm:grid-cols-2 gap-4" style="<?php echo e($record->event_id ? 'display:none;' : ''); ?>">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">सिफारिस गर्ने (Recommender)</label>
                    <select name="recommender_employee_id" id="recommender-select" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none text-sm">
                        <option value="">-- छान्नुहोस् --</option>
                        <?php $__currentLoopData = $allEmployees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($emp->id); ?>" <?php echo e((old('recommender_employee_id', $record->recommender_employee_id) == $emp->id) ? 'selected' : ''); ?>><?php echo e($emp->name); ?></option>
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
                    <label class="block text-sm font-medium text-gray-700 mb-1">स्वीकृति गर्ने (Approver)</label>
                    <select name="approver_employee_id" id="approver-select" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none text-sm">
                        <option value="">-- छान्नुहोस् --</option>
                        <?php $__currentLoopData = $allEmployees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($emp->id); ?>" <?php echo e((old('approver_employee_id', $record->approver_employee_id) == $emp->id) ? 'selected' : ''); ?>><?php echo e($emp->name); ?></option>
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

            <!-- Purpose Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Purpose <span class="text-xs font-normal text-gray-500">(धेरै दिन चल्ने काम भए मात्र)</span>
                </label>
                <select name="purpose_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none text-sm">
                    <option value="">-- एक दिनको मात्र काम (Purpose चाहिँदैन) --</option>
                    <?php $__currentLoopData = \App\Models\Purpose::where('is_active', true)->orWhere('id', $record->purpose_id)->orderBy('id', 'desc')->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $purpose): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($purpose->id); ?>" <?php echo e((old('purpose_id', $record->purpose_id) == $purpose->id) ? 'selected' : ''); ?>>
                            <?php echo e($purpose->name); ?><?php echo e(!$purpose->is_active ? ' (Disabled)' : ''); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <?php $__errorArgs = ['purpose_id'];
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

            <!-- Date Input -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Date <span class="text-red-500">*</span>
                </label>
                <?php echo $__env->make('partials.bs-date-input', [
                    'name' => 'ot_date',
                    'value' => old('ot_date', $record->ot_date),
                    'class' => 'w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none text-sm cursor-pointer bg-white',
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
                        <option value="" <?php echo e(old('is_holiday_override') === null ? 'selected' : ''); ?>>Auto (Shift/Holiday table अनुसार गणना गर्ने)</option>
                        <option value="1" <?php echo e(old('is_holiday_override') === '1' ? 'selected' : ''); ?>>Force: यो बिदाको दिन हो</option>
                        <option value="0" <?php echo e(old('is_holiday_override') === '0' ? 'selected' : ''); ?>>Force: यो बिदाको दिन होइन</option>
                    </select>
                    <p class="text-[11px] text-amber-700 mt-1">अहिले यो record: <strong><?php echo e($record->is_holiday ? 'बिदाको दिन' : 'सामान्य दिन'); ?></strong> (गणना भइसकेको)। "Auto" छान्नुभयो भने फेरि Save गर्दा Shift/Holiday table अनुसार पुनः गणना हुन्छ।</p>
                </div>
            <?php endif; ?>

            <!-- Time Inputs -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        From Time <span class="text-red-500">*</span>
                    </label>
                    <input type="time" name="from_time" value="<?php echo e(old('from_time', $record->from_time)); ?>" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none text-sm" required>
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
                        To Time <span class="text-red-500">*</span>
                    </label>
                    <input type="time" name="to_time" value="<?php echo e(old('to_time', $record->to_time)); ?>" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none text-sm" required>
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
                <textarea name="remarks" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none text-sm" rows="2"><?php echo e(old('remarks', $record->remarks)); ?></textarea>
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

            <!-- Action Buttons -->
            <div class="flex items-center gap-3 pt-2">
                <a href="<?php echo e(url()->previous()); ?>" class="w-1/3 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium py-2.5 px-4 rounded-lg shadow-sm transition text-center text-sm">
                    रद्द गर्नुहोस्
                </a>
                <button type="submit" class="w-2/3 bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-2.5 px-4 rounded-lg shadow-sm transition duration-150 flex items-center justify-center gap-2 text-sm">
                    <i class="fas fa-save"></i>
                    <span>Update गर्नुहोस्</span>
                </button>
            </div>
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

        // Event छानिएको बेला Individual सिफारिस/स्वीकृति field लुकाउने (Event बाटै आउने भएकोले)
        const eventSelect = document.querySelector('select[name="event_id"]');
        const approvalFields = document.getElementById('individual-approval-fields');
        if (eventSelect && approvalFields) {
            const toggle = () => {
                approvalFields.style.display = eventSelect.value ? 'none' : '';
            };
            eventSelect.addEventListener('change', toggle);
            toggle();
        }
    });
</script>
<script src="<?php echo e(asset('js/bs-datepicker.js')); ?>"></script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp\htdocs\overtime-system\resources\views/overtime/edit.blade.php ENDPATH**/ ?>