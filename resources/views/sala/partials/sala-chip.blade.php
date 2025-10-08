@php
    $isActive  = !empty($active);
    // soporta $sala->deleted_at (modelo) o array ['deleted_at'=>...]
    $isDeleted = !empty($sala->deleted_at);
@endphp

<div class="chip sala-chip cfg-sala-card {{ $isActive ? 'active' : '' }} {{ $isDeleted ? 'deleted' : '' }}"
     data-id="{{ $sala->id }}"
     data-nombre="{{ $sala->nombre }}"
     data-descripcion="{{ $sala->descripcion }}"
     data-deleted="{{ $isDeleted ? 1 : 0 }}">
    <span class="sala-name pr-4">{{ $sala->nombre }}</span>
    <button type="button" class="btn btn-xs btn-light border edit-btn btn-edit-sala" title="Editar sala">
        <i class="fas fa-pencil-alt"></i>
    </button>
</div>