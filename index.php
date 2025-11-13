<?php
session_start();
// Asegurarse de que la conexión esté disponible para la clase Cita
require_once("db/conexion.php"); 
require_once("src/cita.php");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clínica Dental</title>
</head>
<?php 
// credenciales.php maneja si mostrar login o el contenido de la app
require_once("src/credenciales.php");
?>
</html>
