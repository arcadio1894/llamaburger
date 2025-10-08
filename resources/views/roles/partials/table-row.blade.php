<tr id="role-row-{{ $r->id }}">
    <td>{{ $r->id }}</td>
    <td>{{ $r->name }}</td>
    <td class="text-right">
        <a class="btn btn-sm btn-outline-primary" href="{{ route('roles.edit', $r->id) }}"
                title="Editar" data-id="{{ $r->id }}" data-name="{{ $r->name }}">
            <i class="fa fa-pencil-alt"></i>
        </a>
        <button class="btn btn-sm btn-outline-danger btn-del-role"
                title="Eliminar" data-id="{{ $r->id }}" data-name="{{ $r->name }}">
            <i class="fa fa-trash"></i>
        </button>
    </td>
</tr>