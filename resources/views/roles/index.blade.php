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

@section('title','Roles')

@section('styles-plugins')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery-confirm@3.3.4/dist/jquery-confirm.min.css">
@endsection

@section('content')

    <div class="d-flex align-items-end mb-3">
        <div class="mr-2" style="min-width:260px;">
            <label class="mb-1">Buscar por nombre</label>
            <input type="text" id="filter-name" class="form-control" placeholder="p.e. admin, mozo">
        </div>
        <div class="mr-2">
            <label class="mb-1 d-block">&nbsp;</label>
            <button id="btn-search" class="btn btn-primary">Buscar</button>
        </div>
        @can('roles.create')
        <div class="ml-auto">
            <label class="mb-1 d-block">&nbsp;</label>
            <a href="{{ route('roles.create') }}" class="btn btn-success">
                <i class="fa fa-plus"></i> Nuevo Rol
            </a>
        </div>
        @endcan
    </div>

    <div class="table-responsive">
        <table class="table table-sm table-hover">
            <thead>
            <tr>
                <th style="width:36px;">#</th>
                <th>Nombre</th>
                <th style="width:100px;">Acciones</th>
            </tr>
            </thead>
            <tbody id="role-tbody"></tbody>
        </table>
    </div>

    <div id="role-pager" class="mt-2"></div>



@endsection

@section('plugins')
    <script src="https://cdn.jsdelivr.net/npm/jquery-confirm@3.3.4/dist/jquery-confirm.min.js"></script>
@endsection

