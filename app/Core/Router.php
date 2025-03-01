<?php
namespace App\Core;

class Router
{
    private $routers = array();
    public function add($route){
        $this->routers[] = $route;
    }

    public function match (string $request) {
         $matches = array();
         foreach ($this->routers as $route) {
            $patron=$route['path'];
            if (preg_match($patron, $request)) {
                $matches = $route;
            }
        }
        return $matches; 
    }
}

?>
