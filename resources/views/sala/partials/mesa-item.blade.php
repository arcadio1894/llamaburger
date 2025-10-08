@php
    $estado = $mesa->estado ?? 'cerrada';
    $map = [
      'libre'   => ['class' => 'badge-success', 'text' => 'Libre'],
      'ocupada' => ['class' => 'badge-warning', 'text' => 'Ocupada'],
    ];
    $b = $map[$estado] ?? ['class' => 'badge-secondary', 'text' => ucfirst($estado)];
@endphp

<a class="mesa-card" data-id="{{ $mesa->id }}">
    <div class="left">
        <div class="icon"><i class="fas fa-utensils"></i></div>
        <div class="name">{{ $mesa->nombre ?? ('Mesa '.$mesa->id) }}</div>
    </div>
    <div class="right">
        <span class="badge {{ $b['class'] }}">{{ $b['text'] }}</span>
    </div>
</a>