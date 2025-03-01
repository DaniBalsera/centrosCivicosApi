<?php
namespace App\Models;

class Instalaciones extends DBAbstractModel
{
    private static $instancia;
    private $id;
    private $nombre;
    private $descripcion;
    private $capacidad_maxima;
    private $centro_id;

    // Patron singleton, no puedo tener dos objetos de la clase Instalaciones
    public static function getInstancia()
    {
        if (!isset(self::$instancia)) {
            $miClase = __CLASS__;
            self::$instancia = new $miClase;
        }
        return self::$instancia;
    }

    public function __clone()
    {
        trigger_error('La clonación no es permitida!.', E_USER_ERROR);
    }

    // Getters
    public function getId()
    {
        return $this->id;
    }

    public function getNombre()
    {
        return $this->nombre;
    }

    public function getDescripcion()
    {
        return $this->descripcion;
    }

    public function getCapacidadMaxima()
    {
        return $this->capacidad_maxima;
    }

    public function getCentroId()
    {
        return $this->centro_id;
    }

    // Setters
    public function setId($id)
    {
        $this->id = $id;
    }

    public function setNombre($nombre)
    {
        $this->nombre = $nombre;
    }

    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;
    }

    public function setCapacidadMaxima($capacidad_maxima)
    {
        $this->capacidad_maxima = $capacidad_maxima;
    }

    public function setCentroId($centro_id)
    {
        $this->centro_id = $centro_id;
    }


    public function get($sh_data = array())
    {
        foreach ($sh_data as $campo => $valor) {
            $$campo = $valor;
        }

        if (isset($id)) {
            $this->query = "SELECT * FROM Instalaciones WHERE centro_id = :centro_id";
            $this->parametros['centro_id'] = $id;
        }

        $this->getResultFromQuery();

        if (count($this->rows) == 1) {
            foreach ($this->rows[0] as $propiedad => $valor) {
                $this->$propiedad = $valor;
            }
            $this->mensaje = 'Instalaciones encontrado';
        } else {
            $this->mensaje = 'Instalaciones no encontrado';
        }
        return $this->rows ?? null;
    }

    


    public function getByFilter($sh_data = array())
    {
        if (!is_array($sh_data)) {
            return null;
        }

        $this->query = "SELECT nombre, descripcion, capacidad_maxima FROM Instalaciones";
        $this->parametros = [];
        $conditions = [];

        if (!empty($sh_data['nombre'])) {
            $conditions[] = "nombre LIKE :nombre";
            $this->parametros['nombre'] = '%' . $sh_data['nombre'] . '%';
        }

        if (!empty($sh_data['descripcion'])) {
            $conditions[] = "descripcion LIKE :descripcion";
            $this->parametros['descripcion'] = '%' . $sh_data['descripcion'] . '%';
        }

        if (!empty($sh_data['capacidad_maxima'])) {
            $conditions[] = "capacidad_maxima = :capacidad_maxima";
            $this->parametros['capacidad_maxima'] = $sh_data['capacidad_maxima'];
        }

        if (!empty($conditions)) {
            $this->query .= " WHERE " . implode(" AND ", $conditions);
        }

        $this->getResultFromQuery();

        if (count($this->rows) > 0) {
            $this->mensaje = 'Instalaciones encontradas';
        } else {
            $this->mensaje = 'Instalaciones no encontradas';
        }
        return $this->rows ?? null;
    }

    public function set(){}
    public function edit(){}
    public function delete(){}
}
?>