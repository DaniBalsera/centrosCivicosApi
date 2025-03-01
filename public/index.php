<?php

require "../bootstrap.php";
require_once "../vendor/autoload.php";

use App\Core\Router;
use App\Controllers\UserController;
use App\Controllers\AuthController;
use App\Controllers\CentrosController;
use App\Controllers\InstalacionesController;
use App\Controllers\ActividadesController;
use App\Controllers\ReservasController;
use App\Controllers\InscripcionesController;


use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");

$requestMethod = $_SERVER['REQUEST_METHOD'];
$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $request);

$userId = null;
if (isset($uri[3])) {
    $userId = (int) $uri[3];
}

function sesion()
{
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
            JWT::decode($jwt, new Key(KEY, 'HS256'));
            return true;
        } catch (Exception $e) {
            echo json_encode(array(
                'message' => 'Acceso denegado',
                'error' => $e->getMessage()
            ));
            return false;
            exit(http_response_code(401));
        }
    }
    return false;
}

$router = new Router();


// Rutas para el controlador de usuarios

$router->add(array(
    'name' => 'login',
    'path' => '/^\/api\/login$/',
    'action' => AuthController::class,
    'perfil' => ['registros']
));

$router->add(array(
    'name' => 'register',
    'path' => '/^\/api\/register$/',
    'action' => UserController::class,
    'perfil' => ['registros']
));

$router->add(array(
    'name' => 'usuarios',
    'path' => '/^\/api\/user$/',
    'action' => UserController::class,
    'perfil' => ['usuario']
));

$router->add(array(
    'name' => 'token',
    'path' => '/^\/api\/token\/refresh$/',
    'action' => UserController::class,
    'perfil' => ['usuario']
));

// Rutas para el controlador de centros



$router->add(array(
    'name'=>'centros',
    'path'=>'/^\/api\/centros$/',
    'action'=> CentrosController::class,
    'perfil'=>  ['publico']
));

$router->add(array(
    'name'=>'centro',
    'path'=>'/^\/api\/centros\/[0-9]+$/',
    'action'=> CentrosController::class,
    'perfil'=>  ['publico']
));

// Rutas para el controlador de instalaciones

$router->add(array(
    'name'=>'instalaciones',
    'path'=>'/^\/api\/instalaciones$/',
    'action'=> InstalacionesController::class,
    'perfil'=>  ['publico']
));

$router->add(array(
    'name'=>'instalacion',
    'path'=>'/^\/api\/centros\/[0-9]+\/instalaciones$/',
    'action'=> InstalacionesController::class,
    'perfil'=>  ['publico']
));


// Rutas para el controlador de actividades

$router->add(array(
    'name'=>'actividades',
    'path'=>'/^\/api\/actividades$/',
    'action'=> ActividadesController::class,
    'perfil'=>  ['publico']
));

$router->add(array(
    'name'=>'actividad',
    'path'=>'/^\/api\/centros\/[0-9]+\/actividades$/',
    'action'=> ActividadesController::class,
    'perfil'=>  ['publico']
));



// Rutas para el controlador de reservas
$router->add(array(
    'name' => 'reservas',
    'path' => '/^\/api\/reservas$/',
    'action' => ReservasController::class,
    'perfil' => ['usuario']
));
$router->add(array(
    'name' => 'reservas_delete',
    'path' => '/^\/api\/reservas\/(\d+)$/',
    'action' => ReservasController::class,
    'perfil' => ['usuario']
));


// Rutas para el controlador de inscripciones

$router->add(array(
    'name' => 'inscripciones',
    'path' => '/^\/api\/inscripciones$/',
    'action' => InscripcionesController::class,
    'perfil' => ['usuario']
));

$router->add(array(
    'name' => 'inscripciones_delete',
    'path' => '/^\/api\/inscripciones\/(\d+)$/',
    'action' => InscripcionesController::class,
    'perfil' => ['usuario']
));

$route = $router->match($request);
if ($route) {
    if ($route['perfil'][0] == 'usuario' && !sesion()) {
        header('HTTP/1.1 401 Unauthorized');
        $response['body'] = json_encode(array('message' => 'Acceso no autorizado'));
        echo json_encode($response['body']);
        exit();
    }

    if ($route['perfil'][0] == 'registros' && sesion()) {
        header('HTTP/1.1 401 Unauthorized');
        $response['body'] = json_encode(array('message' => 'Acceso no permitido'));
        echo json_encode($response['body']);
        exit();
    }

    $controllerName = $route['action'];
    $controller = new $controllerName($requestMethod, $userId);
    $controller->processRequest();
} else {
    $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
    $response['body'] = null;
    echo json_encode($response);
}