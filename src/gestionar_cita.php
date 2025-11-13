<?php
session_start();
require_once("../db/conexion.php");
require_once("cita.php");

$consulta = new Cita();
$rol = $consulta->getRol();

// Seguridad: Solo admin y recepcionista pueden gestionar
if ($rol != 'admin' && $rol != 'recepcionista') {
    $_SESSION["error"] = "Acceso no autorizado.";
    header("Location: ../index.php");
    exit();
}

$accion = $_GET['accion'] ?? null;
$id_consulta = $_GET['id'] ?? null;

if (!$accion || !$id_consulta) {
    $_SESSION["error"] = "Acción o ID de cita no especificado.";
    header("Location: ver_citas.php");
    exit();
}

if ($accion == 'cancelar') {
    $consulta->cancelarCita($id_consulta);
} elseif ($accion == 'reprogramar') {
    $consulta->reprogramarCita($id_consulta);
} else {
    $_SESSION["error"] = "Acción no válida.";
}

// Redirigir de vuelta a la lista de citas (donde se mostrará el mensaje de éxito/error)
header("Location: ver_citas.php");
exit();
?>