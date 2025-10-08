@forelse($items as $r)
    @include('roles.partials.table-row', ['r'=>$r])
@empty
    <tr><td colspan="3" class="text-center text-muted">Sin resultados</td></tr>
@endforelse