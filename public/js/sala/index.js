$(document).ready(function () {
    // Delegación: click en cualquier .btn-sala (aunque el HTML sea reemplazado después)
    $(document).on('click', '.btn-sala', function () {
        const $btn = $(this);
        const salaId = $btn.data('sala-id');

        $('.btn-sala').removeClass('active');
        $btn.addClass('active');

        $.get(`/dashboard/salas/${salaId}/mesas`, function (resp) {
            if (resp.ok && resp.html) {
                $('#mesas-wrap').html(resp.html);
            } else {
                toastr.error(resp.msg || 'No se pudieron cargar las mesas.');
            }
        }, 'json')
            .fail(() => toastr.error('Error al cargar mesas.'));
    });

    // Click en una mesa para abrir modal de apertura
    /*$(document).on('click', '.mesa-card', function(){
        const mesaId   = $(this).data('id');
        const mesaName = $(this).find('.name').text().trim();

        // Construimos las opciones del select
        const mozoOptions = (window.MOZOS || [])
            .map(m => `<option value="${m.id}">${m.nombre}</option>`)
            .join('');

        $.confirm({
            title: `Abrir ${mesaName}`,
            columnClass: 'medium',
            theme: 'modern',
            type: 'blue',
            content:
                `<form id="form-abrir-mesa" class="p-2">
                     <div class="form-group">
                       <label>Colaborador encargado</label>
                       <select name="mozo_id" id="mozo_id" class="form-control" required>
                         ${mozoOptions}
                       </select>
                     </div>
            
                     <div class="form-group">
                       <label>Número de personas:</label>
                       <div class="btn-group btn-group-sm btn-group-toggle d-block mb-2" data-toggle="buttons">
                         ${[1,2,3,4,5,6,7].map(n => `
                           <label class="btn btn-outline-primary mr-1 mb-1 pick-personas">
                             <input type="radio" name="personas_pick" value="${n}"> ${n}
                           </label>`).join('')}
                         <label class="btn btn-outline-secondary mb-1 pick-personas" id="btn-otro">
                           <input type="radio" name="personas_pick" value="otro"> Otro
                         </label>
                       </div>
                       <input type="number" name="personas" class="form-control" min="1" max="50" value="1">
                     </div>
            
                     <div class="form-group">
                       <label>Comentario</label>
                       <textarea name="comentario" class="form-control" rows="2" maxlength="500"></textarea>
                     </div>
                   </form>`,
            buttons: {
                abrir: {
                    text: 'Abrir mesa',
                    btnClass: 'btn-primary',
                    action: function(){
                        const $f = this.$content.find('#form-abrir-mesa');

                        // sincronizar personas
                        const picked = $f.find('input[name="personas_pick"]:checked').val();
                        if (picked && picked !== 'otro') {
                            $f.find('input[name="personas"]').val(picked);
                        }

                        return $.post(`/dashboard/mesas/${mesaId}/abrir`, $f.serialize())
                            .done(resp => {
                                if (resp.ok) {
                                    toastr.success(resp.msg);
                                    window.location = resp.redirect_url;
                                } else {
                                    toastr.error(resp.msg || 'No se pudo abrir la mesa');
                                }
                            })
                            .fail(xhr => toastr.error(xhr.responseJSON?.message || 'Error de validación'));
                    }
                },
                cancelar: { text: 'Cancelar', btnClass: 'btn-default' }
            },
            onContentReady: function(){
                const $f = this.$content.find('#form-abrir-mesa');

                if (window.CURRENT_MOZO_ID) {
                    // setear y bloquear el select
                    $f.find('#mozo_id').val(String(window.CURRENT_MOZO_ID)).prop('disabled', true);
                    // agregar hidden espejo (esto sí se serializa)
                    if (!$f.find('input[name="mozo_id"]').length) {
                        $('<input>', { type:'hidden', name:'mozo_id', value:String(window.CURRENT_MOZO_ID) })
                            .appendTo($f);
                    }
                }

                // Personas: seleccionar "1" por defecto
                $f.find('.pick-personas').first().addClass('active');

                $f.on('click', '.pick-personas', function(){
                    const val = $(this).find('input').val();
                    if (val !== 'otro') $f.find('input[name="personas"]').val(val);
                    $('.pick-personas').removeClass('active');
                    $(this).addClass('active');
                });

                // Preseleccionar y bloquear si el usuario logueado es mozo
                if (window.CURRENT_MOZO_ID) {
                    $f.find('#mozo_id').val(String(window.CURRENT_MOZO_ID)).prop('disabled', true);
                }
            }
        });
    });*/

    $(document).on('click', '.mesa-card', function(){
        const mesaId   = $(this).data('id');
        const mesaName = $(this).find('.name').text().trim();

        $.get(`/dashboard/mesas/${mesaId}/acceso`, function(resp){
            if (!resp.ok) {
                toastr.error(resp.msg || 'No se pudo validar el acceso.');
                return;
            }

            if (resp.estado === 'libre') {
                // 👉 mesa libre: abrir modal "Abrir mesa" (reutiliza el que ya tienes)
                abrirModalAbrirMesa(mesaId, mesaName);  // extrae tu código del $.confirm a esta función
                return;
            }

            // estado = ocupada
            if (resp.can_access && resp.redirect_url) {
                // 👉 es el mozo asignado: entrar a gestionar
                window.location = resp.redirect_url;
                return;
            }

            // 👉 no es el mozo asignado: mostrar alerta
            $.confirm({
                title: 'Acceso restringido',
                type: 'red',
                theme: 'modern',
                content:
                    `<div class="p-2">
           <p>Esta mesa ya está siendo atendida por <strong>${resp.mozo_nombre || 'otro mozo'}</strong>.</p>
         </div>`,
                buttons: {
                    ok: { text: 'Entendido', btnClass: 'btn-danger' }
                }
            });
        }, 'json').fail(() => toastr.error('Error al validar acceso.'));
    });
});

