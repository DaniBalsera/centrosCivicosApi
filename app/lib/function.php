<?php
/**
 * 
 * FUNCION PARA CONECTARSE A LA BASE DE DATOS
 * 
 * @author Héctor Mora Sánchez
 */

function clearData($data) {
    $data = trim($data);
    $data = stripcslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>
