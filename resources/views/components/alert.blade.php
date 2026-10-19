@props(['type' => 'info', 'message' => null])

@php
    $typeClasses = [
        'success' => 'alert-success',
        'error'   => 'alert-error',
        'warning' => 'alert-warning',
        'info'    => 'alert-info',
    ];

    $class = $typeClasses[$type] ?? 'alert-info';
@endphp

<div {{ $attributes->merge(['class' => "alert {$class}"]) }} role="alert">
    {{ $message ?? $slot }}
</div>
