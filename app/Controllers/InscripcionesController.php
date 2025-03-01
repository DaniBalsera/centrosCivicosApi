<?php

namespace App\Controllers;

use App\Models\Inscripciones;

class InscripcionesController
{
    private $requestMethod;
    private $inscripciones;
    private $usuariosId;

    public function __construct($requestMethod, $usuariosId)
    {
        $this->requestMethod = $requestMethod;
        $this->usuariosId = $usuariosId;
        $this->inscripciones = Inscripciones::getInstancia();
    }

    public function processRequest()
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = explode('/', $uri);

        switch ($this->requestMethod) {
            case 'POST':
                $response = $this->createInscripcion();
                break;
            case 'DELETE':
                if (isset($uri[3])) {
                    $response = $this->deleteInscripcion($uri[3]);
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

    private function createInscripcion()
    {
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);

        if (!$this->validateInscripcion($input)) {
            return $this->unprocessableEntityResponse();
        }

        $this->inscripciones->set($input);

        $response['status_code_header'] = 'HTTP/1.1 201 Created';
        $response['body'] = json_encode(['message' => 'Inscripción creada']);
        return $response;
    }

    private function deleteInscripcion($id)
    {
        $result = $this->inscripciones->get(['id' => $id]);

        if (!$result) {
            return [
                'status_code_header' => 'HTTP/1.1 404 Not Found',
                'body' => json_encode(['message' => 'La inscripción con ese id no existe'])
            ];
        }

        $this->inscripciones->delete($id);

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode(['message' => 'Inscripción eliminada']);
        return $response;
    }

    private function validateInscripcion($input)
    {
        if (!isset($input['nombre_solicitante']) || !isset($input['id_usuario']) || !isset($input['telefono']) || !isset($input['email']) || !isset($input['actividad_id']) || !isset($input['fecha_inscripcion']) || !isset($input['estado'])) {
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
?>