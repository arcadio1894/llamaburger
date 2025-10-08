@extends('layouts.admin')

@section('openAccess')
    menu-open
@endsection

@section('activeAccess')
    active
@endsection

@section('activeRoles')
    active
@endsection

@section('title', 'Editar Rol')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Editar Rol: {{ $role->name }}</h5>
            <a href="{{ route('roles.index') }}" class="btn btn-light">Volver</a>
        </div>

        <form id="role-form" method="POST" action="{{ route('roles.update', $role) }}">
            @csrf
            @method('PUT')
            <div class="card-body">
                {{-- nombre --}}
                <div class="form-group">
                    <label>Nombre del rol <span class="text-danger">*</span></label>
                    <input type="text" name="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $role->name) }}"
                           maxlength="80" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- permisos por módulo --}}
                @if($perms->isEmpty())
                    <div class="alert alert-light mb-0">No hay permisos definidos.</div>
                @else
                    @foreach($perms as $module => $rows)
                        @php
                            $firstName  = optional($rows->first())->name;
                            $fallback   = $firstName ? \Illuminate\Support\Str::before($firstName, '.') : 'otros';
                            $moduleKey  = $module ?: $fallback;
                            $moduleLbl  = $modules[$moduleKey] ?? ucfirst($moduleKey);
                            $selectAllId = 'modchk_' . preg_replace('/[^a-z0-9_]/i', '_', $moduleKey);
                        @endphp

                        <div class="border rounded mb-3">
                            <div class="bg-light px-2 py-1 d-flex align-items-center">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input perm-mod-all" id="{{ $selectAllId }}">
                                    <label class="custom-control-label" for="{{ $selectAllId }}">
                                        <strong>{{ $moduleLbl }}</strong>
                                    </label>
                                </div>
                            </div>

                            <div class="px-2 py-2">
                                <div class="row">
                                    @foreach($rows as $p)
                                        @php
                                            $pref   = $p->module ?: \Illuminate\Support\Str::before($p->name, '.');
                                            $action = \Illuminate\Support\Str::after($p->name, $pref.'.');
                                        @endphp
                                        <div class="col-12 col-md-6 col-lg-4 mb-1">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input perm-chk"
                                                       id="perm_{{ $p->id }}"
                                                       name="permissions[]"
                                                       value="{{ $p->id }}"
                                                        {{ in_array($p->id, old('permissions', $selectedIds)) ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="perm_{{ $p->id }}">
                                                    <code>{{ $action }}</code>
                                                    @if($p->description) <small class="text-muted">— {{ $p->description }}</small>@endif
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            <div class="card-footer d-flex justify-content-end">
                @can('roles.edit')
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
                @endcan
                @can('roles.list')
                <a href="{{ route('roles.index') }}" class="btn btn-secondary ml-2">Cancelar</a>
                @endcan
            </div>
        </form>
    </div>



@endsection

@section('scripts')
    <script>
        // Select-all por bloque (sin data-module)
        (function () {
            function refreshModuleToggle($block){
                const $kids = $block.find('.perm-chk');
                const total = $kids.length;
                const checked = $kids.filter(':checked').length;
                const $all = $block.find('.perm-mod-all');
                $all.prop('checked', checked === total)
                    .prop('indeterminate', checked > 0 && checked < total);
            }

            // Padre -> hijos
            document.addEventListener('change', function(e){
                if(!e.target.classList.contains('perm-mod-all')) return;
                const block = e.target.closest('.border');
                const checked = e.target.checked;
                block.querySelectorAll('.perm-chk').forEach(chk => { chk.checked = checked; });
                refreshModuleToggle($(block));
            });

            // Hijo -> padre
            document.addEventListener('change', function(e){
                if(!e.target.classList.contains('perm-chk')) return;
                const block = e.target.closest('.border');
                refreshModuleToggle($(block));
            });

            // Estado inicial
            document.querySelectorAll('.perm-mod-all').forEach(all => {
                refreshModuleToggle($(all.closest('.border')));
            });

            $('#role-form').on('submit', function(e){
                e.preventDefault();
                const $form = $(this);

                $.confirm({
                    title: 'Confirmar actualización',
                    content: '¿Deseas guardar los cambios de este rol?',
                    type: 'orange',
                    buttons: {
                        cancelar: { text: 'Cancelar', btnClass: 'btn-default' },
                        actualizar: {
                            text: 'Sí, actualizar',
                            btnClass: 'btn-warning',
                            action: function(){
                                const payload = $form.serialize(); // ← incluye _token y _method=PUT

                                return $.ajax({
                                    url: $form.attr('action'),
                                    method: 'POST',                 // Laravel leerá _method=PUT
                                    data: payload,
                                    dataType: 'json',
                                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
                                })
                                    .done(function(resp){
                                        if (resp.ok) {
                                            toastr.success(resp.msg || 'Rol actualizado correctamente.');
                                            // si quieres, actualiza el título en la misma vista:
                                            // $('.page-title').text('Editar Rol: ' + (resp.role?.name || ''));
                                        } else {
                                            toastr.error(resp.msg || 'No se pudo actualizar.');
                                        }
                                    })
                                    .fail(function(xhr){
                                        if (xhr.status === 422) {
                                            const json = xhr.responseJSON || {};
                                            toastr.error(json.message || 'Revisa los datos.');
                                        } else {
                                            toastr.error('Error al actualizar.');
                                        }
                                    });
                            }
                        }
                    }
                });
            });
        })();
    </script>
@endsection
