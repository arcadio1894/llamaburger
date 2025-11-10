@extends('layouts.admin')

@section('openOrders')
    menu-open
@endsection

@section('activeOrders')
    active
@endsection

@section('activeListInvoices')
    active
@endsection

@section('title')
    Comprobantes de Mesa/Externos
@endsection

@section('content')
    <div class="card">
        <div class="card-header"><h3 class="card-title">Comprobantes</h3></div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
                <thead>
                <tr>
                    <th>Fecha pedido</th>
                    <th>Nombre cliente</th>
                    <th>Total</th>
                    <th>Método de pago</th>
                    <th>Dato pago (efectivo)</th>
                    <th>Opciones</th>
                </tr>
                </thead>
                <tbody>
                @foreach($invoices as $inv)
                    @php
                        // Fecha (issue_date o created_at)
                        $fecha = optional($inv->issue_date ?? $inv->created_at)
                                  ->timezone('America/Lima')
                                  ->format('Y-m-d H:i');

                        // Monto efectivo (si hay)
                        $referencia = $inv->payments;


                        // Link PDF si está en extra
                        $pdf = data_get($inv->extra, 'enlace_archivo_pdf') ?? data_get($inv->extra, 'links.pdf');
                    @endphp
                    <tr>
                        <td>{{ $fecha }}</td>
                        <td>
                            {{
                              $inv->cliente_nombre
                              ?? optional($inv->customer)->nombre
                              ?? optional($inv->customer)->name
                              ?? optional($inv->customer)->razon_social
                              ?? optional($inv->customer)->full_name
                              ?? '-'
                            }}
                        </td>
                        <td>
                            {{ $inv->payment_amount ? 'S/ '.number_format($inv->payment_amount, 2) : '-' }}
                        </td>
                        <td>
                            {{ $inv->paymentMethod->name ?? '-' }}
                        </td>
                        <td>{{ $inv->display_pago }}</td>
                        <td>
                            <button class="btn btn-xs btn-primary"
                                    data-invoice-items="{{ $inv->id }}">
                                Ver detalles
                            </button>

                            @if($pdf)
                                <a class="btn btn-xs btn-info"
                                   href="{{ route('invoices.print', $inv) }}"
                                   target="_blank" rel="noopener">
                                    Imprimir
                                </a>
                            @else
                                <a class="btn btn-xs btn-info"
                                   href="{{ route('invoices.print', $inv) }}" target="_blank">
                                    Imprimir
                                </a>
                            @endif

                            @php $isAnulado = $inv->estado === 'anulado'; @endphp
                            <button
                                    class="btn btn-xs btn-danger {{ $isAnulado ? 'disabled' : '' }}"
                                    data-invoice-void="{{ $inv->id }}"
                                    {{ $isAnulado ? 'disabled' : '' }}>
                                {{ $isAnulado ? 'Anulado' : 'Anular' }}
                            </button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $invoices->links() }}
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('js/invoice/index.js') }}?v={{ time() }}"></script>
@endsection