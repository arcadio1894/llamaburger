@php
    $isActive = isset($active) && $active;
@endphp

<button
        type="button"
        class="btn btn-outline-primary btn-sm btn-sala {{ $isActive ? 'active' : '' }}"
        data-sala-id="{{ $sala->id }}"
>
    {{ $sala->nombre }}
</button>