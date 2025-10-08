@extends('layouts.admin')

@section('openHelpCenter')
    menu-open
@endsection

@section('activeReclamos')
    active
@endsection

@section('activeReclamosIndex')
    active
@endsection

@section('title')
    Reclamos
@endsection

@section('page-title')
    <h2 class="font-weight-bold text-center">HOJA DE RECLAMACIÓN {{ $reclamo->codigo }} <span class="badge badge-info">{{ $reclamo->status_name }}</span></h2>
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('admin/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <!-- Summernote CSS -->
    <link href="{{ asset('admin/plugins/summernote/summernote-bs4.min.css') }}" rel="stylesheet">

    <link href="{{ asset('css/welcome/reclamacionShow.css') }}" rel="stylesheet">
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 offset-md-2">

                <div class="card mb-5">
                    <div class="card-header text-center">
                        <h5 class="mb-0">1. Identificación del Consumidor Reclamante</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="nombre">Nombres:</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" value="{{ $reclamo->nombre }}" readonly>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="apellido">Apellidos: </label>
                                <input type="text" class="form-control" id="apellido" name="apellido" value="{{ $reclamo->apellido }}" readonly>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="tipo_documento">Tipo documento: </label>
                                <input type="text" class="form-control" id="tipo_documento" name="tipo_documento" value="{{ $reclamo->tipo_documento }}" readonly>

                            </div>
                            <div class="form-group col-md-6">
                                <label for="numero_documento">N° Doc.: </label>
                                <input type="text" class="form-control" id="numero_documento" name="numero_documento" value="{{ $reclamo->numero_documento }}" readonly>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="telefono">Teléfono: </label>
                                <input type="text" class="form-control" id="telefono" name="telefono" value="{{ $reclamo->telefono }}" readonly>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="email">Email: </label>
                                <input type="text" class="form-control" id="email" name="email" value="{{ $reclamo->email }}" readonly>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="departamento">Departamento: </label>
                                <input type="text" class="form-control" id="departamento" name="departamento" value="{{ $reclamo->department->name }}" readonly>

                            </div>
                            <div class="form-group col-md-4">
                                <label for="provincia">Provincia: </label>
                                <input type="text" class="form-control" id="provincia" name="provincia" value="{{ $reclamo->province->name }}" readonly>

                            </div>
                            <div class="form-group col-md-4">
                                <label for="distrito">Distrito: </label>
                                <input type="text" class="form-control" id="distrito" name="distrito" value="{{ $reclamo->district->name }}" readonly>

                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label for="direccion">Dirección: </label>
                                <input type="text" class="form-control" id="direccion" name="direccion" value="{{ $reclamo->direccion }}" readonly>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="menor_edad">¿Eres menor de edad?</label>
                            <input type="text" class="form-control" id="menor_edad" name="menor_edad" value="{{ ($reclamo->menor_edad == 1) ? 'SI': 'NO' }}" readonly>

                        </div>
                        <!-- Sección de datos de los padres o representante -->
                        <div id="datos-representante" class="mt-3" style="{{ ( $reclamo->menor_edad == 1 ) ? '':'display: none;'  }}">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="nombre_representante">Nombres de uno de los padres o representante: </label>
                                    <input type="text" class="form-control" id="nombre_representante" name="nombre_representante" value="{{ $reclamo->nombre_representante }}" readonly>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="telefono_representante">Teléfono de uno de los padres o representante: </label>
                                    <input type="text" class="form-control" id="telefono_representante" name="telefono_representante" value="{{ $reclamo->telefono_representante }}" readonly>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="direccion_representante">Dirección de uno de los padres o representante: </label>
                                    <input type="text" class="form-control" id="direccion_representante" name="direccion_representante" value="{{ $reclamo->direccion_representante }}" readonly>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="correo_representante">Correo de uno de los padres o representante: </label>
                                    <input type="email" class="form-control" id="correo_representante" name="correo_representante" value="{{ $reclamo->correo_representante }}" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-5">
                    <div class="card-header text-center">
                        <h5 class="mb-0">2. Identificación del Bien Contratado</h5>
                    </div>
                    <div class="card-body">
                        <!-- Tipo de bien contratado -->
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="tipo_bien">Tipo:</label>
                                <input type="text" class="form-control" id="tipo_bien" name="tipo_bien" value="{{ $reclamo->tipo_bien }}" readonly>

                            </div>

                            <!-- Monto -->
                            <div class="form-group col-md-6">
                                <label for="monto">Monto:</label>
                                <input type="text" class="form-control" id="monto" name="monto" value="{{ $reclamo->monto }}" readonly>
                            </div>
                        </div>
                        <!-- Descripción del bien contratado -->
                        <div class="form-group">
                            <label for="descripcion">Descripción (Nombre del producto o servicio adquirido): </label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="4" maxlength="300" placeholder="Especificar el producto o servicio adquirido" readonly>{{ $reclamo->descripcion }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="card mb-5">
                    <div class="card-header text-center">
                        <h5 class="mb-0">3. Detalle de Reclamación y pedido del consumidor</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <!-- Tipo de reclamación -->
                            <div class="form-group col-md-6">
                                <label for="tipo_reclamacion">Reclamación:</label>
                                <input type="text" class="form-control" id="monto" name="monto" value="{{ $reclamo->tipo_reclamacion }}" readonly>

                            </div>

                            <!-- Canal de compra -->
                            <div class="form-group col-md-6">
                                <label for="canal">Canal: </label>
                                <input type="text" class="form-control" id="canal" name="canal"
                                       value="{{ $reclamo->canal == 'web' ? 'Tienda Virtual (fuegoymasa.com)' : ($reclamo->canal == 'whatsapp' ? 'Whatsapp' : 'Aplicación Móvil') }}">
                            </div>
                        </div>
                        <!-- Motivo y Submotivo -->
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="motivo">Motivo: </label>
                                <input type="text" class="form-control" id="motivo" name="motivo" value="{{ $reclamo->motive->nombre }}" readonly>

                            </div>
                            <div class="form-group col-md-6">
                                <label for="submotivo">Submotivo: </label>
                                <input type="text" class="form-control" id="submotivo" name="submotivo" value="{{ $reclamo->submotive->nombre }}" readonly>

                            </div>
                        </div>

                        <!-- Detalle del Reclamo o Queja -->
                        <div class="form-group">
                            <label for="detalle">Detalle: </label>
                            <textarea class="form-control" id="detalle" name="detalle" rows="4" maxlength="300" readonly>{{ $reclamo->detalle }}</textarea>

                        </div>

                        <!-- Pedido del cliente -->
                        <div class="form-group">
                            <label for="pedido_cliente">Pedido del cliente: </label>
                            <textarea class="form-control" id="pedido_cliente" name="pedido_cliente" rows="4" maxlength="300" readonly>{{ $reclamo->pedido_cliente }}</textarea>
                        </div>

                        <!-- Subir comprobante de pago -->
                        <div class="form-group">
                            <label>Comprobantes adjuntos:</label>
                            <div id="comprobante-container">
                                <!-- Se llenará desde backend -->
                                @foreach ($reclamo->comprobantes as $comprobante)
                                    <div class="comprobante-item mb-2"
                                         data-url="{{ asset($comprobante->archivo) }}"
                                         data-extension="{{ pathinfo($comprobante->archivo, PATHINFO_EXTENSION) }}">
                                    </div>
                                @endforeach
                            </div>
                        </div>

                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header text-center">
                        <h5 class="mb-0">4. Respuesta</h5>
                    </div>
                    <div class="card-body">
                        <!-- Detalle del Reclamo o Queja -->
                        <div class="form-group">
                            {{--<label for="respuesta">Respuesta: </label>--}}
                            <textarea class="form-control" id="respuesta" name="respuesta" rows="20" placeholder="Escriba la respuesta corta del reclamo">{!! $reclamo->respuesta !!}</textarea>
                        </div>

                    </div>
                    <input type="hidden" name="reclamo_id" id="reclamo_id" value="{{ $reclamo->id }}">
                </div>

                <!-- Botón de Envío -->
                <div class="form-group text-center mt-0">
                    <a href="{{ route('reclamos.index') }}" class="btn btn-primary">Regresar al listado</a>
                    @can('reclamos.gestionar')
                    <button type="button" id="btn-submit" data-url="{{ route('reclamos.guardarRespuesta') }}" class="btn btn-success">Guardar respuesta</button>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para PDF o imagen -->
    <div class="modal fade" id="comprobanteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Visualización de comprobante</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body" id="comprobante-content"></div>
            </div>
        </div>
    </div>

    <!-- Modal para mostrar comprobantes -->
    {{--<div class="modal fade" id="comprobanteModal" tabindex="-1" role="dialog" aria-labelledby="comprobanteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="comprobanteModalLabel">Comprobante</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center" id="comprobante-content">
                    <!-- Aquí se mostrará la imagen o el PDF -->
                </div>
            </div>
        </div>
    </div>
--}}
@endsection

@section('scripts')
    <!-- Summernote JS -->
    <script src="{{ asset('admin/plugins/summernote/summernote-bs4.min.js') }}"></script>

    <!-- Summernote español -->
    <script src="{{ asset('admin/plugins/summernote/lang/summernote-es-ES.min.js') }}"></script>

    <script src="{{ asset('js/reclamaciones/reclamacionShow.js') }}"></script>
@endsection
