<?php
// Este archivo AHORA CONTIENE TODA LA LÓGICA
// Usamos tu conexión PDO
require_once './db/conexion.php'; 

header("Content-Type: application/json");

// Hacemos que $pdo esté disponible
global $pdo;

$accion = $_GET['accion'] ?? '';

// --- ACCIONES DE ODONTOLOGO (TABLA DOCTOR) ---

if ($accion == 'agregarOdontologo') {
    $data = json_decode(file_get_contents("php://input"), true);

    $nombre = $data['nombre'] ?? '';
    $apellido = $data['apellido'] ?? '';
    $edad = $data['edad'] ?? 0;
    $num_colegiado = $data['numero_colegiado'] ?? null;
    $correo = $data['correo'] ?? null;
    $telefono = $data['telefono'] ?? null;
    $contrasena = $data['contrasena'] ?? '123456';
    $hashed_password = password_hash($contrasena, PASSWORD_DEFAULT);
    
    try {
        $stmt = $pdo->prepare(
            "INSERT INTO doctor (Num_colegiado, nombre, apellido, edad, correo, telefono, contraseña)
            VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $num_colegiado, $nombre, $apellido, $edad, $correo, $telefono, $hashed_password
        ]);
        echo json_encode(["status" => "ok", "info" => "Doctor agregado con éxito."]);
    
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}

if ($accion == 'listarOdontologos') {
    try {
        $stmt = $pdo->query("SELECT Num_colegiado, nombre, apellido, telefono, correo FROM doctor ORDER BY apellido, nombre");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}

if ($accion == 'buscarOdontologo') {
    try {
        $busqueda_texto = "%" . ($_GET['q'] ?? '') . "%";
        $busqueda_num = $_GET['q'] ?? ''; 
        
        $stmt = $pdo->prepare(
            "SELECT Num_colegiado, nombre, apellido, telefono, correo FROM doctor 
             WHERE nombre LIKE ? OR apellido LIKE ? OR Num_colegiado = ?"
        );
        $stmt->execute([$busqueda_texto, $busqueda_texto, $busqueda_num]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}

if ($accion == 'eliminarOdontologo') {
    try {
        $num_colegiado = intval($_GET['id']);
        $stmt = $pdo->prepare("DELETE FROM doctor WHERE Num_colegiado = ?");
        $stmt->execute([$num_colegiado]);
        echo json_encode(["status" => "ok"]);

    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}


// --- ACCIONES DE PACIENTE (TABLA CLIENTE) ---

if ($accion == 'agregarCliente') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    try {
        $stmt = $pdo->prepare(
            "INSERT INTO cliente (nombre, apellidos, correo, telefono, historial) 
             VALUES (?, ?, ?, ?, ?)"
        );
        
        $stmt->execute([
            $data['nombre'],
            $data['apellido'],
            $data['correo'],
            $data['telefono'],
            $data['historial']
        ]);
        
        echo json_encode(["status" => "ok", "info" => "Cliente agregado con éxito."]);
    
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}

if ($accion == 'listarClientes') {
    try {
        $stmt = $pdo->query("SELECT id, nombre, apellidos, telefono, correo, historial 
                             FROM cliente 
                             ORDER BY apellidos, nombre");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}

if ($accion == 'buscarCliente') {
    try {
        $busqueda = "%" . ($_GET['q'] ?? '') . "%";
        
        $stmt = $pdo->prepare(
            "SELECT * FROM cliente 
             WHERE nombre LIKE ? OR apellidos LIKE ? OR correo LIKE ? OR telefono LIKE ?"
        );
        
        $stmt->execute([$busqueda, $busqueda, $busqueda, $busqueda]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}

// --- ACCIONES DE CITAS (TABLA CONSULTA) ---

if ($accion == 'listarConsultas') {
    try {
        $id_cliente = intval($_GET['id_cliente'] ?? 0);
        
        $stmt = $pdo->prepare(
            "SELECT fecha, hora, num_colegiado, num_consultorio, estado 
             FROM consulta 
             WHERE id_cliente = ? 
             ORDER BY fecha, hora DESC"
        );
        
        $stmt->execute([$id_cliente]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}
?>