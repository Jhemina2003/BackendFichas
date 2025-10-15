<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Http;

class SocketHelper
{
    /**
     * Notifica al servidor de sockets el cambio de ficha actual de una ventanilla
     * @param int $ventanillaId
     * @param array|null $ficha
     */
    public static function emitirFichaActual($ventanillaId, $ficha)
    {
        try {
            $url = 'http://localhost:3000/emit-ficha'; // Endpoint en el servidor Node.js
            $payload = [
                'ventanillaId' => $ventanillaId,
                'ficha' => $ficha,
            ];
            Http::post($url, $payload);
        } catch (\Exception $e) {
            \Log::error('Error notificando ficha actual por socket: ' . $e->getMessage());
        }
    }
}
