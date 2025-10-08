@php
    $isDeleted = !is_null($u->deleted_at ?? null);
    $roleNames = $u->roles()->pluck('name')->implode(', ');
@endphp
<tr id="user-row-{{ $u->id }}" class="{{ $isDeleted ? 'table-secondary' : '' }}">
    <td>{{ $u->id }}</td>
    <td>
        {{ $u->name }}
        @if($isDeleted)
            <span class="badge badge-secondary ml-1">Eliminado</span>
        @endif
    </td>
    <td class="{{ $isDeleted ? 'text-muted' : '' }}">{{ $u->email }}</td>
    <td class="{{ $isDeleted ? 'text-muted' : '' }}">{{ $roleNames ?: '—' }}</td>
    <td class="text-right">
        @if(!$isDeleted)
            <button class="btn btn-sm btn-outline-primary btn-edit-user"
                    data-id="{{ $u->id }}" data-name="{{ $u->name }}">
                <i class="fa fa-pencil-alt"></i>
            </button>
            <button class="btn btn-sm btn-outline-danger btn-del-user"
                    data-id="{{ $u->id }}" data-name="{{ $u->name }}">
                <i class="fa fa-trash"></i>
            </button>
        @else
            <button class="btn btn-sm btn-outline-success btn-restore-user"
                    data-id="{{ $u->id }}" data-name="{{ $u->name }}">
                <i class="fa fa-undo"></i>
            </button>
        @endif
    </td>
</tr>