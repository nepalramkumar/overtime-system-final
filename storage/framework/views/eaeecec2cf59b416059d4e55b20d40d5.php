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
    <div class="p-6">
        
        <!-- सफलताको सन्देश देखाउने सेक्सन -->
        <?php if(session('success')): ?>
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative shadow-sm">
                <span class="block sm:inline"><?php echo e(session('success')); ?></span>
            </div>
        <?php endif; ?>

        <?php if(session('error')): ?>
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative shadow-sm">
                <span class="block sm:inline"><?php echo e(session('error')); ?></span>
            </div>
        <?php endif; ?>

        <div class="mb-4 text-right">
            <a href="<?php echo e(route('users.create')); ?>" class="bg-gray-600 text-white px-4 py-2 rounded font-bold hover:bg-gray-700">
                New User
            </a>
        </div>
        <h2 class="text-xl font-bold mb-4">प्रयोगकर्ता सूची</h2>
        <table class="w-full border bg-white shadow-sm rounded-lg">
            <thead>
                <tr class="bg-gray-200">
                    <th class="p-2 border">नाम</th>
                    <th class="p-2 border">इमेल</th>
                    <th class="p-2 border">रोल</th>
                    <th class="p-2 border">एक्सन</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td class="p-2 border"><?php echo e($user->name); ?></td>
                    <td class="p-2 border"><?php echo e($user->email); ?></td>
                    <td class="p-2 border"><?php echo e($user->role); ?></td>
                    <td class="p-2 border flex gap-2">
                        <a href="<?php echo e(route('users.edit', $user->id)); ?>" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 text-sm">
                            Edit
                        </a>

                        <form action="<?php echo e(route('users.destroy', $user->id)); ?>"
                              method="POST"
                              class="inline"
                              onsubmit="return confirm('के तपाईं यो युजर हटाउन निश्चित हुनुहुन्छ? अडचणी आउन सक्छ।');">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 text-sm">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
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
<?php endif; ?><?php /**PATH D:\xampp\htdocs\overtime-system\resources\views/users/index.blade.php ENDPATH**/ ?>