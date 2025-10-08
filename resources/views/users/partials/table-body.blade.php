@forelse($items as $u)
    @include('users.partials.table-row', ['u'=>$u])
@empty
    <tr><td colspan="5" class="text-center text-muted">Sin usuarios</td></tr>
@endforelse