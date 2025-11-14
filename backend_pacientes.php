<?php
require_once './db/conexion.php'; 

header("Content-Type: application/json");

// Hacemos que $pdo esté disponible
global $pdo;

$accion = $_GET['accion'] ?? '';

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
