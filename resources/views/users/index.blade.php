@extends('layouts.admin')

@section('openAccess')
    menu-open
@endsection

@section('activeAccess')
    active
@endsection

@section('activeUser')
    active
@endsection

@section('title','Usuarios')

@section('content')
    <div class="card">
        <div class="card-body">

            @can('usuarios.create')
            <div class="d-flex mb-3">
                <div class="ml-auto">
                    <button id="btn-new-user" class="btn btn-success">
                        <i class="fa fa-plus"></i> Nuevo Usuario
                    </button>
                </div>
            </div>
            @endcan
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead>
                    <tr>
                        <th style="width:60px;">#</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Roles</th>
                        <th style="width:100px;">Acciones</th>
                    </tr>
                    </thead>
                    <tbody id="user-tbody">
                    {{-- Puedes iniciar vacío y luego cargar con list(), o renderizar los primeros aquí --}}
                    </tbody>
                </table>
            </div>

            <div id="user-pager" class="mt-2"></div>
        </div>
    </div>

    <script>
        // Datos de roles disponibles (desde PHP)
        window.ROLES = @json($roles->pluck('name')); // ['admin','mozo','distribuidor',...]
    </script>
@endsection

@section('plugins')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery-confirm@3.3.4/dist/jquery-confirm.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery-confirm@3.3.4/dist/jquery-confirm.min.js"></script>
@endsection

