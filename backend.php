<?php
include './db/conexion.php';
header("Content-Type: application/json");

$accion = $_GET['accion'] ?? '';

if ($accion == 'agregarPaciente') {
    $data = json_decode(file_get_contents("php://input"), true);
    $stmt = $conn->prepare("INSERT INTO pacientes (nombre, telefono, correo, historial_autorizado, historial)
                            VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssis", $data['nombre'], $data['telefono'], $data['correo'], $data['autoriza'], $data['historial']);
    $stmt->execute();
    echo json_encode(["status" => "ok"]);
}

if ($accion == 'listarPacientes') {
    $result = $conn->query("SELECT * FROM pacientes ORDER BY id_paciente DESC");
    echo json_encode($result->fetch_all(MYSQLI_ASSOC));
}

if ($accion == 'buscarPaciente') {
    $busqueda = "%".$conn->real_escape_string($_GET['q'])."%";
    $stmt = $conn->prepare("SELECT * FROM pacientes WHERE nombre LIKE ? OR telefono LIKE ? OR correo LIKE ?");
    $stmt->bind_param("sss", $busqueda, $busqueda, $busqueda);
    $stmt->execute();
    $result = $stmt->get_result();
    echo json_encode($result->fetch_all(MYSQLI_ASSOC));
}

if ($accion == 'agregarCita') {
    $data = json_decode(file_get_contents("php://input"), true);
    $stmt = $conn->prepare("INSERT INTO citas (id_paciente, fecha_cita, hora_cita, tratamiento, notas_internas)
                            VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $data['id_paciente'], $data['fecha'], $data['hora'], $data['tratamiento'], $data['notas']);
    $stmt->execute();
    echo json_encode(["status" => "ok"]);
}

if ($accion == 'listarCitas') {
    $id = intval($_GET['id_paciente']);
    $result = $conn->query("SELECT * FROM citas WHERE id_paciente=$id ORDER BY fecha_cita DESC");
    echo json_encode($result->fetch_all(MYSQLI_ASSOC));
}
?>
