<?php
namespace App\Models;

class Actividades extends DBAbstractModel
{
    private static $instancia;
    private $centro_id;
    private $nombre;
    private $descripcion;
    private $fecha_inicio;
    private $fecha_fin;
    private $horario;
    private $plazas;

    // Patron singleton, no puedo tener dos objetos de la clase Actividades
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
    public function getCentroId()
    {
        return $this->centro_id;
    }
    public function getNombre()
    {
        return $this->nombre;
    }

    public function getDescripcion()
    {
        return $this->descripcion;
    }

    public function getFechaInicio()
    {
        return $this->fecha_inicio;
    }

    public function getFechaFin()
    {
        return $this->fecha_fin;
    }

    public function getHorario()
    {
        return $this->horario;
    }

    public function getPlazas()
    {
        return $this->plazas;
    }

    // Setters
    public function setCentroId($centro_id)
    {
        $this->centro_id = $centro_id;
    }

    public function setNombre($nombre)
    {
        $this->nombre = $nombre;
    }

    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;
    }

    public function setFechaInicio($fecha_inicio)
    {
        $this->fecha_inicio = $fecha_inicio;
    }

    public function setFechaFin($fecha_fin)
    {
        $this->fecha_fin = $fecha_fin;
    }

    public function setHorario($horario)
    {
        $this->horario = $horario;
    }

    public function setPlazas($plazas)
    {
        $this->plazas = $plazas;
    }

// Para obtener actividades por id_centro
public function get($sh_data = array())
{
    foreach ($sh_data as $campo => $valor) {
        $$campo = $valor;
    }

    if (isset($id)) {
        $this->query = "SELECT * FROM Actividades WHERE centro_id = :centro_id";
        $this->parametros['centro_id'] = $id;
    }

    // Ejecutamos la consulta
    $this->getResultFromQuery();

    if (count($this->rows) > 0) {
        $this->mensaje = 'Actividades encontradas';
    } else {
        $this->mensaje = 'Actividades no encontradas';
    }
    return $this->rows ?? null;
}

public function getAll()
{
    $this->query = "SELECT * FROM Actividades";
    $this->getResultFromQuery();
    return $this->rows;
}

public function getByFilter($sh_data = array())
{
    if (!is_array($sh_data)) {
        return null;
    }

    $this->query = "SELECT nombre, descripcion, fecha_inicio, fecha_fin, horario, plazas FROM Actividades";
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

    if (!empty($sh_data['fecha_inicio'])) {
        $conditions[] = "fecha_inicio = :fecha_inicio";
        $this->parametros['fecha_inicio'] = $sh_data['fecha_inicio'];
    }

    if (!empty($sh_data['fecha_fin'])) {
        $conditions[] = "fecha_fin = :fecha_fin";
        $this->parametros['fecha_fin'] = $sh_data['fecha_fin'];
    }

    if (!empty($sh_data['horario'])) {
        $conditions[] = "horario LIKE :horario";
        $this->parametros['horario'] = '%' . $sh_data['horario'] . '%';
    }

    if (!empty($sh_data['plazas'])) {
        $conditions[] = "plazas = :plazas";
        $this->parametros['plazas'] = $sh_data['plazas'];
    }

    // Si hay condiciones, las añadimos a la consulta con AND
    if (!empty($conditions)) {
        $this->query .= " WHERE " . implode(" AND ", $conditions);
    }

    // Ejecutamos la consulta
    $this->getResultFromQuery();

    // Mensaje según los resultados obtenidos
    $this->mensaje = count($this->rows) > 0 ? 'Actividades encontradas' : 'Actividades no encontradas';

    return $this->rows ?? null;
}

public function set(){}
public function edit(){}
public function delete(){}
}
?>