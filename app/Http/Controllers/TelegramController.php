<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\TelegramNotification;
use Illuminate\Http\Request;

class TelegramController extends Controller
{
    public function sendMessage()
    {
        // Obtén el token y el chat_id desde las variables de entorno
        /*$botToken = env('TELEGRAM_BOT_TOKEN');
        $chatId = env('TELEGRAM_CHAT_ID_PROCESS'); // Asegúrate de que este valor esté definido en el .env

        // Verifica que ambos valores no estén vacíos
        if (empty($botToken) || empty($chatId)) {
            return response()->json(['error' => 'Falta configurar el token o el chat ID en el archivo .env'], 400);
        }

        // Crea el mensaje dinámico basado en el tipo
        $message = "Hay un usuario que hizo una operación con código " .
            " y fecha ";

        // Construye la URL para la API de Telegram
        $apiUrl = "https://api.telegram.org/bot{$botToken}/sendMessage?chat_id={$chatId}&text=" . urlencode($message);

        // Llama a la API
        $response = file_get_contents($apiUrl);

        // Devuelve la respuesta de Telegram
        return $response;*/

        $user = User::find(1);
        $type = 'process';
        $mensaje2 = "El usuario con nombre XXXXXXX con fecha de registro 01 Jul 2023, 6:21 pm necesita que valides sus documentos.";

        $user->notify(new TelegramNotification($type, $mensaje2));

        return 'Notificación enviada a Telegram.';
    }

    public function sendNotification( $type, array $data)
    {
        if ($type == 'process') {
            // Encuentra al usuario que recibirá la notificación (puede ser otro usuario)
            $user = User::find(2);

            // Construye el mensaje dinámicamente
            $message = "Se ha generado un nuevo pedido ".$data['order']." para el cliente " . $data['nameUser']." - ". $data['nameUserReal']." - ". $data['phoneUser'] .
                " el " . $data['dateOperation'] . ".";

            // Envía la notificación
            $user->notify(new TelegramNotification($type, $message));
        } elseif ($type == 'cash_register_warning') {
            $user = User::find(2); // o el ID del admin
            $message = $data['mensaje'];
            $user->notify(new TelegramNotification($type, $message));
        } else {
            throw new \Exception("Tipo de notificación no soportado: {$type}");
        }
    }
}
