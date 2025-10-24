<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'contained' => true,
    'label' => null,
    'labelHidden' => false,
    'required' => false,
]));

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

foreach (array_filter(([
    'contained' => true,
    'label' => null,
    'labelHidden' => false,
    'required' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<fieldset
    <?php echo e($attributes->class([
            'fi-fieldset',
            'fi-fieldset-label-hidden' => $labelHidden,
            'fi-fieldset-not-contained' => ! $contained,
        ])); ?>

>
    <!--[if BLOCK]><![endif]--><?php if(filled($label)): ?>
        <legend>
            <?php echo e($label); ?><!--[if BLOCK]><![endif]--><?php if($required): ?><sup class="fi-fieldset-label-required-mark">*</sup>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </legend>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <?php echo e($slot); ?>

</fieldset>
<?php /**PATH /Users/tutran/Githubs/regmail/vendor/filament/support/resources/views/components/fieldset.blade.php ENDPATH**/ ?>