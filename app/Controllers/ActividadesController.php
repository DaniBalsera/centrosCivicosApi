<?php

namespace App\Controllers;

use App\Models\Actividades;

class ActividadesController
{
    private $requestMethod;
    private $actividades;
    private $usuariosId;

    public function __construct($requestMethod, $usuariosId)
    {
        $this->requestMethod = $requestMethod;
        $this->usuariosId = $usuariosId;
        $this->actividades = Actividades::getInstancia();
    }

    public function processRequest()
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = explode('/', $uri);

        switch ($this->requestMethod) {
            case 'GET':
                if (isset($uri[3])) {
                    $response = $this->getActividad($uri[3]);
                } else {
                    $response = $this->getActividadFilter();
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

    private function getActividadFilter()
    {
        $input = (array) json_decode(file_get_contents('php://input'), true);

        $result = $this->actividades->getByFilter($input);

        if (!$result) {
            return $this->notFoundResponse();
        }

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function getActividad($id = '')
    {
        $result = $this->actividades->get(['id' => $id]);

        if (!$result) {
            return $this->notFoundResponse();
        }

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function notFoundResponse()
    {
        return [
            'status_code_header' => 'HTTP/1.1 404 Not Found',
            'body' => json_encode(['message' => 'Actividad no encontrada'], JSON_UNESCAPED_UNICODE)
        ];
    }
}
?>