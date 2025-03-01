<?php

namespace App\Controllers;

use App\Models\Instalaciones;

class InstalacionesController
{
    private $requestMethod;
    private $instalaciones;
    private $usuariosId;

    public function __construct($requestMethod, $usuariosId)
    {
        $this->requestMethod = $requestMethod;
        $this->usuariosId = $usuariosId;
        $this->instalaciones = Instalaciones::getInstancia();
    }

    public function processRequest()
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = explode('/', $uri);

        switch ($this->requestMethod) {
            case 'GET':
                if (isset($uri[3])) {
                    $response = $this->getInstalacion($uri[3]);
                } else {
                    $response = $this->getInstalacionFilter();
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

    private function getInstalacionFilter()
    {
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);

        $result = $this->instalaciones->getByFilter($input);

        if (!$result) {
            return $this->notFoundResponse();
        }

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function getInstalacion($id = '')
    {
        $result = $this->instalaciones->get(['id' => $id]);

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
            'body' => json_encode(['message' => 'Instalación no encontrada'], JSON_UNESCAPED_UNICODE)
        ];
    }
}
?>
