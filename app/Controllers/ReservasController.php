<?php

namespace App\Controllers;

use App\Models\Reservas;
use \Exception;
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

class ReservasController
{
    private $requestMethod;
    private $reservas;
    private $usuariosId;

    public function __construct($requestMethod, $usuariosId = null)
    {
        $this->requestMethod = $requestMethod;
        $this->usuariosId = $usuariosId;
        $this->reservas = Reservas::getInstancia();
    }

    public function processRequest()
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = explode('/', $uri);

        switch ($this->requestMethod) {
            case 'GET':
                $response = $this->getReservas();
                break;
            case 'POST':
                $response = $this->createReserva();
                break;
            case 'DELETE':
                if (isset($uri[3])) {
                    $response = $this->deleteReserva($uri[3]);
                } else {
                    $response = $this->notFoundResponse();
                }
                break;
            default:
                $response = $this->notFoundResponse();
                break;
        }

        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }

    private function getReservas()
    {
        // Obtener el ID del usuario desde el token JWT
        $usuariosId = $this->getUserIdFromToken();
    
        if (!$usuariosId) {
            return [
                'status_code_header' => 'HTTP/1.1 401 Unauthorized',
                'body' => json_encode(['message' => 'No tienes permiso para acceder a esta información'])
            ];
        }
    
        $result = $this->reservas->get(['id_usuario' => $usuariosId]);
    
        if (!$result) {
            return [
                'status_code_header' => 'HTTP/1.1 404 Not Found',
                'body' => json_encode(['message' => 'No tienes reservas registradas'])
            ];
        }
    
        return [
            'status_code_header' => 'HTTP/1.1 200 OK',
            'body' => json_encode($result)
        ];
    }
    
    public function getUserIdFromToken() {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
        $jwt = null;

        if ($authHeader) {
            $arr = explode(" ", $authHeader);
            if (isset($arr[1])) {
                $jwt = $arr[1];
            }
        }

        if ($jwt) {
            try {
                $decoded = JWT::decode($jwt, new Key(KEY, 'HS256'));
                return $decoded->data->id;
            } catch (Exception $e) {
                return null;
            }
        }
        return null;
    }

    private function createReserva()
    {
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);

        if (!$this->validateReserva($input)) {
            return $this->unprocessableEntityResponse();
        }

        $this->reservas->set($input);

        $response['status_code_header'] = 'HTTP/1.1 201 Created';
        $response['body'] = json_encode(['message' => 'Reserva creada']);
        return $response;
    }

    private function deleteReserva($id)
    {
        $result = $this->reservas->get(['id' => $id]);

        if (!$result) {
            return [
                'status_code_header' => 'HTTP/1.1 404 Not Found',
                'body' => json_encode(['message' => 'La reserva con ese id no existe'])
            ];
        }

        $this->reservas->delete($id);

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode(['message' => 'Reserva eliminada']);
        return $response;
    }

    private function validateReserva($input)
    {
        if (!isset($input['nombre_solicitante']) || !isset($input['id_usuario']) || !isset($input['telefono']) || !isset($input['email']) || !isset($input['id_instalacion']) || !isset($input['fecha_hora_inicio']) || !isset($input['fecha_hora_final']) || !isset($input['estado'])) {
            return false;
        }
        return true;
    }

    private function unprocessableEntityResponse()
    {
        return [
            'status_code_header' => 'HTTP/1.1 422 Unprocessable Entity',
            'body' => json_encode(['message' => 'Datos inválidos'])
        ];
    }

    private function notFoundResponse()
    {
        return [
            'status_code_header' => 'HTTP/1.1 404 Not Found',
            'body' => json_encode(['message' => 'Recurso no encontrado'])
        ];
    }
}
