<?php

namespace App\Models;

class Usuarios extends DBAbstractModel
{
    private static $instancia;
    private $id;
    private $nombre;
    private $email;
    private $password;

    // Patron singleton
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
    public function getId()       { return $this->id; }
    public function getNombre()   { return $this->nombre; }
    public function getEmail()    { return $this->email; }
    public function getPassword() { return $this->password; }

    // Setters
    public function setId($id)           { $this->id = $id; }
    public function setNombre($nombre)   { $this->nombre = $nombre; }
    public function setEmail($email)     { $this->email = $email; }
    public function setPassword($password){ $this->password = $password; }

    // Para obtener todos los usuarios
    public function getAll()
    {
        $this->query = "SELECT * FROM usuarios";
        $this->getResultFromQuery();
        return $this->rows;
    }

    // Para obtener un usuario por id o email
    public function get($sh_data = array())
    {
        foreach ($sh_data as $campo => $valor) {
            $$campo = $valor;
        }

        if (isset($id)) {
            $this->query = "SELECT * FROM usuarios WHERE id = :id";
            $this->parametros['id'] = $id;
        } elseif (isset($email)) {
            $this->query = "SELECT * FROM usuarios WHERE email = :email";
            $this->parametros['email'] = $email;
        } else {
            return null;
        }

        $this->getResultFromQuery();

        if (count($this->rows) == 1) {
            foreach ($this->rows[0] as $propiedad => $valor) {
                $this->$propiedad = $valor;
            }
            $this->mensaje = 'Usuario encontrado';
        } else {
            $this->mensaje = 'Usuario no encontrado';
        }
        return $this->rows[0] ?? null;
    }

    // Crear usuario
    public function set($dataCont = array())
    {
        foreach ($dataCont as $campo => $valor) {
            $$campo = $valor;
        }

        $this->query = "INSERT INTO usuarios (nombre, email, password) 
                        VALUES (:nombre, :email, :password)";

        // Asumimos que $password YA viene hasheado
        $this->parametros['nombre']   = $nombre;
        $this->parametros['email']    = $email;
        $this->parametros['password'] = $password;

        $this->getResultFromQuery();
        $this->mensaje = 'Usuario agregado';

        return $this->mensaje;
    }

    // Editar usuario
    public function edit($dataCont = array())
    {
        foreach ($dataCont as $campo => $valor) {
            $$campo = $valor;
        }

        $this->query = "UPDATE usuarios 
                        SET nombre = :nombre, 
                            email = :email, 
                            password = :password 
                        WHERE id = :id";

        $this->parametros['nombre']   = $nombre;
        $this->parametros['email']    = $email;
        // Asumimos que $password YA viene hasheado (o es el mismo si no cambió)
        $this->parametros['password'] = $password;
        $this->parametros['id']       = $id;

        $this->getResultFromQuery();
        $this->mensaje = 'Usuario modificado';
    }

    // Eliminar usuario
    public function delete($dataCont = array())
    {
        foreach ($dataCont as $campo => $valor) {
            $$campo = $valor;
        }
        $this->query = "DELETE FROM usuarios WHERE id = :id";
        $this->parametros['id'] = $id;
        $this->getResultFromQuery();

        $this->mensaje = 'Usuario eliminado';
    }

    // Login
    public function login($email = '')
    {
        if ($email != '') {
            $this->query = "SELECT * FROM usuarios WHERE email = :email";
            $this->parametros['email'] = $email;
            $this->getResultFromQuery();
        }
        if (count($this->rows) == 1) {
            foreach ($this->rows[0] as $propiedad => $valor) {
                $this->$propiedad = $valor;
            }
            $this->mensaje = "Usuario encontrado";
        } else {
            $this->mensaje = 'Usuario no encontrado';
        }
        return $this->rows[0] ?? null;
    }
}
