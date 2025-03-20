<?php

namespace App\Traits;

// Trait -> Reutilizar código en diferentes clases
// Cambiar el nombre de la clase a ApiResponse
trait ApiResponse
{
    /**
     * Formato de respuesta
     * Format Response
     * @param bool $success
     * @param string $message
     * @param int|null $status
     * @param Object|null $data
     * @return array{success: bool, message: string, data: Object|null}
     */
    public function response(bool $success, string $message, $status = null, $data = null)
    {
        if ($status == null) {
            return [
                'success' => $success,
                'message' => $message,
                'data' => $data
            ];
        }
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ], $status);
    }
}