@section('scripts')
    {{-- Exponer permisos agrupados para los modales --}}
    <script>
        window.PERM_MAP = @json($permMap); // { modulo: [{id, name, action, description}] }
        window.MODULE_LABELS = @json($modules); // para títulos bonitos
    </script>
    <script>
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

        const stateR = { page: 1, per_page: 20 };

        function loadRoles(page=1){
            stateR.page = page;
            $.get("{{ route('roles.list') }}", {
                page: page, per_page: stateR.per_page, name: $('#filter-name').val() || ''
            }, function(resp){
                if(!resp.ok) return toastr.error('No se pudo cargar la lista');
                $('#role-tbody').html(resp.html.tbody);
                $('#role-pager').html(resp.html.pager);
            }, 'json').fail(()=>toastr.error('Error al listar roles'));
        }

        $('#btn-search').on('click', ()=> loadRoles(1));
        $('#filter-name').on('keypress', function(e){ if(e.which===13) loadRoles(1); });
        $(document).on('click','.role-page-link', function(e){ e.preventDefault(); loadRoles($(this).data('page')); });

        // ------- Helpers para construir la grilla de permisos -------
        function buildPermGrid(selectedIds){
            selectedIds = selectedIds || [];
            const map = window.PERM_MAP || {};
            const labels = window.MODULE_LABELS || {};
            let html = '';

            Object.keys(map).forEach(module => {
                const perms = map[module];
                const moduleId = `modchk_${module}`;

                // select all
                html += `
                    <div class="border rounded mb-3">
                      <div class="bg-light px-2 py-1 d-flex align-items-center">
                        <div class="custom-control custom-checkbox">
                          <input type="checkbox" class="custom-control-input perm-mod-all" id="${moduleId}" data-module="${module}">
                          <label class="custom-control-label" for="${moduleId}">
                            <strong>${labels[module] || module}</strong>
                          </label>
                        </div>
                      </div>
                      <div class="px-2 py-2">
                        <div class="row">`;

                perms.forEach(p => {
                    const checked = selectedIds.includes(p.id) ? 'checked' : '';
                    html += `
                              <div class="col-12 col-md-6 col-lg-4 mb-1">
                                <div class="custom-control custom-checkbox">
                                  <input type="checkbox" class="custom-control-input perm-chk" id="perm_${p.id}"
                                         name="permissions[]" value="${p.id}" data-module="${module}" ${checked}>
                                  <label class="custom-control-label" for="perm_${p.id}">
                                    <code>${p.action}</code> <small class="text-muted">${p.description || ''}</small>
                                  </label>
                                </div>
                              </div>`;
                });

                html += `</div></div></div>`;
            });

            return html || '<div class="text-muted">No hay permisos definidos.</div>';
        }

        // select all comportamiento
        function bindSelectAll(container){
            // toggle hijos
            container.on('change', '.perm-mod-all', function(){
                const mod = $(this).data('module');
                const checked = $(this).is(':checked');
                container.find(`.perm-chk[data-module="${mod}"]`).prop('checked', checked);
            });
            // actualizar estado del select all
            container.on('change', '.perm-chk', function(){
                const mod = $(this).data('module');
                const $kids = container.find(`.perm-chk[data-module="${mod}"]`);
                const $all  = container.find(`.perm-mod-all[data-module="${mod}"]`);
                const total = $kids.length;
                const checked = $kids.filter(':checked').length;
                $all.prop('checked', checked === total)
                    .prop('indeterminate', checked > 0 && checked < total);
            });

            // set indeterminate inicial
            container.find('.perm-mod-all').each(function(){
                const mod = $(this).data('module');
                const $kids = container.find(`.perm-chk[data-module="${mod}"]`);
                const total = $kids.length;
                const checked = $kids.filter(':checked').length;
                $(this).prop('checked', checked === total)
                    .prop('indeterminate', checked > 0 && checked < total);
            });
        }

        // ------- Crear rol -------
        $('#btn-new-role').on('click', function(){
            $.confirm({
                title: 'Nuevo Rol',
                theme: 'modern', type: 'green', columnClass: 'xlarge',
                content: function(){
                    const $content = $(`
                          <form id="form-role" class="p-2">
                            <div class="form-group">
                              <label>Nombre del rol</label>
                              <input name="name" class="form-control" required maxlength="80" placeholder="ej. mozo">
                            </div>
                            <div id="perm-grid"></div>
                          </form>
                        `);
                    $content.find('#perm-grid').html(buildPermGrid([]));
                    setTimeout(()=> bindSelectAll($content), 0);
                    return $content;
                },
                buttons: {
                    guardar: {
                        text: 'Guardar', btnClass: 'btn-success',
                        action: function(){
                            const data = this.$content.find('#form-role').serialize();
                            return $.post("{{ route('roles.store') }}", data)
                                .done(resp => {
                                    if(!resp.ok) return toastr.error(resp.msg || 'No se pudo crear');
                                    toastr.success(resp.msg);
                                    loadRoles(stateR.page);
                                })
                                .fail(xhr => toastr.error(xhr.responseJSON?.message || 'Validación fallida'));
                        }
                    },
                    cancelar: { text:'Cancelar', btnClass:'btn-default' }
                }
            });
        });

        // ------- Editar rol -------
        $(document).on('click', '.btn-edit-role', function(){
            const id   = $(this).data('id');
            const name = $(this).data('name');

            // Obtener ids de permisos del rol
            $.get(`/dashboard/roles/${id}/perms`, {}, (res) => {
                if(!res.ok) return toastr.error('No se pudieron leer permisos del rol');

                $.confirm({
                    title: `Editar Rol: ${name}`,
                    theme: 'modern', type: 'orange', columnClass: 'xlarge',
                    content: function(){
                        const $content = $(`
                            <form id="form-role-edit" class="p-2">
                              <div class="form-group">
                                <label>Nombre del rol</label>
                                <input name="name" class="form-control" required maxlength="80" value="${name}">
                              </div>
                              <div id="perm-grid"></div>
                            </form>
                          `);
                        $content.find('#perm-grid').html(buildPermGrid(res.ids || []));
                        setTimeout(()=> bindSelectAll($content), 0);
                        return $content;
                    },
                    buttons: {
                        guardar: {
                            text: 'Guardar', btnClass: 'btn-primary',
                            action: function(){
                                const data = this.$content.find('#form-role-edit').serialize();
                                return $.post(`/dashboard/roles/${id}/update`, data)
                                    .done(resp => {
                                        if(!resp.ok) return toastr.error(resp.msg || 'No se pudo actualizar');
                                        toastr.success(resp.msg);
                                        $(`#role-row-${id}`).replaceWith(resp.html);
                                    })
                                    .fail(xhr => toastr.error(xhr.responseJSON?.message || 'Validación fallida'));
                            }
                        },
                        cancelar: { text:'Cancelar', btnClass:'btn-default' }
                    }
                });

            }, 'json').fail(()=> toastr.error('Error al cargar permisos del rol'));
        });

        // ------- Eliminar rol -------
        $(document).on('click', '.btn-del-role', function(){
            const id   = $(this).data('id');
            const name = $(this).data('name');

            $.confirm({
                title: 'Eliminar Rol',
                type: 'red', theme: 'modern',
                content: `<p>¿Eliminar el rol <strong>${name}</strong>?</p>`,
                buttons: {
                    eliminar: {
                        text: 'Eliminar', btnClass: 'btn-danger',
                        action: function(){
                            return $.post(`/dashboard/roles/${id}/destroy`, {})
                                .done(resp => {
                                    if(!resp.ok) return toastr.error(resp.msg || 'No se pudo eliminar');
                                    toastr.success(resp.msg);
                                    loadRoles(stateR.page);
                                })
                                .fail(()=> toastr.error('Error al eliminar'));
                        }
                    },
                    cancelar: { text:'Cancelar', btnClass:'btn-default' }
                }
            });
        });

        // Primera carga
        $(function(){ loadRoles(1); });
    </script>
@endsection