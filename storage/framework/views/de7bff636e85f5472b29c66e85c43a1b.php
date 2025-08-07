<aside
  x-cloak
  :class="{
    'translate-x-0': sidebarOpen || window.innerWidth >= 640,
    '-translate-x-full': !sidebarOpen && window.innerWidth < 640,
    'w-16 sm:w-20': sidebarCollapsed,
    'w-72': !sidebarCollapsed
  }"
  class="sticky bottom-0 left-0 transform
         bg-white dark:bg-gray-800 shadow-md
         transition-all duration-300 ease-in-out flex flex-col w-72 items-center"
>
</aside>

<?php if (isset($component)) { $__componentOriginalbd26f90b0e8ad6cc54a49c99a73eac08 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbd26f90b0e8ad6cc54a49c99a73eac08 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.panel','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('layouts.panel'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('sidebar', null, []); ?> 
      <?php echo $__env->make('partials.agent-sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
     <?php $__env->endSlot(); ?>

  
  <h1 class="text-2xl font-bold mb-4 text-gray-800 dark:text-white"> 🔔 Support</h1>

  
    <div class="min-h-screen x-cloak dark:bg-gray-900 p-4 flex flex-col space-y-6">
        <p class="text-gray-600 dark:text-gray-300">If you need help, please contact our support team:</p>
          <ul class="list-disc ml-6 mt-4">
            
            <li>Administrator</li>
            <li>For Any Concern: Please contact your administrator and provide screen shot for easy tracing of problem.</li>
            
        </ul>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbd26f90b0e8ad6cc54a49c99a73eac08)): ?>
<?php $attributes = $__attributesOriginalbd26f90b0e8ad6cc54a49c99a73eac08; ?>
<?php unset($__attributesOriginalbd26f90b0e8ad6cc54a49c99a73eac08); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbd26f90b0e8ad6cc54a49c99a73eac08)): ?>
<?php $component = $__componentOriginalbd26f90b0e8ad6cc54a49c99a73eac08; ?>
<?php unset($__componentOriginalbd26f90b0e8ad6cc54a49c99a73eac08); ?>
<?php endif; ?>
<?php /**PATH /home/u355685815/domains/orcasbettingapp.com/resources/views/agent/support.blade.php ENDPATH**/ ?>