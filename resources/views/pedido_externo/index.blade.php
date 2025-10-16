@extends('layouts.admin')

@section('title','Pedidos Externos')

@section('activeListPedidoExterno','active')

@push('styles')
    <style>
        /* Toques sutiles */
        .ed-card {
            border: 1px solid rgba(0,0,0,.06);
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 6px 20px rgba(0,0,0,.06);
            transition: transform .2s ease, box-shadow .2s ease;
            background: #fff;
        }
        .ed-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(0,0,0,.10);
        }
        .ed-chip {
            font-size: .75rem;
            border-radius: 9999px;
            padding: .3rem .6rem;
        }
        .ed-kv {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: .25rem .75rem;
            font-size: .95rem;
        }
        .ed-kv small { color: #6c757d; }
        .ed-actions .btn {
            border-radius: .75rem;
        }
        .sticky-filters {
            position: sticky; top: .5rem; z-index: 3; background: transparent;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">

        {{-- Encabezado + CTA --}}
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3">
            <div>
                <h4 class="mb-0">Pedidos Externos</h4>
                <small class="text-muted">Gestión rápida de pedidos sin mesa.</small>
            </div>
            <form method="POST" action="{{ route('pedido.externo.crear') }}">
                @csrf
                <button class="btn btn-dark px-4">
                    <i class="bi bi-plus-lg me-1"></i> Nuevo Pedido Externo
                </button>
            </form>
        </div>

        {{-- Filtros / Búsqueda --}}
        <div class="sticky-filters mb-3">
            <form method="GET" action="{{ route('pedido.externo.index') }}" class="card border-0 shadow-sm rounded-4">
                <div class="card-body py-3">
                    <div class="row g-2 align-items-center">
                        <div class="col-12 col-md-5">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i class="fas fa-search"></i></span>
                                <input type="search" name="q" value="{{ request('q') }}"
                                       class="form-control border-start-0" placeholder="Buscar por #ID o comentario...">
                            </div>
                        </div>

                        <div class="col-12 col-md-5">
                            <div class="d-flex flex-wrap gap-2">
                                @php
                                    $estados = ['' => 'Todos', 'abierta'=>'Abierta', 'en_proceso'=>'En proceso', 'por_pagar'=>'Por pagar', 'cerrada'=>'Cerrada', 'anulada'=>'Anulada'];
                                    $pick    = request('estado','');
                                @endphp
                                @foreach($estados as $key=>$label)
                                    <button name="estado" value="{{ $key }}" type="submit"
                                            class="btn btn-sm {{ ($pick===$key)?'btn-primary':'btn-outline-secondary' }}">
                                        {{ $label }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        {{--<div class="col-12 col-md-2 text-md-end">
                            <select name="sort" class="form-select" onchange="this.form.submit()">
                                @php $sort = request('sort','recientes'); @endphp
                                <option value="recientes" {{ $sort=='recientes'?'selected':'' }}>Más recientes</option>
                                <option value="antiguos"  {{ $sort=='antiguos'?'selected':'' }}>Más antiguos</option>
                            </select>
                        </div>--}}
                    </div>
                </div>
            </form>
        </div>

        {{-- Grid de cards --}}
        @php
            function estadoBadgeClass($estado) {
                switch ($estado) {
                    case 'abierta':    return 'bg-warning text-dark';
                    case 'en_proceso': return 'bg-info text-dark';
                    case 'por_pagar':  return 'bg-primary';
                    case 'cerrada':    return 'bg-success';
                    case 'anulada':    return 'bg-secondary';
                    default:           return 'bg-light text-dark';
                }
            }
        @endphp

        @if($atenciones->count())
            <div class="row g-3 row-cols-1 row-cols-sm-2 row-cols-lg-3">
                @foreach($atenciones as $a)
                    @php
                        // Estos campos asumen que el controlador ya hizo eager loads (items_count, total)
                        $estado    = $a->estado;
                        $badge     = estadoBadgeClass($estado);
                        $total     = isset($a->monto_total) ? (float) $a->monto_total : null;
                        $itemsCnt  = $a->items_count ?? null;
                    @endphp

                    <div class="col">
                        <article class="ed-card h-100">
                            <div class="p-3 border-bottom bg-light">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="ed-chip {{ $badge }}">{{ strtoupper($estado) }}</span>
                                    </div>
                                    <span class="text-muted small">#{{ $a->id }}</span>
                                </div>
                            </div>

                            <div class="p-3">
                                <div class="ed-kv mb-2">
                                    <small>Creado</small>
                                    <span class="text-end">{{ optional($a->opened_at)->diffForHumans() ?? '—' }}</span>

                                    <small>Actualizado</small>
                                    <span class="text-end">{{ optional($a->updated_at)->diffForHumans() ?? '—' }}</span>

                                    <small>Ítems</small>
                                    <span class="text-end">{{ $itemsCnt !== null ? $itemsCnt : '—' }}</span>
                                    <br>
                                    <small>Total</small>
                                    <span class="text-end fw-semibold">
                                      @if(!is_null($total)) S/ {{ number_format($total,2) }} @else — @endif
                                    </span>
                                </div>

                                @if($a->comentario)
                                    <div class="small text-muted border-start ps-2">
                                        {{ \Illuminate\Support\Str::limit($a->comentario, 120) }}
                                    </div>
                                @endif
                            </div>

                            <div class="p-3 border-top bg-white ed-actions d-flex gap-2">
                                <a href="{{ route('pedido.externo.comanda.show', [$a->id, 1]) }}"
                                   class="btn btn-outline-primary w-100">
                                    Abrir
                                </a>

                                @if(in_array($estado, ['abierta','en_proceso','por_pagar']))
                                    <form class="w-100" method="POST" action="{{ route('pedido.externo.ir_pagar', $a) }}">
                                        @csrf
                                        <button class="btn btn-success w-100">Ir a Pagar</button>
                                    </form>
                                @endif
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>

            <div class="mt-3">
                {{ $atenciones->withQueryString()->links() }}
            </div>
        @else
            <div class="text-center text-muted py-5">
                <i class="bi bi-inboxes" style="font-size:3rem;"></i>
                <p class="mt-3 mb-0">No hay pedidos externos con los filtros actuales.</p>
            </div>
        @endif

    </div>
@endsection