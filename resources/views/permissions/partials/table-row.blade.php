@php
    // Si tienes column module, tomar acción desde name:
    $module = $p->module ?? \Illuminate\Support\Str::before($p->name, '.');
    $action = \Illuminate\Support\Str::after($p->name, $module.'.');
@endphp

<tr id="perm-row-{{ $p->id }}">
    <td>{{ $p->id }}</td>
    <td>{{ $p->name }}</td>
    <td>{{ $module }}</td>
    <td>{{ $p->description ?? '—' }}</td>
    <td class="text-right">
        <button
                class="btn btn-sm btn-outline-primary btn-edit-perm"
                title="Editar"
                data-id="{{ $p->id }}"
                data-module="{{ $module }}"
                data-action="{{ $action }}"
                data-description="{{ $p->description }}"
        >
            <i class="fa fa-pencil-alt"></i>
        </button>
        <button
                class="btn btn-sm btn-outline-danger btn-del-perm"
                title="Eliminar"
                data-id="{{ $p->id }}"
                data-name="{{ $p->name }}"
        >
            <i class="fa fa-trash"></i>
        </button>
    </td>
</tr>