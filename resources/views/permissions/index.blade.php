@extends('layouts.admin')

@section('openAccess')
    menu-open
@endsection

@section('activeAccess')
    active
@endsection

@section('activePermissions')
    active
@endsection

@section('title','Permisos')

@section('styles-plugins')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery-confirm@3.3.4/dist/jquery-confirm.min.css">
@endsection

@section('content')

    <div class="d-flex align-items-end mb-3">
        <div class="mr-2" style="min-width:260px;">
            <label class="mb-1">Buscar por nombre</label>
            <input type="text" id="filter-name" class="form-control" placeholder="p.e. salas.create">
        </div>
        <div class="mr-2" style="min-width:220px;">
            <label class="mb-1">Módulo</label>
            <select id="filter-module" class="form-control">
                <option value="">— Todos —</option>
                @foreach($modules as $k => $label)
                    <option value="{{ $k }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="mr-2">
            <label class="mb-1 d-block">&nbsp;</label>
            <button id="btn-search" class="btn btn-primary">Buscar</button>
        </div>
        @can('permisos.create')
        <div class="ml-auto">
            <label class="mb-1 d-block">&nbsp;</label>
            <button id="btn-new-permission" class="btn btn-success">
                <i class="fa fa-plus"></i> Nuevo Permiso
            </button>
        </div>
        @endcan
    </div>

    <div class="table-responsive">
        <table class="table table-sm table-hover">
            <thead>
            <tr>
                <th style="width:36px;">#</th>
                <th>Nombre (name)</th>
                <th>Módulo</th>
                <th>Descripción</th>
                <th style="width:80px;">Acciones</th>
            </tr>
            </thead>
            <tbody id="perm-tbody">
            {{-- se carga por AJAX --}}
            </tbody>
        </table>
    </div>

    <div id="perm-pager" class="mt-2"></div>

@endsection

@section('plugins')
    <script src="https://cdn.jsdelivr.net/npm/jquery-confirm@3.3.4/dist/jquery-confirm.min.js"></script>
@endsection

