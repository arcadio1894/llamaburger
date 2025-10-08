@if($mesas->isEmpty())
    <div class="text-muted mesas-empty">Aún no hay mesas en esta sala.</div>
@else
    @foreach($mesas as $mesa)
        @include('sala.partials.mesa-chip', ['mesa' => $mesa])
    @endforeach
@endif