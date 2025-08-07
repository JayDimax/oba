<?php if (isset($component)) { $__componentOriginal69dc84650370d1d4dc1b42d016d7226b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal69dc84650370d1d4dc1b42d016d7226b = $attributes; } ?>
<?php $component = App\View\Components\GuestLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('guest-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(App\View\Components\GuestLayout::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br px-4">
        <div class="w-full max-w-md bg-white/10 backdrop-blur-lg rounded-xl shadow-lg p-8 space-y-6 text-center border border-white/30">

            
            <div class="flex justify-center">
                <img src="<?php echo e(asset('images/orca-logo.png')); ?>" alt="Logo" class="h-20 w-auto">
            </div>

            <h1 class="text-4xl font-bold text-gray-800 dark:text-gray-100">ORCAS</h1>
            <p class="text-lg text-gray-600 dark:text-gray-300">Betting App</p>

            
            <?php if(session('status')): ?>
                <div class="text-green-600 dark:text-green-400 font-medium">
                    <?php echo e(session('status')); ?>

                </div>
            <?php endif; ?>

            
            <form method="POST" action="<?php echo e(route('login')); ?>" class="space-y-6 text-left">
                <?php echo csrf_field(); ?>

                
                <div>
                    <label for="agent_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">User Code</label>
                    <input id="agent_code" name="agent_code" type="text" required autofocus
                        class="w-full mt-1 px-3 py-2 border border-gray-300 dark:border-gray-700 rounded bg-white/80 dark:bg-gray-900 text-gray-900 dark:text-gray-100 shadow-sm focus:ring focus:ring-blue-500" />
                </div>

                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password"
                        class="w-full mt-1 px-3 py-2 border border-gray-300 dark:border-gray-700 rounded bg-white/80 dark:bg-gray-900 text-gray-900 dark:text-gray-100 shadow-sm focus:ring focus:ring-blue-500" />
                    <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="text-red-600 dark:text-red-400 text-sm mt-1"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                
                <div class="flex items-center">
                    <input id="remember_me" type="checkbox" name="remember"
                        class="mr-2 rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                    <label for="remember_me" class="text-sm text-gray-600 dark:text-gray-400">Remember me</label>
                </div>

                
                <div class="flex flex-col gap-3 mt-6">
                    <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-lg transition">
                        Log in
                    </button>

                    <?php if(Route::has('password.request')): ?>
                        <a href="<?php echo e(route('password.request')); ?>"
                            class="text-sm text-center text-gray-600 dark:text-gray-400 hover:underline">
                            Forgot your password?
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal69dc84650370d1d4dc1b42d016d7226b)): ?>
<?php $attributes = $__attributesOriginal69dc84650370d1d4dc1b42d016d7226b; ?>
<?php unset($__attributesOriginal69dc84650370d1d4dc1b42d016d7226b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal69dc84650370d1d4dc1b42d016d7226b)): ?>
<?php $component = $__componentOriginal69dc84650370d1d4dc1b42d016d7226b; ?>
<?php unset($__componentOriginal69dc84650370d1d4dc1b42d016d7226b); ?>
<?php endif; ?>
<?php /**PATH /home/u355685815/domains/orcasbettingapp.com/resources/views/auth/login.blade.php ENDPATH**/ ?>