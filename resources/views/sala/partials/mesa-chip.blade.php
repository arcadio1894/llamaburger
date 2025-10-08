@php $isDeleted = !empty($mesa->deleted_at); @endphp
<div class="chip mesa-chip cfg-mesa-card {{ $isDeleted ? 'deleted' : '' }}"
     data-id="{{ $mesa->id }}"
     data-nombre="{{ $mesa->nombre }}"
     data-estado="{{ $mesa->estado }}"
     data-descripcion="{{ $mesa->descripcion }}"
     data-deleted="{{ $isDeleted ? 1 : 0 }}">
    <strong class="mesa-name pr-4">{{ $mesa->nombre }}</strong>
    <button type="button" class="btn btn-xs btn-light border edit-btn btn-edit-mesa" title="Editar mesa">
        <i class="fas fa-pencil-alt"></i>
    </button>
</div>