@section('scripts')
    <script>
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

        // (opcional) cargar lista inicial
        function loadUsers(page=1){
            $.get("{{ route('users.list') }}", { page }, function(resp){
                if(resp.ok){
                    $('#user-tbody').html(resp.html.tbody);
                    $('#user-pager').html(resp.html.pager);
                }
            }, 'json');
        }

        // ---- Modal crear usuario ----
        $('#btn-new-user').on('click', function(){
            const roles = window.ROLES || [];
            const roleChecks = roles.map(r => {
                const id = 'role_'+r;
                return `
        <div class="custom-control custom-checkbox mr-3 mb-2">
          <input type="checkbox" class="custom-control-input role-chk" id="${id}" value="${r}" name="roles[]">
          <label class="custom-control-label" for="${id}">${r}</label>
        </div>`;
            }).join('');

            $.confirm({
                title: 'Nuevo Usuario',
                theme: 'modern', type: 'green', columnClass: 'medium',
                content:
                    `<form id="form-user" class="p-2">
                       <div class="form-group">
                         <label>Nombre</label>
                         <input name="name" class="form-control" required maxlength="120">
                       </div>
                       <div class="form-group">
                         <label>Email</label>
                         <input name="email" type="email" class="form-control" required maxlength="150">
                       </div>

                       <div class="form-group">
                         <label>Roles</label>
                         <div class="d-flex flex-wrap">${roleChecks}</div>
                         <small class="text-muted d-block">Puedes seleccionar uno o varios.</small>
                       </div>

                       <div class="form-group d-none" id="dist-extra">
                         <label>Teléfono (Distribuidor)</label>
                         <input name="phone" class="form-control" maxlength="30" placeholder="Ej. 999-999-999">
                       </div>
                     </form>`,
                buttons: {
                    guardar: {
                        text: 'Guardar',
                        btnClass: 'btn-success',
                        action: function(){
                            const $f = this.$content.find('#form-user');
                            // validaciones simples en cliente
                            if(!$f[0].checkValidity()){
                                $f[0].reportValidity();
                                return false;
                            }
                            // al menos 1 rol
                            if($f.find('.role-chk:checked').length === 0){
                                toastr.error('Selecciona al menos un rol.');
                                return false;
                            }

                            // En tu botón Guardar del modal:
                            return $.post("{{ route('users.store') }}", $f.serialize())
                                .done(resp => {
                                    if(!resp.ok) return toastr.error(resp.msg || 'No se pudo crear');
                                    toastr.success(resp.msg + (resp.temp_password ? ` — Clave temporal: ${resp.temp_password}` : ''));
                                    loadUsers(1);
                                })
                                .fail(xhr => {
                                    const json = xhr.responseJSON || {};
                                    // limpia errores previos
                                    clearFieldErrors($f);

                                    if(xhr.status === 422){
                                        // Mostrar errores de validación elegantes
                                        showFieldErrors($f, json.errors || {});
                                        // Mensaje general (si llega)
                                        if (json.message) toastr.error(json.message);
                                    } else {
                                        toastr.error(json.message || 'Error al guardar');
                                    }
                                });
                        }
                    },
                    cancelar: { text: 'Cancelar', btnClass: 'btn-default' }
                },
                onContentReady: function(){
                    const $dialog = this.$content;
                    // Mostrar campo teléfono si eligen "distribuidor"
                    function toggleDist(){
                        const isDistrib = $dialog.find('.role-chk[value="distribuidor"]').prop('checked');
                        $dialog.find('#dist-extra').toggleClass('d-none', !isDistrib);
                    }
                    $dialog.on('change', '.role-chk', toggleDist);
                    toggleDist();
                }
            });
        });

        // Inicial (si usas list AJAX)
        loadUsers(1);

        // Helpers para errores
        function clearFieldErrors($form){
            $form.find('.is-invalid').removeClass('is-invalid');
            $form.find('.invalid-feedback.dynamic').remove();
        }

        function showFieldErrors($form, errors){
            const msgs = [];
            Object.keys(errors || {}).forEach(function(name){
                const messages = errors[name];
                if (!messages || !messages.length) return;

                // para inputs tipo roles[] (checkboxes múltiples)
                let $inputs = $form.find(`[name="${name}"]`);
                if (!$inputs.length) {
                    // intenta roles[] como name[]
                    $inputs = $form.find(`[name="${name}[]"]`);
                }

                msgs.push(messages[0]);

                if ($inputs.length) {
                    // marcar el/los inputs
                    if ($inputs.attr('type') === 'checkbox') {
                        // si son checkboxes, marca el grupo (primer custom-control)
                        const $first = $inputs.first().closest('.custom-control');
                        $inputs.addClass('is-invalid');
                        if (!$first.find('.invalid-feedback.dynamic').length) {
                            $first.append(`<div class="invalid-feedback dynamic" style="display:block">${messages[0]}</div>`);
                        }
                    } else {
                        $inputs.addClass('is-invalid');
                        if (!$inputs.eq(0).next('.invalid-feedback').length) {
                            $inputs.eq(0).after(`<div class="invalid-feedback dynamic">${messages[0]}</div>`);
                        }
                    }
                }
            });
            if (msgs.length) {
                // toastr de resumen (primera línea)
                toastr.error(msgs[0]);
            }
        }

        // abrir modal editar
        $(document).on('click', '.btn-edit-user', function(){
            const id = $(this).data('id');

            $.get(`/dashboard/users/${id}/json`, {}, function(resp){
                if(!resp.ok) return toastr.error('No se pudo cargar el usuario');
                const u = resp.user;
                const roles = window.ROLES || [];

                const roleChecks = roles.map(r => {
                    const rid = 'role_'+r+'_edit';
                    const checked = (u.roles || []).includes(r) ? 'checked' : '';
                    return `
                        <div class="custom-control custom-checkbox mr-3 mb-2">
                          <input type="checkbox" class="custom-control-input role-chk" id="${rid}" value="${r}" name="roles[]" ${checked}>
                          <label class="custom-control-label" for="${rid}">${r}</label>
                        </div>`;
                }).join('');

                $.confirm({
                    title: `Editar Usuario #${u.id}`,
                    theme: 'modern', type: 'orange', columnClass: 'medium',
                    content:
                        `<form id="form-user-edit" class="p-2">
                           <div class="form-group">
                             <label>Nombre</label>
                             <input name="name" class="form-control" required maxlength="120" value="${ u.name}">
                           </div>
                           <div class="form-group">
                             <label>Email</label>
                             <input name="email" type="email" class="form-control" required maxlength="150" value="${u.email}">
                           </div>

                           <div class="form-group">
                             <label>Roles</label>
                             <div class="d-flex flex-wrap">${roleChecks}</div>
                           </div>

                           <div class="form-group ${u.is_distribuidor ? '' : 'd-none'}" id="dist-extra-edit">
                             <label>Teléfono (Distribuidor)</label>
                             <input name="phone" class="form-control" maxlength="30" value="${u.phone ? u.phone : ''}">
                           </div>
                         </form>`,
                    buttons: {
                        guardar: {
                            text: 'Guardar cambios', btnClass: 'btn-primary',
                            action: function(){
                                const $f = this.$content.find('#form-user-edit');
                                clearFieldErrors($f);

                                if($f.find('.role-chk:checked').length === 0){
                                    toastr.error('Selecciona al menos un rol.');
                                    return false;
                                }

                                return $.post(`/dashboard/users/${id}/update`, $f.serialize())
                                    .done(res => {
                                        if(!res.ok) return toastr.error(res.msg || 'No se pudo actualizar');
                                        toastr.success(res.msg);
                                        // reemplazar fila en tabla si viene html
                                        if(res.html){
                                            $(`#user-row-${id}`).replaceWith(res.html);
                                        } else {
                                            // o refrescar listado si usas paginación AJAX
                                            // loadUsers(1);
                                        }
                                    })
                                    .fail(xhr => {
                                        const json = xhr.responseJSON || {};
                                        if (xhr.status === 422) {
                                            showFieldErrors($f, json.errors || {});
                                            toastr.error(json.message || 'Revisa los datos');
                                        } else {
                                            toastr.error(json.message || 'Error al actualizar');
                                        }
                                    });
                            }
                        },
                        cancelar: { text: 'Cancelar', btnClass: 'btn-default' }
                    },
                    onContentReady: function(){
                        const $dlg = this.$content;
                        // toggle phone
                        function toggleDist(){
                            const isDistrib = $dlg.find('.role-chk[value="distribuidor"]').prop('checked');
                            $dlg.find('#dist-extra-edit').toggleClass('d-none', !isDistrib);
                        }
                        $dlg.on('change', '.role-chk', toggleDist);
                        toggleDist();
                    }
                });
            }, 'json').fail(()=> toastr.error('Error al obtener datos del usuario'));
        });

        // Eliminar (soft) → reemplaza fila por versión "eliminada"
        $(document).on('click', '.btn-del-user', function(){
            const id = $(this).data('id');
            const name = $(this).data('name');

            $.confirm({
                title: 'Eliminar Usuario',
                content: `¿Seguro que deseas eliminar a <strong>${name}</strong>?`,
                theme: 'modern', type: 'red',
                buttons: {
                    eliminar: {
                        text: 'Eliminar', btnClass: 'btn-danger',
                        action: function(){
                            return $.post(`/dashboard/users/${id}/destroy`, {})
                                .done(res => {
                                    if(!res.ok) return toastr.error(res.msg || 'No se pudo eliminar');
                                    toastr.success(res.msg);
                                    if(res.html){
                                        $(`#user-row-${id}`).replaceWith(res.html);
                                    } else {
                                        // Si prefieres, podrías recargar la página actual:
                                        // loadUsers(1);
                                    }
                                })
                                .fail(()=> toastr.error('Error al eliminar'));
                        }
                    },
                    cancelar: { text: 'Cancelar', btnClass: 'btn-default' }
                }
            });
        });

        // Restaurar → reemplaza fila por versión "activa"
        $(document).on('click', '.btn-restore-user', function(){
            const id = $(this).data('id');
            const name = $(this).data('name');

            $.confirm({
                title: 'Restaurar Usuario',
                content: `¿Restaurar a <strong>${name}</strong>?`,
                theme: 'modern', type: 'green',
                buttons: {
                    restaurar: {
                        text: 'Restaurar', btnClass: 'btn-success',
                        action: function(){
                            return $.post(`/dashboard/users/${id}/restore`, {})
                                .done(res => {
                                    if(!res.ok) return toastr.error(res.msg || 'No se pudo restaurar');
                                    toastr.success(res.msg);
                                    if(res.html){
                                        $(`#user-row-${id}`).replaceWith(res.html);
                                    }
                                })
                                .fail(()=> toastr.error('Error al restaurar'));
                        }
                    },
                    cancelar: { text: 'Cancelar', btnClass: 'btn-default' }
                }
            });
        });

        // Delegado: manejar clics en la paginación
        $(document).on('click', '.user-page-link', function (e) {
            e.preventDefault();

            const $li   = $(this).closest('.page-item');
            const page  = parseInt($(this).data('page'), 10);

            // Ignorar si no hay page, si está deshabilitado o ya es la página activa
            if (!page || $li.hasClass('disabled') || $li.hasClass('active')) return;

            // (opcional) si tienes buscador o per_page, pásalos también
            const q       = $('#user-search').val() || '';
            const perPage = $('#user-per-page').val() || 20;

            loadUsers(page, q, perPage);
        });

        // Mejora: adapta loadUsers para aceptar filtros
        function loadUsers(page = 1, q = '', per_page = 20) {
            $.get("{{ route('users.list') }}", { page, q, per_page }, function (resp) {
                if (resp.ok) {
                    $('#user-tbody').html(resp.html.tbody);
                    $('#user-pager').html(resp.html.pager);
                    // (opcional) scroll al inicio de la tabla
                    // document.querySelector('#user-tbody').scrollIntoView({ behavior: 'smooth', block: 'start' });
                } else {
                    toastr.error('No se pudo cargar la lista.');
                }
            }, 'json').fail(() => toastr.error('Error cargando usuarios.'));
        }

        // Si tienes buscador y selector de “por página”, engánchalos:
        $('#user-search').on('keyup', _.debounce(function(){
            const q = $(this).val();
            const per = $('#user-per-page').val() || 20;
            loadUsers(1, q, per);
        }, 300));

        $('#user-per-page').on('change', function(){
            const q = $('#user-search').val() || '';
            const per = $(this).val() || 20;
            loadUsers(1, q, per);
        });
    </script>
@endsection