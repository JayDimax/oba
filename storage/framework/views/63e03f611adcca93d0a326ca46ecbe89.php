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
         transition-all duration-300 ease-in-out flex flex-col w-72 items-center">
  <!-- Sidebar content (left unchanged) -->
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
  <h1 class="text-2xl font-bold mb-4 text-gray-800 dark:text-white">📤 Daily Collection</h1>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    <!-- Cashier Card -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 space-y-1">
      <div class="text-xs uppercase text-gray-400">Cashier</div>
      <div class="font-bold text-lg text-gray-800 dark:text-white"><?php echo e($cashier->name ?? 'Unassigned'); ?></div>
      <div class="text-gray-500 dark:text-gray-300"><?php echo e($cashier->agent_code ?? 'N/A'); ?></div>
      <div class="text-sm text-gray-600 dark:text-gray-300 mt-1 italic">
        Authorized Cashier
      </div>
      
  </div>

  <!-- Agent Card -->
  <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 space-y-1">
    <div class="text-xs uppercase text-gray-400">Collector</div>
    <div class="font-bold text-lg text-gray-800 dark:text-white"><?php echo e($agent->name); ?></div>
    <div class="text-sm text-gray-500 dark:text-gray-400"><?php echo e($agent->agent_code ?? 'N/A'); ?></div>
    <div class="text-sm text-gray-600 dark:text-gray-300 mt-1 italic">
      Registered Agent
    </div>
  </div>
  </div>


  <?php if(!$hasResults): ?>
    <div class="bg-yellow-50 dark:bg-gray-700 text-yellow-800 dark:text-yellow-200 border-l-4 border-yellow-400 p-4 rounded shadow-sm">
        <div class="font-semibold text-sm">Waiting for Results</div>
        <div class="text-xs text-gray-600 dark:text-gray-300 mt-1">
            Draw results (2PM, 5PM, 9PM) are not yet declared for this date. Remittance summary will be shown once results are available.
        </div>
    </div>
  <?php else: ?>
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 border-l-4 border-blue-600 mt-4">
      <div class="text-xs uppercase text-gray-500 dark:text-gray-300 mb-1 font-semibold">
          Overall Remittance for <?php echo e(\Carbon\Carbon::parse($date)->format('F j, Y')); ?>

      </div>
      <div class="text-2xl font-bold text-blue-600 dark:text-blue-300">
          ₱<?php echo e(number_format($overallRemittance, 2)); ?>

      </div>
      <div class="text-sm text-gray-500 dark:text-gray-400 mt-1 italic">
          Computed after all game draw results were declared.
      </div>
  </div>
<?php endif; ?>

  
  <?php if(session('success')): ?>
  <div
    x-data="{ show: true }"
    x-show="show"
    x-init="setTimeout(() => show = false, 3000)"
    x-transition
    class="bg-green-100 dark:bg-green-800 text-green-800 dark:text-green-100 px-4 py-3 rounded shadow mb-4">
    ✅ <?php echo e(session('success')); ?>

  </div>
  <?php endif; ?>

  <?php if(session('error')): ?>
  <div
    x-data="{ show: true }"
    x-show="show"
    x-init="setTimeout(() => show = false, 4000)"
    x-transition
    class="bg-red-100 dark:bg-red-800 text-red-800 dark:text-red-100 px-4 py-3 rounded shadow mb-4">
    ⚠️ <?php echo e(session('error')); ?>

  </div>
  <?php endif; ?>

<!-- Agent Remittance Form (Horizontal Layout) -->
<div x-data="{ confirm: false, preview: null, loading: false }" class="w-full overflow-auto px-0 mt-6">
  <div class="w-full bg-white dark:bg-gray-800 shadow-md rounded-none sm:rounded-xl p-6">
    <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-6">Submit Remittance</h2>

    <!-- Form Section -->
    <form id="remitForm"
          method="POST"
          action="<?php echo e(route('agent.collections.store')); ?>"
          enctype="multipart/form-data"
          class="flex flex-col lg:flex-row lg:items-end lg:space-x-6 space-y-4 lg:space-y-0"
          x-init="$watch(() => $refs.date.value, async (val) => {
              if (!val) return;
              loading = true;
              const res = await fetch(`<?php echo e(route('agent.remit-preview')); ?>?date=${val}`);
              preview = await res.json();
              loading = false;
          })"
    >
      <?php echo csrf_field(); ?>

      <!-- Game Date -->
      <div class="flex-1">
        <label for="collection_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
          Game Date
        </label>
        <input type="date"
               name="collection_date"
               id="collection_date"
               x-ref="date"
               value="<?php echo e(old('collection_date', $gameDate ?? now()->toDateString())); ?>"
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
               required>
      </div>

      <!-- Proof File -->
      <div>
        <label for="proof_file" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
          Upload Proof of Payment
        </label>
        <input type="file" name="proof_file" id="proof_file"
               accept="image/*,application/pdf"
               class="mt-1 block w-full text-sm border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
               required>
      </div>

      <!-- Submit Button -->
      <div class="flex-none">
        <label class="block text-sm font-medium text-transparent">Submit</label>
        <button type="button"
                @click="confirm = true"
                class="w-full px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-md shadow">
          Remit Now
        </button>
      </div>
    </form>
  </div>

  <!-- Confirmation Modal -->
  <div x-show="confirm" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 w-full max-w-sm mx-auto" @click.away="confirm = false">
      <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-3">Confirm Remittance</h3>
      <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">
        Are you sure you want to remit now? Once submitted, this will be sent to the cashier for approval.
      </p>
      <div class="flex justify-end space-x-2">
        <button @click="confirm = false"
                class="px-4 py-2 text-sm rounded-md bg-gray-300 dark:bg-gray-700 text-gray-800 dark:text-gray-200 hover:bg-gray-400 dark:hover:bg-gray-600">
          Cancel
        </button>
        <button @click="document.getElementById('remitForm').submit()"
                class="px-4 py-2 text-sm rounded-md bg-blue-600 text-white hover:bg-blue-700">
          Confirm
        </button>
      </div>
    </div>
  </div>
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
<?php /**PATH /home/u355685815/domains/orcasbettingapp.com/resources/views/agent/collections.blade.php ENDPATH**/ ?>