@section('scripts')
    <script>
        // Opciones de módulos para selects
        const MODULE_OPTIONS = @json(config('modules'));

        // CSRF global
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

        const state = { page: 1, per_page: 20 };

        function loadTable(page = 1) {
            state.page = page;
            const params = {
                page: page,
                per_page: state.per_page,
                name: $('#filter-name').val() || '',
                module: $('#filter-module').val() || ''
            };
            $.get("{{ route('permissions.list') }}", params, function(resp){
                if(!resp.ok) return toastr.error('No se pudo cargar la lista');
                $('#perm-tbody').html(resp.html.tbody);
                $('#perm-pager').html(resp.html.pager);
            }, 'json').fail(() => toastr.error('Error al listar permisos'));
        }

        // Buscar
        $('#btn-search').on('click', () => loadTable(1));
        $('#filter-name, #filter-module').on('keypress change', function(e){
            if (e.type === 'keypress' && e.which !== 13) return;
            loadTable(1);
        });

        // Paginación (delegación)
        $(document).on('click', '.perm-page-link', function(e){
            e.preventDefault();
            const page = Number($(this).data('page'));
            if (page) loadTable(page);
        });

        // Crear permiso
        $('#btn-new-permission').on('click', function(){
            const moduleOptions = @json($modules);
            const opts = Object.entries(moduleOptions).map(([v,t]) => `<option value="${v}">${t}</option>`).join('');

            $.confirm({
                title: 'Nuevo Permiso',
                theme: 'modern', type: 'green', columnClass: 'medium',
                content:
                    `<form id="form-permission" class="p-2">
           <div class="form-group">
             <label>Módulo</label>
             <select name="module" class="form-control" required>
               <option value="">— Seleccione —</option>${opts}
             </select>
           </div>
           <div class="form-group">
             <label>Acción</label>
             <input name="action" class="form-control" placeholder="p.e. create, view, update" required maxlength="40">
             <small class="text-muted">El nombre final será <code>modulo.accion</code>, ej: <em>salas.create</em></small>
           </div>
           <div class="form-group">
             <label>Descripción (opcional)</label>
             <input name="description" class="form-control" maxlength="255">
           </div>
         </form>`,
                buttons: {
                    guardar: {
                        text: 'Guardar', btnClass: 'btn-success',
                        action: function(){
                            const $f = this.$content.find('#form-permission');
                            return $.post("{{ route('permissions.store') }}", $f.serialize())
                                .done(resp => {
                                    if (!resp.ok) return toastr.error(resp.msg || 'No se pudo crear');
                                    toastr.success(resp.msg);
                                    // Recargar tabla (mantén la página actual para no perder contexto)
                                    loadTable(state.page);
                                })
                                .fail(xhr => toastr.error(xhr.responseJSON?.message || 'Validación fallida'));
                        }
                    },
                    cancelar: { text: 'Cancelar', btnClass: 'btn-default' }
                }
            });
        });

        // Primera carga
        $(function(){ loadTable(1); });

        // Editar permiso (delegación)
        $(document).on('click', '.btn-edit-perm', function(){
            const btn   = $(this);
            const id    = btn.data('id');
            const mod   = btn.data('module') || '';
            const act   = btn.data('action') || '';
            const desc  = btn.data('description') || '';

            const opts = Object.entries(MODULE_OPTIONS)
                .map(([k,v]) => `<option value="${k}" ${k===mod?'selected':''}>${v}</option>`)
                .join('');

            $.confirm({
                title: 'Editar Permiso',
                theme: 'modern', type: 'orange', columnClass: 'medium',
                content:
                    `<form id="form-permission-edit" class="p-2">
                         <div class="form-group">
                           <label>Módulo</label>
                           <select name="module" class="form-control" required>
                             ${opts}
                           </select>
                         </div>
                         <div class="form-group">
                           <label>Acción</label>
                           <input name="action" class="form-control" required maxlength="40" value="${act}">
                         </div>
                         <div class="form-group">
                           <label>Descripción</label>
                           <input name="description" class="form-control" maxlength="255" value="${desc}">
                         </div>
                       </form>`,
                buttons: {
                    guardar: {
                        text: 'Guardar', btnClass: 'btn-primary',
                        action: function(){
                            const $f = this.$content.find('#form-permission-edit');
                            return $.post(`/dashboard/permissions/${id}/update`, $f.serialize())
                                .done(resp => {
                                    if(!resp.ok) return toastr.error(resp.msg || 'No se pudo actualizar');
                                    toastr.success(resp.msg);
                                    // Reemplaza la fila por la nueva HTML
                                    $(`#perm-row-${id}`).replaceWith(resp.html);
                                })
                                .fail(xhr => toastr.error(xhr.responseJSON?.message || 'Error de validación'));
                        }
                    },
                    cancelar: { text: 'Cancelar', btnClass: 'btn-default' }
                }
            });
        });

        // Eliminar permiso (delegación)
        $(document).on('click', '.btn-del-perm', function(){
            const id   = $(this).data('id');
            const name = $(this).data('name');

            $.confirm({
                title: 'Eliminar Permiso',
                type: 'red', theme: 'modern',
                content: `<p>¿Seguro que deseas eliminar <strong>${name}</strong>?</p>`,
                buttons: {
                    eliminar: {
                        text: 'Eliminar', btnClass: 'btn-danger',
                        action: function(){
                            return $.post(`/dashboard/permissions/${id}/destroy`, {})
                                .done(resp => {
                                    if(!resp.ok) return toastr.error(resp.msg || 'No se pudo eliminar');
                                    toastr.success(resp.msg);
                                    // Recarga la página actual del listado para mantener paginación & filtros
                                    loadTable(state.page);
                                })
                                .fail(() => toastr.error('Error al eliminar'));
                        }
                    },
                    cancelar: { text: 'Cancelar', btnClass: 'btn-default' }
                }
            });
        });
    </script>
@endsection