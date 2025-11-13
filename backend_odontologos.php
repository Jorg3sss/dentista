<?php
// Usamos tu conexión PDO
// Asumimos que este archivo está en la carpeta raíz (junto a index.php)
// y tu conexión está en 'db/conexion.php'
require_once './db/conexion.php'; 

header("Content-Type: application/json");

// Hacemos que $pdo esté disponible
global $pdo;

$accion = $_GET['accion'] ?? '';

// --- ACCIONES DE ODONTOLOGO (TABLA DOCTOR) ---

if ($accion == 'agregarOdontologo') {
    $data = json_decode(file_get_contents("php://input"), true);

    // ADAPTACIÓN: Leer todos los campos del formulario
    $nombre = $data['nombre'] ?? '';
    $apellido = $data['apellido'] ?? '';
    $edad = $data['edad'] ?? 0;
    $num_colegiado = $data['numero_colegiado'] ?? null;
    $correo = $data['correo'] ?? null;
    $telefono = $data['telefono'] ?? null;
    $contrasena = $data['contrasena'] ?? '123456'; // Contraseña de fallback
    $hashed_password = password_hash($contrasena, PASSWORD_DEFAULT);
    
    // ADAPTACIÓN: Campos 'especialidad' y 'horario' del formulario ya no existen.
    
    try {
        $stmt = $pdo->prepare(
            "INSERT INTO doctor (Num_colegiado, nombre, apellido, edad, correo, telefono, contraseña)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        
        $stmt->execute([
            $num_colegiado,
            $nombre,
            $apellido,
            $edad,
            $correo,
            $telefono,
            $hashed_password
        ]);
        
        echo json_encode(["status" => "ok", "info" => "Doctor agregado con éxito."]);
    
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}

if ($accion == 'listarOdontologos') {
    try {
        // ADAPTACIÓN: Seleccionar de 'doctor'
        $stmt = $pdo->query("SELECT Num_colegiado, nombre, apellido, telefono, correo FROM doctor ORDER BY apellido, nombre");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}

if ($accion == 'buscarOdontologo') {
    try {
        // CORRECCIÓN: Preparamos 2 variables para la búsqueda
        $busqueda_texto = "%" . ($_GET['q'] ?? '') . "%";
        $busqueda_num = $_GET['q'] ?? ''; // Para búsqueda exacta de Num_colegiado
        
        // ADAPTACIÓN: Buscar en 'doctor' por nombre/apellido (LIKE) o Num_colegiado (EXACTO)
        $stmt = $pdo->prepare(
            "SELECT Num_colegiado, nombre, apellido, telefono, correo FROM doctor 
             WHERE nombre LIKE ? OR apellido LIKE ? OR Num_colegiado = ?"
        );
        
        // Ejecutamos con ambos tipos de variables
        $stmt->execute([$busqueda_texto, $busqueda_texto, $busqueda_num]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}

if ($accion == 'eliminarOdontologo') {
    try {
        $num_colegiado = intval($_GET['id']); // El ID ahora es el Num_colegiado
        
        // ADAPTACIÓN: Borrar de 'doctor' usando 'Num_colegiado'
        $stmt = $pdo->prepare("DELETE FROM doctor WHERE Num_colegiado = ?");
        $stmt->execute([$num_colegiado]);
        
        echo json_encode(["status" => "ok"]);

    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}
?>