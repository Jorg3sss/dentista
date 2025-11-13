<?php
// Este archivo se incluye desde credenciales.php -> index.php
// $pdo y la sesión ya están disponibles.
// $consulta (new Cita()) ya está instanciado en credenciales.php
// $rol (variable) ya está definido en credenciales.php

// ¡IMPORTANTE! 
// En login.php, guardamos el rol como "doctor". 
// Usamos "doctor" aquí, no "odontologo", para que coincida.
if ($rol != 'odontologo') {
    echo "<p style='color:red;'>Acceso denegado. Esta sección es solo para doctores.</p>";
    exit();
}

// 1. Obtener el Num_colegiado del doctor logueado
// (Según nuestro login.php, $_SESSION["id"] YA ES el Num_colegiado para un doctor)
$num_colegiado = $consulta->getDoctorNumColegiado($_SESSION["id"]);

if (!$num_colegiado) {
    // Si $num_colegiado está vacío, mostramos un error claro.
    // Esto podría pasar si la sesión se corrompe o hay un error en getDoctorNumColegiado.
    session_unset();// Cerramos sesión por seguridad
    session_destroy();
    echo "<p style='color:red;'>Error: No se pudo verificar su número de colegiado para mostrar sus citas.</p>";
    
    // Mostramos un error más específico si existe en la sesión
    if (isset($_SESSION["error"])) {
         echo "<p style='color:red;'>Detalle: " . htmlspecialchars($_SESSION["error"]) . "</p>";
         unset($_SESSION["error"]); // Limpiamos el error después de mostrarlo
    }
    exit();
}

// 2. Obtener horario
$mi_horario = $consulta->getMiHorario($num_colegiado);
?>

<h3>Mi Horario</h3>
<?php if (empty($mi_horario)): ?>
    <p>No tiene un horario asignado.</p>
    <?php if(isset($_SESSION["error"])): ?>
        <p style="color:red;"><?php echo htmlspecialchars($_SESSION["error"]); unset($_SESSION["error"]); ?></p>
    <?php endif; ?>
<?php else: ?>
    <table border="1" style="width:100%; border-collapse: collapse;">
        <thead>
            <tr>
                <th>Consultorio</th>
                <th>Días</th>
                <th>Horario</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($mi_horario as $h): ?>
                <tr>
                    <td><?php echo htmlspecialchars($h['num_consultorio']); ?></td>
                    <td><?php echo htmlspecialchars($h['dias']); ?></td>
                    <td><?php echo htmlspecialchars($h['hora_entrada'] . ' - ' . $h['hora_salida']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<hr>

<?php
// 3. Obtener citas
$citas = $consulta->getMisCitas($num_colegiado);
?>

<h3>Mis Próximas Citas (Agendadas)</h3>

<?php if(isset($_SESSION["error"])): ?>
    <p style="color:red;"><?php echo htmlspecialchars($_SESSION["error"]); unset($_SESSION["error"]); ?></p>
<?php endif; ?>

<table border="1" style="width:100%; border-collapse: collapse;">
    <thead>
        <tr>
            <th>Fecha</th>
            <th>Hora</th>
            <th>Paciente</th>
            <th>Consultorio</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($citas)): ?>
            <tr>
                <td colspan="4">No tiene citas agendadas.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($citas as $c): ?>
                <tr>
                    <td><?php echo htmlspecialchars($c['fecha']); ?></td>
                    <td><?php echo htmlspecialchars($c['hora']); ?></td>
                    <td><?php echo htmlspecialchars($c['cli_apellidos'] . ', ' . $c['cli_nombre']); ?></td>
                    <td><?php echo htmlspecialchars($c['num_consultorio']); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>