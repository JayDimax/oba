<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps(['label' => 'Mode']) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps(['label' => 'Mode']); ?>
<?php foreach (array_filter((['label' => 'Mode']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<a href="javascript:void(0)"
   @click="$store.layout.toggleDarkMode()"
   class="flex items-center gap-2 px-3 py-2 rounded-md transition-all duration-200 
          hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm"
>
  <i :data-lucide="$store.layout.darkMode ? 'sun' : 'moon'" class="w-5 h-5"></i>
  <span x-show="!$store.layout.sidebarCollapsed" x-transition><?php echo e($label); ?></span>
</a>
<?php /**PATH /home/u355685815/domains/orcasbettingapp.com/resources/views/components/theme-toggle.blade.php ENDPATH**/ ?>