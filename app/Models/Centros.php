<?php
namespace App\Models;

class Centros extends DBAbstractModel
{
    private static $instancia;
    private $id;
    private $nombre;
    private $direccion;
    private $telefono;
    private $email;
    protected $mensaje;

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

    public function getAll()
    {
        $this->query = "SELECT * FROM centros_civicos";
        $this->getResultFromQuery();
        return $this->rows;
    }

    public function get($sh_data = array())
    {
        foreach ($sh_data as $campo => $valor) {
            $this->$campo = $valor;
        }

        if (isset($this->id)) {
            $this->query = "SELECT * FROM centros_civicos WHERE id = :id";
            $this->parametros['id'] = $this->id;
        }

        $this->getResultFromQuery();

        if (count($this->rows) == 1) {
            foreach ($this->rows[0] as $propiedad => $valor) {
                $this->$propiedad = $valor;
            }
            $this->mensaje = 'Centro encontrado';
        } else {
            $this->mensaje = 'Centro no encontrado';
        }
        return $this->rows[0] ?? null;
    }

    public function set() {}
    public function edit() {}
    public function delete() {}

    // Getters
    public function getId()
    {
        return $this->id;
    }

    public function getNombre()
    {
        return $this->nombre;
    }

    public function getDireccion()
    {
        return $this->direccion;
    }

    public function getTelefono()
    {
        return $this->telefono;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getMensaje()
    {
        return $this->mensaje;
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

    public function setDireccion($direccion)
    {
        $this->direccion = $direccion;
    }

    public function setTelefono($telefono)
    {
        $this->telefono = $telefono;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function setMensaje($mensaje)
    {
        $this->mensaje = $mensaje;
    }
}
?>