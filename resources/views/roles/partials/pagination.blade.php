@php
    $p=$paginator;$cur=$p->currentPage();$last=$p->lastPage();
@endphp
@if($last>1)
    <nav>
        <ul class="pagination pagination-sm mb-0">
            <li class="page-item {{ $cur==1?'disabled':'' }}">
                <a class="page-link role-page-link" href="#" data-page="{{ $cur-1 }}">&laquo;</a>
            </li>
            @for($i=max(1,$cur-2); $i<=min($last,$cur+2); $i++)
                <li class="page-item {{ $i==$cur?'active':'' }}">
                    <a class="page-link role-page-link" href="#" data-page="{{ $i }}">{{ $i }}</a>
                </li>
            @endfor
            <li class="page-item {{ $cur==$last?'disabled':'' }}">
                <a class="page-link role-page-link" href="#" data-page="{{ $cur+1 }}">&raquo;</a>
            </li>
        </ul>
    </nav>
@endif