<?php
// Conexión con la base de datos
$conexion = new mysqli("localhost", "root", "", "consultorio_db");

// Verificar conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Datos precargados de consultorios
$consultorios = [
    ['Consultorio A', 1, 'Ortodoncia', '8:00 - 16:00', 'Disponible'],
    ['Consultorio B', 2, 'Odontopediatría', '9:00 - 17:00', 'En mantenimiento'],
    ['Consultorio C', 3, 'Endodoncia', '10:00 - 18:00', 'Fuera de servicio']
];

// Datos precargados de odontólogos
$odontologos = [
    ['Dr. Juan Pérez', 'Ortodoncia'],
    ['Dra. Ana Gómez', 'Odontopediatría'],
    ['Dr. Luis Ramírez', 'Endodoncia']
];

// Insertar consultorios
foreach ($consultorios as $c) {
    $conexion->query("INSERT INTO consultorios (nombre, numero, especialidad, horario, estado)
                      VALUES ('$c[0]', '$c[1]', '$c[2]', '$c[3]', '$c[4]')");
}

// Insertar odontólogos
foreach ($odontologos as $o) {
    $conexion->query("INSERT INTO odontologos (nombre, especialidad)
                      VALUES ('$o[0]', '$o[1]')");
}

// Asignaciones (relaciones)
$conexion->query("INSERT INTO asignaciones (consultorio_id, odontologo_id) VALUES (1, 1)");
$conexion->query("INSERT INTO asignaciones (consultorio_id, odontologo_id) VALUES (2, 2)");
$conexion->query("INSERT INTO asignaciones (consultorio_id, odontologo_id) VALUES (3, 3)");

echo "<h2>✅ Datos precargados e insertados correctamente en la base de datos.</h2>";

$conexion->close();
?>