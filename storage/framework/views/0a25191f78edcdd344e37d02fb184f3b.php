
<?php
    $bsClass = $class ?? 'w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none cursor-pointer bg-white';
    $bsPlaceholder = $placeholder ?? 'YYYY-MM-DD (BS)';
    $bsRequired = $required ?? false;
?>
<div class="relative">
    <input type="text"
           id="<?php echo e($name); ?>_bs"
           class="bs-date-display <?php echo e($bsClass); ?>"
           placeholder="<?php echo e($bsPlaceholder); ?>"
           data-ad-target="<?php echo e($name); ?>_ad"
           <?php echo e($bsRequired ? 'required' : ''); ?>

           readonly
           autocomplete="off">
    <input type="hidden"
           name="<?php echo e($name); ?>"
           id="<?php echo e($name); ?>_ad"
           value="<?php echo e($value ?? ''); ?>">
</div>
<?php /**PATH D:\xampp\htdocs\overtime-system\resources\views/partials/bs-date-input.blade.php ENDPATH**/ ?>