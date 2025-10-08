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

@section('title', 'Crear Rol')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Nuevo Rol</h5>
            <a href="{{ route('roles.index') }}" class="btn btn-light">Volver</a>
        </div>

        <form id="role-form" method="POST" action="{{ route('roles.store') }}">
            @csrf
            <div class="card-body">
                {{-- nombre --}}
                <div class="form-group">
                    <label>Nombre del rol <span class="text-danger">*</span></label>
                    <input type="text" name="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}"
                           maxlength="80" required placeholder="ej. mozo">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- permisos por módulo --}}
                @if($perms->isEmpty())
                    <div class="alert alert-light mb-0">No hay permisos definidos.</div>
                @else
                    {{-- 👇 aquí pegas el foreach completo --}}
                    @foreach($perms as $module => $rows)
                        @php
                            // 👇 si $module es null, toma el prefijo del primero (dashboard, salas, etc.)
                            $firstName  = optional($rows->first())->name;
                            $fallback   = $firstName ? \Illuminate\Support\Str::before($firstName, '.') : 'otros';
                            $moduleKey  = $module ?: $fallback;                 // <-- clave consistente
                            $moduleLbl  = $modules[$moduleKey] ?? ucfirst($moduleKey);
                            $selectAllId = 'modchk_' . preg_replace('/[^a-z0-9_]/i', '_', $moduleKey);
                        @endphp

                        <div class="border rounded mb-3">
                            <div class="bg-light px-2 py-1 d-flex align-items-center">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input perm-mod-all"
                                           id="{{ $selectAllId }}" data-module="{{ $moduleKey }}">
                                    <label class="custom-control-label" for="{{ $selectAllId }}">
                                        <strong>{{ $moduleLbl }}</strong>
                                    </label>
                                </div>
                            </div>

                            <div class="px-2 py-2">
                                <div class="row">
                                    @foreach($rows as $p)
                                        @php
                                            $pref = $p->module ?: \Illuminate\Support\Str::before($p->name, '.');
                                            $action = \Illuminate\Support\Str::after($p->name, $pref.'.');
                                        @endphp
                                        <div class="col-12 col-md-6 col-lg-4 mb-1">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input perm-chk"
                                                       id="perm_{{ $p->id }}"
                                                       name="permissions[]" value="{{ $p->id }}"
                                                       data-module="{{ $moduleKey }}"><!-- 👈 igual que el padre -->
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
                @can('roles.create')
                <button type="submit" class="btn btn-success">Guardar</button>
                @endcan
                @can('roles.list')
                <a href="{{ route('roles.index') }}" class="btn btn-secondary ml-2">Cancelar</a>
                @endcan
            </div>
        </form>
    </div>
@endsection

@push('css')
    <style>
        .custom-control-label code { font-size: .85rem; }
    </style>
@endpush

@section('scripts')
    <script>
        // Select all por módulo + estado indeterminado
        $(function () {
            function refreshModuleToggle($container, module){
                const $kids = $container.find('.perm-chk[data-module="'+module+'"]');
                const total = $kids.length;
                const checked = $kids.filter(':checked').length;
                const $all = $container.find('.perm-mod-all[data-module="'+module+'"]');
                $all.prop('checked', checked === total)
                    .prop('indeterminate', checked > 0 && checked < total);
            }

            // Padre -> hijos
            $(document).on('change', '.perm-mod-all', function(){
                const mod = $(this).data('module');
                const checked = $(this).is(':checked');
                const $block = $(this).closest('.border');
                $block.find('.perm-chk[data-module="'+mod+'"]').prop('checked', checked).trigger('change');
                refreshModuleToggle($block, mod);
            });

            // Hijo -> padre
            $(document).on('change', '.perm-chk', function(){
                const mod = $(this).data('module');
                const $block = $(this).closest('.border');
                refreshModuleToggle($block, mod);
            });

            // Estado inicial (cuando el DOM ya está)
            $('.perm-mod-all').each(function(){
                const mod = $(this).data('module');
                const $block = $(this).closest('.border');
                refreshModuleToggle($block, mod);
            });

            $('#role-form').on('submit', function(e){
                e.preventDefault();
                const $form = $(this);

                $.confirm({
                    title: 'Confirmar creación',
                    content: '¿Seguro que deseas guardar este rol con los permisos seleccionados?',
                    type: 'green',
                    buttons: {
                        confirmar: {
                            text: 'Sí, guardar',
                            btnClass: 'btn-success',
                            action: function(){
                                $.ajax({
                                    url: $form.attr('action'),
                                    method: 'POST',
                                    data: $form.serialize(),
                                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                                    success: function(resp){
                                        if(resp.ok){
                                            toastr.success(resp.msg);
                                            if(resp.redirect_url){
                                                window.location.href = resp.redirect_url;
                                            }
                                        } else {
                                            toastr.error(resp.msg || 'No se pudo guardar');
                                        }
                                    },
                                    error: function(xhr){
                                        toastr.error(xhr.responseJSON?.message || 'Error en validación');
                                    }
                                });
                                return false; // evita submit real
                            }
                        },
                        cancelar: {
                            text: 'Cancelar',
                            btnClass: 'btn-secondary'
                        }
                    }
                });
            });
        });
    </script>
@endsection