<?php
namespace App\Models;

class Inscripciones extends DBAbstractModel
{
    private static $instancia;

    // Patrón singleton, no puedo tener dos objetos de la clase Inscripciones
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

    // Para obtener inscripciones por id_usuario o id
    public function get($sh_data = array())
    {
        foreach ($sh_data as $campo => $valor) {
            $$campo = $valor;
        }

        if (isset($id)) {
            $this->query = "SELECT * FROM Inscripciones WHERE id = :id";
            $this->parametros['id'] = $id;
        } elseif (isset($id_usuario)) {
            $this->query = "SELECT * FROM Inscripciones WHERE id_usuario = :id_usuario";
            $this->parametros['id_usuario'] = $id_usuario;
        } else {
            return null;
        }

        // Ejecutamos la consulta
        $this->getResultFromQuery();

        if (count($this->rows) > 0) {
            $this->mensaje = 'Inscripciones encontradas';
        } else {
            $this->mensaje = 'Inscripciones no encontradas';
        }
        return $this->rows ?? null;
    }

    // Para crear una nueva inscripción
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
        $actividad_id = $actividad_id ?? '';
        $fecha_inscripcion = $fecha_inscripcion ?? '';
        $estado = $estado ?? '';

        $this->query = "INSERT INTO Inscripciones (nombre_solicitante, id_usuario, telefono, email, actividad_id, fecha_inscripcion, estado) VALUES (:nombre_solicitante, :id_usuario, :telefono, :email, :actividad_id, :fecha_inscripcion, :estado)";

        $this->parametros['nombre_solicitante'] = $nombre_solicitante;
        $this->parametros['id_usuario'] = $id_usuario;
        $this->parametros['telefono'] = $telefono;
        $this->parametros['email'] = $email;
        $this->parametros['actividad_id'] = $actividad_id;
        $this->parametros['fecha_inscripcion'] = $fecha_inscripcion;
        $this->parametros['estado'] = $estado;

        $this->getResultFromQuery();

        $this->mensaje = 'Inscripción creada';
    }

    // Para eliminar una inscripción por id
    public function delete($id = '')
    {
        $this->query = "DELETE FROM Inscripciones WHERE id = :id";
        $this->parametros['id'] = $id;
        $this->getResultFromQuery();
        $this->mensaje = 'Inscripción eliminada';
    }

    // Para editar una inscripción

    public function edit( $sh_data = array() ){}
}
?>