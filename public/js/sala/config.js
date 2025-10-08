$(document).ready(function () {
    // Abrir modal crear sala
    $('#btn-add-sala').on('click', function(){
        $.confirm({
            title: '<i class="fas fa-plus mr-1"></i> Crear Sala',
            columnClass: 'medium',
            type: 'blue',
            theme: 'modern',
            content: '' +
                '<form id="form-sala" class="p-2">' +
                '   <input type="hidden" name="id" value="">' +
                '   <div class="form-group">' +
                '       <label>Nombre</label>' +
                '       <input type="text" name="nombre" class="form-control" required maxlength="120">' +
                '   </div>' +
                '   <div class="form-group">' +
                '       <label>Descripción</label>' +
                '       <input type="text" name="descripcion" class="form-control" maxlength="500">' +
                '   </div>' +
                '</form>',
            buttons: {
                guardar: {
                    text: 'Guardar',
                    btnClass: 'btn-primary',
                    action: function(){
                        const $form = this.$content.find('#form-sala');
                        const data  = $form.serialize();

                        return $.post("/dashboard/salas", data)   // tu ruta absoluta
                            .done(resp => {
                                if (resp.ok) {
                                    // 1) Agregar o reemplazar chip en #salas-wrap
                                    if (resp.html) {
                                        const $exists = $(`.cfg-sala-card[data-id="${resp.sala.id}"]`);
                                        if ($exists.length) {
                                            $exists.replaceWith(resp.html);
                                        } else {
                                            $('#salas-wrap').append(resp.html);
                                        }
                                    }

                                    // 2) Si setActive: marcar activo y refrescar mesas
                                    if (resp.setActive) {
                                        $('.cfg-sala-card').removeClass('active');
                                        const $newChip = $(`.cfg-sala-card[data-id="${resp.sala.id}"]`);
                                        $newChip.addClass('active');
                                        if (resp.mesasHtml) {
                                            $('#mesas-wrap').html(resp.mesasHtml);
                                        }
                                    }

                                    toastr.success(resp.msg);
                                } else {
                                    toastr.error(resp.msg || 'No se pudo crear la sala');
                                }
                            })
                            .fail(xhr => {
                                toastr.error(xhr.responseJSON?.message || 'Error en validación');
                            });
                    }
                },
                cancelar: {
                    text: 'Cancelar',
                    btnClass: 'btn-default'
                }
            }
        });
    });

    // Editar sala (cargar datos)
    $(document).on('click', '.btn-edit-sala', function(e){
        const $card      = $(this).closest('.cfg-sala-card');
        const wasActive  = $card.hasClass('active');
        const isDeleted  = Number($card.data('deleted')) === 1;

        // Construimos el objeto "buttons" según estado
        const buttons = {
            guardar: {
                text: 'Guardar',
                btnClass: 'btn-primary',
                action: function(){
                    const $form = this.$content.find('#form-sala');
                    const data  = $form.serialize();
                    const id    = $card.data('id');

                    return $.post(`/dashboard/salas/${id}/update`, data)
                        .done(resp => {
                            if (resp.ok) {
                                if (resp.html) {
                                    const $newChip = $(resp.html);
                                    // conservar active si lo era
                                    if (wasActive) $newChip.addClass('active');
                                    $card.replaceWith($newChip);
                                } else {
                                    // fallback
                                    $card.attr('data-nombre', resp.sala.nombre);
                                    $card.attr('data-descripcion', resp.sala.descripcion || '');
                                    $card.find('.sala-name').text(resp.sala.nombre);
                                }
                                toastr.success(resp.msg);
                            } else {
                                toastr.error(resp.msg || 'No se pudo actualizar la sala');
                            }
                        })
                        .fail(xhr => toastr.error(xhr.responseJSON?.message || 'Error en validación'));
                }
            },
            cancelar: { text: 'Cancelar', btnClass: 'btn-default' }
        };

        // Botón Eliminar o Restaurar según estado
        if (!isDeleted) {
            buttons.eliminar = {
                text: 'Eliminar',
                btnClass: 'btn-danger',
                action: function(){
                    const id = $card.data('id');
                    return $.post('/dashboard/salas/destroy', { id })
                        .done(resp => {
                            if (resp.ok && resp.html) {
                                const $newChip = $(resp.html);
                                // conservar active si lo era (opcional: probablemente no sea necesario)
                                if (wasActive) $newChip.addClass('active');
                                $('#salas-wrap').find(`.cfg-sala-card[data-id="${resp.id}"]`).replaceWith($newChip);
                                toastr.success(resp.msg);
                            } else {
                                toastr.error(resp.msg || 'No se pudo eliminar la sala');
                            }
                        })
                        .fail(xhr => toastr.error(xhr.responseJSON?.message || 'Error'));
                }
            };
        } else {
            buttons.restaurar = {
                text: 'Restaurar',
                btnClass: 'btn-success',
                action: function(){
                    const id = $card.data('id');
                    return $.post('/dashboard/salas/restore', { id })
                        .done(resp => {
                            if (resp.ok && resp.html) {
                                const $newChip = $(resp.html);
                                if (wasActive) $newChip.addClass('active');
                                $('#salas-wrap').find(`.cfg-sala-card[data-id="${id}"]`).replaceWith($newChip);
                                toastr.success(resp.msg);
                            } else {
                                toastr.error(resp.msg || 'No se pudo restaurar la sala');
                            }
                        })
                        .fail(xhr => toastr.error(xhr.responseJSON?.message || 'Error'));
                }
            };
        }

        // Abrimos el confirm con los botones armados
        $.confirm({
            title: '<i class="fas fa-pencil-alt mr-1"></i> Editar Sala',
            columnClass: 'medium',
            type: isDeleted ? 'red' : 'orange',
            theme: 'modern',
            content:
                '<form id="form-sala" class="p-2">' +
                `  <input type="hidden" name="id" value="${$card.data('id')}">` +
                '  <div class="form-group">' +
                '    <label>Nombre</label>' +
                `    <input type="text" name="nombre" class="form-control" required maxlength="120" value="${$card.data('nombre')}">` +
                '  </div>' +
                '  <div class="form-group">' +
                '    <label>Descripción</label>' +
                `    <input type="text" name="descripcion" class="form-control" maxlength="500" value="${($card.data('descripcion') || '')}">` +
                '  </div>' +
                '</form>',
            buttons
        });
    });

    // Click en ficha de sala: marcar activa y cargar mesas
    $(document).on('click', '.cfg-sala-card', function(e){
        if($(e.target).closest('.btn-edit-sala').length) return;

        $('.cfg-sala-card').removeClass('active');
        $(this).addClass('active');

        const salaId = $(this).data('id');

        $.get(`/dashboard/salas/config/mesas/${salaId}`, function(resp){
            if(resp.ok && resp.html){
                $('#mesas-wrap').html(resp.html);
            } else {
                toastr.error(resp.msg || 'No se pudieron cargar las mesas');
            }
        }, 'json');
    });

    // Crear mesa
    $(document).on('click', '#btn-add-mesa', function(){
        const $activeSala = $('.cfg-sala-card.active');
        if (!$activeSala.length) {
            toastr.warning('Selecciona primero una sala.');
            return;
        }
        const salaId = $activeSala.data('id');

        $.confirm({
            title: '<i class="fas fa-plus mr-1"></i> Crear Mesa',
            columnClass: 'medium',
            theme: 'modern',
            content:
                `<form id="form-mesa" class="p-2">
                     <input type="hidden" name="sala_id" value="${salaId}">
                     <div class="form-group">
                       <label>Nombre</label>
                       <input type="text" name="nombre" class="form-control" required maxlength="120">
                     </div>
                     <div class="form-group">
                       <label>Descripción</label>
                       <input type="text" name="descripcion" class="form-control" maxlength="500">
                     </div>
                   </form>`,
            buttons: {
                guardar: {
                    text: 'Guardar', btnClass: 'btn-primary',
                    action: function(){
                        const data = this.$content.find('#form-mesa').serialize();
                        return $.post('/dashboard/mesas', data)
                            .done(resp => {
                                if (resp.ok && resp.html) {
                                    // 🔸 si existe el placeholder, quítalo
                                    $('#mesas-wrap .mesas-empty').remove();
                                    // 🔸 agrega el chip
                                    $('#mesas-wrap').append(resp.html);
                                    toastr.success(resp.msg);
                                } else {
                                    toastr.error(resp.msg || 'No se pudo crear la mesa');
                                }
                            })
                            .fail(xhr => toastr.error(xhr.responseJSON?.message || 'Error en validación'));
                    }
                },
                cancelar: { text: 'Cancelar', btnClass: 'btn-default' }
            }
        });
    });

// Editar mesa (ícono lápiz)
    $(document).on('click', '.btn-edit-mesa', function (e) {
        e.stopPropagation();

        const $chip       = $(this).closest('.cfg-mesa-card');
        const id          = $chip.data('id');
        const nombre      = $chip.data('nombre') || '';
        const descripcion = $chip.data('descripcion') || '';
        const estado      = $chip.data('estado') || 'cerrada';
        const isDeleted   = Number($chip.data('deleted')) === 1;

        // Botonera dinámica según estado
        const buttons = {
            guardar: {
                text: 'Guardar',
                btnClass: 'btn-primary',
                action: function () {
                    const $form = this.$content.find('#form-mesa');
                    const data  = $form.serialize();

                    return $.post(`/dashboard/mesas/${id}/update`, data)
                        .done(resp => {
                            if (resp.ok && resp.html) {
                                replaceMesaChip(id, resp.html);
                                toastr.success(resp.msg);
                            } else {
                                toastr.error(resp.msg || 'No se pudo actualizar la mesa');
                            }
                        })
                        .fail(xhr => toastr.error(xhr.responseJSON?.message || 'Error en validación'));
                }
            },
            cancelar: { text: 'Cancelar', btnClass: 'btn-default' }
        };

        if (!isDeleted) {
            // Mostrar ELIMINAR
            buttons.eliminar = {
                text: 'Eliminar',
                btnClass: 'btn-danger',
                action: function () {
                    return $.post('/dashboard/mesas/destroy', { id })
                        .done(resp => {
                            if (resp.ok && resp.html) {
                                replaceMesaChip(id, resp.html); // vendrá con clase .deleted
                                toastr.success(resp.msg);
                            } else {
                                toastr.error(resp.msg || 'No se pudo eliminar la mesa');
                            }
                        })
                        .fail(xhr => toastr.error(xhr.responseJSON?.message || 'Error'));
                }
            };
        } else {
            // Mostrar RESTAURAR
            buttons.restaurar = {
                text: 'Restaurar',
                btnClass: 'btn-success',
                action: function () {
                    return $.post('/dashboard/mesas/restore', { id })
                        .done(resp => {
                            if (resp.ok && resp.html) {
                                replaceMesaChip(id, resp.html); // quitará .deleted
                                toastr.success(resp.msg);
                            } else {
                                toastr.error(resp.msg || 'No se pudo restaurar la mesa');
                            }
                        })
                        .fail(xhr => toastr.error(xhr.responseJSON?.message || 'Error'));
                }
            };
        }

        // Popup con formulario prellenado
        $.confirm({
            title: `<i class="fas fa-pencil-alt mr-1"></i> Editar Mesa ${nombre}`,
            columnClass: 'medium',
            type: isDeleted ? 'red' : 'orange',
            theme: 'modern',
            content:
                `<form id="form-mesa" class="p-2">
                 <input type="hidden" name="id" value="${id}">
                 <div class="form-group">
                   <label>Nombre</label>
                   <input type="text" name="nombre" class="form-control" required maxlength="120" value="${nombre}">
                 </div>
                 <div class="form-group">
                   <label>Estado</label>
                   <select name="estado" class="form-control">
                     <option value="libre"   ${estado==='libre'   ? 'selected' : ''}>Libre</option>
                     <option value="ocupada" ${estado==='ocupada' ? 'selected' : ''}>Ocupada</option>
                   </select>
                 </div>
                 <div class="form-group">
                   <label>Descripción</label>
                   <input type="text" name="descripcion" class="form-control" maxlength="500" value="${descripcion}">
                 </div>
               </form>`,
            buttons
        });
    });
});

// Utilidad: reemplazar chip de mesa por HTML del backend
function replaceMesaChip(id, html) {
    const $old = $(`.cfg-mesa-card[data-id="${id}"]`);
    const $new = $(html);
    if ($old.length) $old.replaceWith($new);
    else $('#mesas-wrap').append($new);
}