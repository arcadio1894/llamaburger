@if($mesas->isEmpty())
    <div class="alert alert-light mb-0 mesas-empty">No hay mesas definidas para esta sala.</div>
@else
    <div class="row mesas-grid">
        @foreach($mesas as $mesa)
            <div class="col-6 col-md-3 col-mesa">
                @include('sala.partials.mesa-item', ['mesa' => $mesa])
            </div>
        @endforeach
    </div>
@endif