function abrirModalAbrirMesa(mesaId, mesaName){
    // TODO: pega aquí tal cual tu $.confirm de “Abrir mesa”
    // usando window.MOZOS, CURRENT_MOZO_ID, hidden mozo_id si disabled, etc.
    // Construimos las opciones del select
    const mozoOptions = (window.MOZOS || [])
        .map(m => `<option value="${m.id}">${m.nombre}</option>`)
        .join('');

    $.confirm({
        title: `Abrir ${mesaName}`,
        columnClass: 'medium',
        theme: 'modern',
        type: 'blue',
        content:
            `<form id="form-abrir-mesa" class="p-2">
                     <div class="form-group">
                       <label>Colaborador encargado</label>
                       <select name="mozo_id" id="mozo_id" class="form-control" required>
                         ${mozoOptions}
                       </select>
                     </div>
            
                     <div class="form-group">
                       <label>Número de personas:</label>
                       <div class="btn-group btn-group-sm btn-group-toggle d-block mb-2" data-toggle="buttons">
                         ${[1,2,3,4,5,6,7].map(n => `
                           <label class="btn btn-outline-primary mr-1 mb-1 pick-personas">
                             <input type="radio" name="personas_pick" value="${n}"> ${n}
                           </label>`).join('')}
                         <label class="btn btn-outline-secondary mb-1 pick-personas" id="btn-otro">
                           <input type="radio" name="personas_pick" value="otro"> Otro
                         </label>
                       </div>
                       <input type="number" name="personas" class="form-control" min="1" max="50" value="1">
                     </div>
            
                     <div class="form-group">
                       <label>Comentario</label>
                       <textarea name="comentario" class="form-control" rows="2" maxlength="500"></textarea>
                     </div>
                   </form>`,
        buttons: {
            abrir: {
                text: 'Abrir mesa',
                btnClass: 'btn-primary',
                action: function(){
                    const $f = this.$content.find('#form-abrir-mesa');

                    // sincronizar personas
                    const picked = $f.find('input[name="personas_pick"]:checked').val();
                    if (picked && picked !== 'otro') {
                        $f.find('input[name="personas"]').val(picked);
                    }

                    return $.post(`/dashboard/mesas/${mesaId}/abrir`, $f.serialize())
                        .done(resp => {
                            if (resp.ok) {
                                toastr.success(resp.msg);
                                window.location = resp.redirect_url;
                            } else {
                                toastr.error(resp.msg || 'No se pudo abrir la mesa');
                            }
                        })
                        .fail(xhr => toastr.error(xhr.responseJSON?.message || 'Error de validación'));
                }
            },
            cancelar: { text: 'Cancelar', btnClass: 'btn-default' }
        },
        onContentReady: function(){
            const $f = this.$content.find('#form-abrir-mesa');

            if (window.CURRENT_MOZO_ID) {
                // setear y bloquear el select
                $f.find('#mozo_id').val(String(window.CURRENT_MOZO_ID)).prop('disabled', true);
                // agregar hidden espejo (esto sí se serializa)
                if (!$f.find('input[name="mozo_id"]').length) {
                    $('<input>', { type:'hidden', name:'mozo_id', value:String(window.CURRENT_MOZO_ID) })
                        .appendTo($f);
                }
            }

            // Personas: seleccionar "1" por defecto
            $f.find('.pick-personas').first().addClass('active');

            $f.on('click', '.pick-personas', function(){
                const val = $(this).find('input').val();
                if (val !== 'otro') $f.find('input[name="personas"]').val(val);
                $('.pick-personas').removeClass('active');
                $(this).addClass('active');
            });

            // Preseleccionar y bloquear si el usuario logueado es mozo
            if (window.CURRENT_MOZO_ID) {
                $f.find('#mozo_id').val(String(window.CURRENT_MOZO_ID)).prop('disabled', true);
            }
        }
    });
}