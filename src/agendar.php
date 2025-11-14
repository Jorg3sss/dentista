<?php
// Este archivo se incluye desde credenciales.php, que está en index.php
// $pdo y la sesión ya están disponibles.
// $consulta (new Cita()) ya está instanciado en credenciales.php

// 1. Manejar el envío del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agendar_cita'])) {
    $consulta->agendarCita(); // <-- AQUÍ SE LLAMA LA LÓGICA
    // agendarCita() guarda el error o éxito en $_SESSION.
    // Redirigimos para evitar reenvío de formulario y mostrar el mensaje.
    header("Location: index.php"); 
    exit();
}

// 2. Obtener horarios de doctores para la tabla
$horarios = $consulta->getHorariosDoctores();

// 3. Verificar si hay un mensaje de éxito
if (isset($_SESSION["success"])): ?>
    <div style="color:green;">
        <?php echo $_SESSION["success"]; unset($_SESSION["success"]); ?>
    </div>
    <a href="index.php">Volver al inicio</a>
<?php else: ?>
    
    <?php if(isset($_SESSION["error"])): ?>
        <p style="color:red;"><?php echo $_SESSION["error"]; unset($_SESSION["error"]); ?></p>
    <?php endif; ?>

    <h3>Horarios de Doctores</h3>
    <table border="1" style="width:100%; border-collapse: collapse;">
        <thead>
            <tr>
                <th>Doctor</th>
                <th>N° Colegiado</th>
                <th>Consultorio</th>
                <th>Días</th>
                <th>Horario</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($horarios)): ?>
                <tr>
                    <td colspan="5">No hay horarios de doctores disponibles.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($horarios as $h): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($h['apellido'] . ', ' . $h['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($h['Num_colegiado']); ?></td>
                        <td><?php echo htmlspecialchars($h['num_consultorio']); ?></td>
                        <td><?php echo htmlspecialchars($h['dias']); ?></td>
                        <td><?php echo htmlspecialchars($h['hora_entrada'] . ' - ' . $h['hora_salida']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <hr>

    <h3>Agendar Nueva Cita</h3>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
        <label>Correo del paciente</label><br>
        <input type="email" name="correo_paciente" required><br>
        
        <label>Número de colegiado</label><br>
        <input type="text" name="num_colegiado" required><br>
        
        <label>Número de consultorio</label><br>
        <input type="text" name="num_consultorio" required><br>
        
        <label>Fecha</label><br>
        <input type="date" name="fecha" required><br>
        
        <label>Hora (formato 24h, ej: 14:30)</label><br>
        <input type="time" name="hora" required><br><br>
        
        <input type="submit" name="agendar_cita" value="Agendar cita"><br>
    </form>
    

<?php 
endif;
require_once("odontologos.html"); ?>

