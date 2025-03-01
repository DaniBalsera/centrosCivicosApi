<?php

namespace App\Models;

class Reservas extends DBAbstractModel
{
    private static $instancia;

    // Patrón singleton, no puedo tener dos objetos de la clase Reservas
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

    // Para obtener reservas por id_usuario
    public function get($params = [])
    {
        if (!empty($params)) {
            $this->query = "SELECT * FROM reservas WHERE " . key($params) . " = :" . key($params);
            $this->parametros = $params;
            $this->getResultFromQuery();
        }

        if (count($this->rows) > 0) {
            $this->mensaje = 'Reserva encontrada';
        } else {
            $this->mensaje = 'Reserva no encontrada';
        }
        return $this->rows;
    }

    // Para crear una nueva reserva
    public function set($sh_data = array())
    {
        if (!is_array($sh_data)) {
            return null;
        }

        foreach ($sh_data as $campo => $valor) {
            $$campo = $valor;
        }

        $nombre_solicitante = $nombre_solicitante ?? '';
        $id_usuario = $id_usuario ?? '';
        $telefono = $telefono ?? '';
        $email = $email ?? '';
        $id_instalacion = $id_instalacion ?? '';
        $fecha_hora_inicio = $fecha_hora_inicio ?? '';
        $fecha_hora_final = $fecha_hora_final ?? '';
        $estado = $estado ?? '';

        $this->query = "INSERT INTO Reservas (nombre_solicitante, id_usuario, telefono, email, id_instalacion, fecha_hora_inicio, fecha_hora_final, estado) VALUES (:nombre_solicitante, :id_usuario, :telefono, :email, :id_instalacion, :fecha_hora_inicio, :fecha_hora_final, :estado)";

        $this->parametros['nombre_solicitante'] = $nombre_solicitante;
        $this->parametros['id_usuario'] = $id_usuario;
        $this->parametros['telefono'] = $telefono;
        $this->parametros['email'] = $email;
        $this->parametros['id_instalacion'] = $id_instalacion;
        $this->parametros['fecha_hora_inicio'] = $fecha_hora_inicio;
        $this->parametros['fecha_hora_final'] = $fecha_hora_final;
        $this->parametros['estado'] = $estado;

        $this->getResultFromQuery();

        $this->mensaje = 'Reserva creada';
    }

    // Para eliminar una reserva por id
    public function delete($id = '')
    {
        $this->query = "DELETE FROM Reservas WHERE id = :id";
        $this->parametros['id'] = $id;
        $this->getResultFromQuery();
        $this->mensaje = 'Reserva eliminada';
    }

    // Para editar una reserva
    public function edit($sh_data = array()) {}
}
