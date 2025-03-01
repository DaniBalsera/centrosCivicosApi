<?php

namespace App\Controllers;

use App\Models\Usuarios;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class UserController
{
    private $requestMethod;
    private $usuarios;
    private $usuariosId;

    public function __construct($requestMethod, $usuariosId = null)
    {
        $this->requestMethod = $requestMethod;
        $this->usuariosId = $usuariosId;
        $this->usuarios = Usuarios::getInstancia();
    }

    public function processRequest()
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = explode('/', $uri);

        switch ($this->requestMethod) {
            case 'GET':
                $response = $this->getUsuario();
                break;
            case 'POST':
                if (isset($uri[3]) && $uri[3] === 'refresh') {
                    $response = $this->refreshToken();
                } else {
                    $response = $this->register();
                }
                break;
            case 'PUT':
                $response = $this->updateUsuario();
                break;
            case 'DELETE':
                $response = $this->deleteUsuario();
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

    private function getUsuario()
    {
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);

        if (empty($input)) {
            $result = $this->usuarios->getAll();
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($result);
            return $response;
        } else {
            if (!$this->usuarios->get($input)) {
                return $this->notFoundResponse();
            }

            $result = $this->usuarios->get($input);
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($result);
            return $response;
        }
    }

    private function register()
    {
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);

        // Verificar si los datos necesarios están presentes
        if (!$this->validateUsuario($input)) {
            $response['status_code_header'] = 'HTTP/1.1 400 Bad Request';
            $response['body'] = json_encode(['message' => 'Datos de usuario inválidos'], JSON_UNESCAPED_UNICODE);
            return $response;
        }

        // Verificar si ya existe un usuario con ese email
        $existingUser = $this->usuarios->get(['email' => $input['email']]);
        if ($existingUser) {
            $response['status_code_header'] = 'HTTP/1.1 409 Conflict';
            $response['body'] = json_encode(['message' => 'Este usuario ya existe'], JSON_UNESCAPED_UNICODE);
            return $response;
        } else {
            // Hasheamos la contraseña aquí (SOLO UNA VEZ)
            $input['password'] = password_hash($input['password'], PASSWORD_BCRYPT);

            // Guardamos el usuario
            $this->usuarios->set($input);

            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode(['message' => 'Usuario creado con éxito'], JSON_UNESCAPED_UNICODE);
            return $response;
        }
    }

    private function updateUsuario()
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);

        $jwt = explode(" ", $authHeader)[1] ?? null;

        try {
            $decoded = JWT::decode($jwt, new Key(KEY, 'HS256'));
            $idUser = $decoded->data->id ?? null;
        } catch (Exception $e) {
            return $this->unauthorizedResponse("Acceso denegado: " . $e->getMessage());
        }

        // Verificamos si hay conflicto con otro usuario que tenga el mismo email
        $existingUser = $this->usuarios->get(['email' => $input['email']]);
        if ($existingUser && $existingUser['id'] != $idUser) {
            $response['status_code_header'] = 'HTTP/1.1 409 Conflict';
            $response['body'] = json_encode(['message' => 'Este usuario ya existe'], JSON_UNESCAPED_UNICODE);
            return $response;
        }

        // Obtenemos el usuario actual de la BD
        $usuarioRegistrado = $this->usuarios->get(['id' => $idUser]);

        // Si no se envía password, conservamos la que ya tiene (no hasheamos nada nuevo)
        if (!isset($input['password'])) {
            $input['password'] = $usuarioRegistrado['password'];
        } else {
            // Si hay una nueva contraseña, la hasheamos antes de guardar
            $input['password'] = password_hash($input['password'], PASSWORD_BCRYPT);
        }

        // Validación mínima
        if (!$this->validateUsuario($input)) {
            return $this->notFoundResponse();
        }

        // Añadimos el id al array para poder hacer el update
        $input['id'] = $idUser;
        $this->usuarios->edit($input);

        return $this->successResponse("Usuario actualizado con éxito");
    }

    private function deleteUsuario()
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);

        $jwt = explode(" ", $authHeader)[1] ?? null;

        try {
            $decoded = JWT::decode($jwt, new Key(KEY, 'HS256'));
            $idUser = $decoded->data->id ?? null;
        } catch (Exception $e) {
            return $this->unauthorizedResponse("Acceso denegado: " . $e->getMessage());
        }

        $this->usuarios->delete(['id' => $idUser]);

        return $this->successResponse("Usuario eliminado con éxito");
    }

    private function refreshToken()
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;

        if (!$authHeader) {
            return $this->unauthorizedResponse("Token no proporcionado");
        }

        // Extraer el token JWT del encabezado de autorización
        $jwt = explode(" ", $authHeader)[1] ?? null;
        if (!$jwt) {
            return $this->unauthorizedResponse("Token inválido");
        }

        try {
            $decoded = JWT::decode($jwt, new Key(KEY, 'HS256'));
        } catch (Exception $e) {
            return $this->unauthorizedResponse("Acceso denegado: " . $e->getMessage());
        }

        // Generar un nuevo token
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
            "data" => $decoded->data
        );

        $jwt = JWT::encode($token, $key, 'HS256');
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode(
            array(
                "message" => "Token renovado con éxito.",
                "jwt" => $jwt,
                "expireAt" => $expire_claim
            )
        );

        return $response;
    }

    private function validateUsuario($input)
    {
        // Mínimo que tenga 'nombre' y 'email'
        if (!isset($input['nombre']) || !isset($input['email'])) {
            return false;
        }
        return true;
    }

    private function unauthorizedResponse($message)
    {
        return [
            'status_code_header' => 'HTTP/1.1 401 Unauthorized',
            'body' => json_encode(['message' => $message], JSON_UNESCAPED_UNICODE)
        ];
    }

    private function notFoundResponse()
    {
        return [
            'status_code_header' => 'HTTP/1.1 404 Not Found',
            'body' => json_encode(['message' => 'Usuario no encontrado'], JSON_UNESCAPED_UNICODE)
        ];
    }

    private function successResponse($message)
    {
        return [
            'status_code_header' => 'HTTP/1.1 200 OK',
            'body' => json_encode(['message' => $message], JSON_UNESCAPED_UNICODE)
        ];
    }
}
