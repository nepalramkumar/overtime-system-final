<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['notif', 'compact' => false]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['notif', 'compact' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $type = class_basename($notif->type ?? '');

    // Notification को प्रकार अनुसार Icon + रङ — छिटो चिन्न मिल्ने बनाउन
    $meta = match ($type) {
        'PetrolBillReminderNotification'     => ['icon' => 'fa-gas-pump',      'color' => 'orange'],
        'RepairExpenseReminderNotification'  => ['icon' => 'fa-wrench',        'color' => 'amber'],
        'EventCreatedNotification'           => ['icon' => 'fa-calendar-plus', 'color' => 'purple'],
        'EventApprovedForEntryNotification'  => ['icon' => 'fa-calendar-check','color' => 'emerald'],
        'EventCreationRejectedNotification'  => ['icon' => 'fa-calendar-xmark','color' => 'rose'],
        'EventSubmittedNotification'         => ['icon' => 'fa-paper-plane',   'color' => 'blue'],
        'EventRecommendedNotification'       => ['icon' => 'fa-thumbs-up',     'color' => 'blue'],
        'EventRejectedNotification'          => ['icon' => 'fa-circle-xmark', 'color' => 'rose'],
        default                              => ['icon' => 'fa-bell',         'color' => 'slate'],
    };

    $colorMap = [
        'orange'  => ['bg' => 'bg-orange-50',  'text' => 'text-orange-600',  'ring' => 'ring-orange-100'],
        'amber'   => ['bg' => 'bg-amber-50',   'text' => 'text-amber-600',   'ring' => 'ring-amber-100'],
        'purple'  => ['bg' => 'bg-purple-50',  'text' => 'text-purple-600',  'ring' => 'ring-purple-100'],
        'emerald' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'ring' => 'ring-emerald-100'],
        'rose'    => ['bg' => 'bg-rose-50',    'text' => 'text-rose-600',    'ring' => 'ring-rose-100'],
        'blue'    => ['bg' => 'bg-blue-50',    'text' => 'text-blue-600',    'ring' => 'ring-blue-100'],
        'slate'   => ['bg' => 'bg-slate-100',  'text' => 'text-slate-500',   'ring' => 'ring-slate-100'],
    ];
    $c = $colorMap[$meta['color']];
    $isUnread = is_null($notif->read_at);
?>

<form method="POST" action="<?php echo e(route('notifications.read', $notif->id)); ?>" class="block">
    <?php echo csrf_field(); ?>
    <button type="submit"
            class="w-full text-left flex items-start gap-3 <?php echo e($compact ? 'px-4 py-3' : 'px-5 py-4'); ?> transition
                   <?php echo e($isUnread ? 'bg-blue-50/40 hover:bg-blue-50' : 'bg-white hover:bg-slate-50'); ?>">

        
        <span class="mt-0.5 flex-shrink-0 inline-flex items-center justify-center w-9 h-9 rounded-full <?php echo e($c['bg']); ?> <?php echo e($c['text']); ?> ring-4 <?php echo e($c['ring']); ?>">
            <i class="fas <?php echo e($meta['icon']); ?> text-xs"></i>
        </span>

        <span class="flex-1 min-w-0">
            <span class="flex items-center gap-2">
                <span class="text-sm truncate <?php echo e($isUnread ? 'font-bold text-slate-800' : 'font-medium text-slate-500'); ?>">
                    <?php echo e($notif->data['title'] ?? ''); ?>

                </span>
                <?php if($isUnread): ?>
                    <span class="flex-shrink-0 w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                <?php endif; ?>
            </span>
            <span class="block text-xs <?php echo e($isUnread ? 'text-slate-600' : 'text-slate-400'); ?> mt-0.5 leading-relaxed <?php echo e($compact ? 'line-clamp-2' : ''); ?>">
                <?php echo e($notif->data['message'] ?? ''); ?>

            </span>
            <span class="block text-[11px] text-slate-400 mt-1.5">
                <?php echo e($notif->created_at->diffForHumans()); ?>

            </span>
        </span>
    </button>
</form><?php /**PATH D:\xampp\htdocs\overtime-system\resources\views/components/notification-item.blade.php ENDPATH**/ ?>