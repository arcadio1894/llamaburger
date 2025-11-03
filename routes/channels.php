<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Distributor;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('orders', function ($user) {
    return true; // Permitir acceso al canal para todos los usuarios
});

Broadcast::channel('active-users', function ($user) {
    return true; // Permitir acceso a todos los usuarios
});

Broadcast::channel('orders.distributor.{distributorId}', function ($user, $distributorId) {
    $dist = Distributor::where('user_id', $user->id)->first();
    return $dist && (int)$dist->id === (int)$distributorId;
});

Broadcast::channel('orders.admin', function ($user) {
    return $user && $user->hasRole('administrador');
});
