<?php

namespace App\Controllers;

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
use App\Models\Usuarios;

class AuthController
{
    private $requestMethod;
    private $users;

    public function __construct($requestMethod)
    {
        $this->requestMethod = $requestMethod;
        $this->users = Usuarios::getInstancia();
    }

    public function processRequest()
    {
        switch ($this->requestMethod) {
            case 'POST':
                $response = $this->loginFromRequest();
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

    public function loginFromRequest()
    {
        // Leemos flujo de entrada
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);

        // Determinamos si el JSON es válido
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->unprocessableEntityResponse("El JSON recibido no es válido.");
        }

        if (!isset($input['email']) || !isset($input['password'])) {
            return $this->unprocessableEntityResponse("El email y la contraseña son obligatorios.");
        }

        if (count($input) > 2) {
            return $this->unprocessableEntityResponse("Solo puede escribir el correo y la contraseña.");
        }

        $email = $input['email'];
        $password = $input['password'];
        $dataUser = $this->users->login($email);

        // Verificación de la contraseña
        if ($dataUser && password_verify($password, $dataUser['password'])) {
            $id = $dataUser['id'];
            $key = KEY; // Clave de encriptación
            $issuer_claim = "http://apicentros.local"; // Emisor del token
            $audience_claim = "http://apicentros.local"; // Destinatario del token
            $issuedat_claim = time(); // Tiempo en que fue emitido el token
            $notbofore_claim = time(); // Tiempo antes del cual no es válido el token
            $expire_claim = $issuedat_claim + 3600; // Tiempo de expiración del token

            $token = array(
                "iss" => $issuer_claim,
                "aud" => $audience_claim,
                "iat" => $issuedat_claim,
                "nbf" => $notbofore_claim,
                "exp" => $expire_claim,
                "data" => array(
                    "usuario" => $email,
                    "id" => $id
                )
            );

            $jwt = JWT::encode($token, $key, 'HS256');
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode(
                array(
                    "message" => "Inicio de sesión exitoso.",
                    "jwt" => $jwt,
                    "usuario" => $email,
                    "expireAt" => $expire_claim
                )
            );
        } else {
            $response['status_code_header'] = 'HTTP/1.1 401 Unauthorized';
            $response['body'] = json_encode(["message" => "Usuario o contraseña incorrectos."]);
        }

        return $response;
    }

    private function unprocessableEntityResponse($message)
    {
        return [
            'status_code_header' => 'HTTP/1.1 422 Unprocessable Entity',
            'body' => json_encode(['message' => $message], JSON_UNESCAPED_UNICODE)
        ];
    }

    private function notFoundResponse()
    {
        return [
            'status_code_header' => 'HTTP/1.1 404 Not Found',
            'body' => json_encode(['message' => 'Recurso no encontrado'], JSON_UNESCAPED_UNICODE)
        ];
    }
}
