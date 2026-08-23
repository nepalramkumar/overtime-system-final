<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('header', null, []); ?> 
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            <?php echo e(__('Dashboard')); ?>

        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="w-full sm:px-6 lg:px-8 space-y-6">

             
            <?php $notifications = auth()->user()->notifications->take(10); ?>
            <?php if($notifications->count() > 0): ?>
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-800 dark:text-gray-200 text-sm flex items-center gap-2">
                        <i class="fas fa-bell text-blue-600"></i>
                        Notifications
                        <?php if(auth()->user()->unreadNotifications->count() > 0): ?>
                            <span class="inline-flex items-center justify-center px-2 py-0.5 text-[11px] font-bold text-white bg-rose-600 rounded-full">
                                <?php echo e(auth()->user()->unreadNotifications->count()); ?> नयाँ
                            </span>
                        <?php endif; ?>
                    </h3>
                    <?php if(auth()->user()->unreadNotifications->count() > 0): ?>
                        <form method="POST" action="<?php echo e(route('notifications.mark-all-read')); ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="text-xs text-blue-600 hover:underline">सबै Read गर्नुहोस्</button>
                        </form>
                    <?php endif; ?>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    <?php $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notif): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <form method="POST" action="<?php echo e(route('notifications.read', $notif->id)); ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="w-full text-left px-6 py-3.5 flex items-start gap-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition
                                <?php echo e(is_null($notif->read_at) ? 'bg-blue-50/60 dark:bg-gray-900' : 'opacity-70'); ?>">
                                <span class="mt-1 w-2 h-2 rounded-full flex-shrink-0 <?php echo e(is_null($notif->read_at) ? 'bg-blue-500' : 'bg-gray-300'); ?>"></span>
                                <span class="flex-1">
                                    <span class="flex items-center gap-2">
                                        <span class="block text-sm <?php echo e(is_null($notif->read_at) ? 'font-bold text-gray-900 dark:text-white' : 'font-medium text-gray-500 dark:text-gray-400'); ?>"><?php echo e($notif->data['title'] ?? ''); ?></span>
                                        <?php if(is_null($notif->read_at)): ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-600 text-white">नयाँ</span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-gray-200 dark:bg-gray-600 text-gray-500 dark:text-gray-300">हेरिसकियो</span>
                                        <?php endif; ?>
                                    </span>
                                    <span class="block text-xs <?php echo e(is_null($notif->read_at) ? 'text-gray-700 dark:text-gray-300' : 'text-gray-400'); ?> mt-0.5"><?php echo e($notif->data['message'] ?? ''); ?></span>
                                    <span class="block text-[11px] text-gray-400 mt-1">
                                        <?php echo e($notif->created_at->format('Y-m-d h:i A')); ?> &middot; <?php echo e($notif->created_at->diffForHumans()); ?>

                                    </span>
                                </span>
                            </button>
                        </form>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if($isAdmin && $overall): ?>
            
            <div>
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">Overall — यस महिना</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <a href="<?php echo e(route('overtime.pending')); ?>" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 hover:shadow-md hover:border-blue-200 transition">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-gray-500">सिफारिस बाँकी OT</span>
                            <i class="fas fa-hourglass-half text-amber-500"></i>
                        </div>
                        <div class="text-2xl font-bold text-gray-800 mt-1"><?php echo e($overall['ot_pending_recommend']); ?></div>
                    </a>
                    <a href="<?php echo e(route('overtime.recommended')); ?>" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 hover:shadow-md hover:border-blue-200 transition">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-gray-500">स्वीकृति बाँकी OT</span>
                            <i class="fas fa-user-check text-blue-500"></i>
                        </div>
                        <div class="text-2xl font-bold text-gray-800 mt-1"><?php echo e($overall['ot_pending_approve']); ?></div>
                    </a>
                    <a href="<?php echo e(route('reports.index')); ?>" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 hover:shadow-md hover:border-blue-200 transition">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-gray-500">Verified OT घण्टा</span>
                            <i class="fas fa-check-circle text-emerald-500"></i>
                        </div>
                        <div class="text-2xl font-bold text-gray-800 mt-1"><?php echo e(number_format($overall['ot_hours_month'], 1)); ?></div>
                        <div class="text-[11px] text-gray-400"><?php echo e($overall['ot_verified_month']); ?> रेकर्ड</div>
                    </a>
                    <a href="<?php echo e(route('events.list')); ?>" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 hover:shadow-md hover:border-blue-200 transition">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-gray-500">सक्रिय Events</span>
                            <i class="fas fa-calendar-check text-blue-500"></i>
                        </div>
                        <div class="text-2xl font-bold text-gray-800 mt-1"><?php echo e($overall['events_active']); ?></div>
                    </a>
                    <a href="<?php echo e(route('petrol.bills.index')); ?>" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 hover:shadow-md hover:border-blue-200 transition">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-gray-500">Petrol खर्च</span>
                            <i class="fas fa-gas-pump text-orange-500"></i>
                        </div>
                        <div class="text-2xl font-bold text-gray-800 mt-1">रु. <?php echo e(number_format($overall['petrol_month_total'], 0)); ?></div>
                        <div class="text-[11px] text-gray-400"><?php echo e($overall['petrol_count_month']); ?> बिल</div>
                    </a>
                    <a href="<?php echo e(route('repair.expenses.index')); ?>" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 hover:shadow-md hover:border-blue-200 transition">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-gray-500">Repair खर्च</span>
                            <i class="fas fa-wrench text-orange-500"></i>
                        </div>
                        <div class="text-2xl font-bold text-gray-800 mt-1">रु. <?php echo e(number_format($overall['repair_month_total'], 0)); ?></div>
                        <div class="text-[11px] text-gray-400"><?php echo e($overall['repair_count_month']); ?> entry</div>
                    </a>
                </div>
            </div>
            <?php endif; ?>

            
            <?php if($myOt): ?>
            <div>
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">मेरो विवरण</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <a href="<?php echo e(route('overtime.my')); ?>" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 hover:shadow-md hover:border-blue-200 transition">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-gray-500">यस महिना OT घण्टा</span>
                            <i class="fas fa-clock text-blue-500"></i>
                        </div>
                        <div class="text-2xl font-bold text-gray-800 mt-1"><?php echo e(number_format($myOt['total_hours_month'], 1)); ?></div>
                    </a>
                    <a href="<?php echo e(route('overtime.my')); ?>" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 hover:shadow-md hover:border-blue-200 transition">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-gray-500">बाँकी (Pending) OT</span>
                            <i class="fas fa-hourglass-half text-amber-500"></i>
                        </div>
                        <div class="text-2xl font-bold text-gray-800 mt-1"><?php echo e($myOt['pending']); ?></div>
                    </a>
                    <a href="<?php echo e(route('overtime.my')); ?>" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 hover:shadow-md hover:border-blue-200 transition">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-gray-500">Verified OT</span>
                            <i class="fas fa-check-circle text-emerald-500"></i>
                        </div>
                        <div class="text-2xl font-bold text-gray-800 mt-1"><?php echo e($myOt['verified']); ?></div>
                    </a>
                    <a href="<?php echo e(route('overtime.my')); ?>" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 hover:shadow-md hover:border-blue-200 transition">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-gray-500">Rejected OT</span>
                            <i class="fas fa-times-circle text-rose-500"></i>
                        </div>
                        <div class="text-2xl font-bold text-gray-800 mt-1"><?php echo e($myOt['rejected']); ?></div>
                    </a>
                </div>
            </div>
            <?php endif; ?>

            
            <?php if($myRepairFy || $myPetrolPending): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php if($myPetrolPending): ?>
                <a href="<?php echo e(route('petrol.bills.create')); ?>" class="bg-white rounded-xl border <?php echo e($myPetrolPending['claimed'] ? 'border-gray-100' : 'border-amber-300'); ?> shadow-sm p-4 hover:shadow-md transition flex items-center justify-between">
                    <div>
                        <div class="text-xs font-semibold text-gray-500 mb-1">Petrol Claim — <?php echo e($myPetrolPending['month']); ?> <?php echo e($myPetrolPending['year']); ?></div>
                        <?php if($myPetrolPending['claimed']): ?>
                            <div class="text-sm font-bold text-emerald-600"><i class="fas fa-check-circle mr-1"></i> यो महिनाको Claim भइसक्यो</div>
                        <?php else: ?>
                            <div class="text-sm font-bold text-amber-600"><i class="fas fa-exclamation-circle mr-1"></i> <?php echo e($myPetrolPending['month']); ?> को Claim बाँकी छ</div>
                        <?php endif; ?>
                    </div>
                    <i class="fas fa-gas-pump text-2xl text-orange-400"></i>
                </a>
                <?php endif; ?>

                <?php if($myRepairFy): ?>
                <a href="<?php echo e(route('repair.expenses.index', ['employee_id' => auth()->user()->employee_id, 'fy_year' => $myRepairFy['fy_year']])); ?>"
                   class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 hover:shadow-md hover:border-blue-200 transition">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-semibold text-gray-500">Repair Expense — FY <?php echo e($myRepairFy['fy_year']); ?></span>
                        <i class="fas fa-wrench text-orange-400"></i>
                    </div>
                    <div class="text-sm">
                        <span class="font-bold text-gray-800">रु. <?php echo e(number_format($myRepairFy['claimed'], 0)); ?></span>
                        <span class="text-gray-400">claimed /</span>
                        <span class="font-bold <?php echo e($myRepairFy['remaining'] > 0 ? 'text-emerald-600' : 'text-rose-600'); ?>">रु. <?php echo e(number_format($myRepairFy['remaining'], 0)); ?> बाँकी</span>
                        <span class="text-gray-400">(Limit रु. <?php echo e(number_format($myRepairFy['limit'], 0)); ?>)</span>
                    </div>
                    <div class="text-[11px] text-blue-600 mt-1">कुन मितिमा के claim भयो हेर्नुहोस् →</div>
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            
            <div>
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">छिटो पहुँच (Quick Links)</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <a href="<?php echo e(route('overtime.create')); ?>" class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-3 rounded-lg shadow-sm transition">
                        <i class="fas fa-plus"></i> OT Entry
                    </a>
                    <a href="<?php echo e(route('events.list')); ?>" class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-3 rounded-lg shadow-sm transition">
                        <i class="fas fa-calendar-check"></i> Event OT
                    </a>
                    <a href="<?php echo e(route('petrol.bills.create')); ?>" class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-3 rounded-lg shadow-sm transition">
                        <i class="fas fa-gas-pump"></i> Petrol Entry
                    </a>
                    <a href="<?php echo e(route('repair.expenses.create')); ?>" class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-3 rounded-lg shadow-sm transition">
                        <i class="fas fa-wrench"></i> Repair Entry
                    </a>
                </div>
            </div>

       

        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php /**PATH D:\xampp\htdocs\overtime-system\resources\views/dashboard.blade.php ENDPATH**/ ?>