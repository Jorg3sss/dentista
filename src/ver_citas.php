<?php
session_start();
require_once("../db/conexion.php");
require_once("cita.php");

$consulta = new Cita();
$rol = $consulta->getRol();

if ($rol != 'admin' && $rol != 'recepcionista') {
    header("Location: ../index.php");
    exit();
}

$todas_las_citas = $consulta->getAllCitas();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Citas</title>
    <script>
        function confirmarAccion(accion, id) {
            let mensaje = accion === 'cancelar' 
                ? '¿Está seguro de que desea CANCELAR esta cita?' 
                : '¿Está seguro de que desea marcar esta cita como REPROGRAMADA? (Deberá agendar la nueva manualmente)';
            
            if (confirm(mensaje)) {
                window.location.href = 'gestionar_cita.php?accion=' + accion + '&id=' + id;
            }
        }
    </script>
</head>
<body>
    <h2>Gestionar Todas las Citas</h2>

    <a href="../index.php">Volver a Agendar</a><br><br>

    <?php if(isset($_SESSION["error"])): ?>
        <p style="color:red;"><?php echo $_SESSION["error"]; unset($_SESSION["error"]); ?></p>
    <?php endif; ?>
    <?php if(isset($_SESSION["success"])): ?>
        <p style="color:green;"><?php echo $_SESSION["success"]; unset($_SESSION["success"]); ?></p>
    <?php endif; ?>

    <table border="1" style="width:100%; border-collapse: collapse;">
        <thead>
            <tr>
                <th>ID Cita</th>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Paciente</th>
                <th>Doctor</th>
                <th>Consultorio</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($todas_las_citas)): ?>
                <tr>
                    <td colspan="8">No hay citas agendadas.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($todas_las_citas as $c): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($c['id']); ?></td>
                        <td><?php echo htmlspecialchars($c['fecha']); ?></td>
                        <td><?php echo htmlspecialchars($c['hora']); ?></td>
                        <td><?php echo htmlspecialchars($c['cli_apellidos'] . ', ' . $c['cli_nombre']); ?></td>
                        <td><?php echo htmlspecialchars($c['doc_apellido'] . ', ' . $c['doc_nombre']); ?></td>
                        <td><?php echo htmlspecialchars($c['num_consultorio']); ?></td>
                        <td><?php echo htmlspecialchars($c['estado']); ?></td>
                        <td>
                            <!-- Usamos JS para la confirmación -->
                            <button onclick="confirmarAccion('cancelar', <?php echo $c['id']; ?>)">Cancelar</button>
                            <button onclick="confirmarAccion('reprogramar', <?php echo $c['id']; ?>)">Reprogramar</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    
</body>
</html>