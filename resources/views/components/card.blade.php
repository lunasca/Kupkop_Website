@props(['title' => null])

<div {{ $attributes->merge(['class' => 'card']) }}>
    @if($title)
        <div class="card-header">
            <h3>{{ $title }}</h3>
        </div>
    @endif
    <div class="card-body">
        {{ $slot }}
    </div>
</div>
