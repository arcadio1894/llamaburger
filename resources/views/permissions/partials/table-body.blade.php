@forelse($items as $i => $p)
    @include('permissions.partials.table-row', ['p' => $p])
@empty
    <tr><td colspan="4" class="text-center text-muted">Sin resultados</td></tr>
@endforelse