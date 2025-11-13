<?php
session_start();
require_once("../db/conexion.php");

if (isset($_GET['id'])) {
    $op = $_GET['id'];

    // op=1 es para Iniciar Sesión
    if ($op == "1") {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $correo = $_POST["correo"];
            $contra = $_POST["contraseña"];

            if (!str_contains($correo, ".com")) {
                $_SESSION["error"] = "El correo no es válido (debe contener .com)";
                header("Location: ../index.php");
                exit();
            }

            try {
                // 1. Intentar encontrar en la tabla 'usuario'
                $stmt = $pdo->prepare("SELECT * FROM usuario WHERE correo = ?");
                $stmt->execute([$correo]);
                $user = $stmt->fetch();

                if ($user) {
                    // Usuario encontrado en 'usuario'
                    if (password_verify($contra, $user['contraseña'])) {
                        // Contraseña correcta
                        $_SESSION["online"] = true;
                        $_SESSION["id"] = $user['id'];
                        $_SESSION["rol"] = $user['rol']; // Guardamos el rol
                        header("Location: ../index.php");
                        exit();
                    } else {
                        // Contraseña incorrecta
                        $_SESSION["error"] = "Contraseña incorrecta";
                        header("Location: ../index.php");
                        exit();
                    }
                } else {
                    // 2. Si no se encuentra en 'usuario', buscar en 'doctor'
                    $stmt_doctor = $pdo->prepare("SELECT * FROM doctor WHERE correo = ?");
                    $stmt_doctor->execute([$correo]);
                    $doctor = $stmt_doctor->fetch();

                    if ($doctor) {
                        // Usuario encontrado en 'doctor'
                        if (password_verify($contra, $doctor['contraseña'])) {
                            // Contraseña correcta
                            $_SESSION["online"] = true;
                            // Para un doctor, su 'id' de sesión será su Num_colegiado
                            $_SESSION["id"] = $doctor['Num_colegiado'];
                            $_SESSION["rol"] = "doctor"; // Asignamos el rol manualmente
                            header("Location: ../index.php");
                            exit();
                        } else {
                            // Contraseña incorrecta
                            $_SESSION["error"] = "Contraseña incorrecta";
                            header("Location: ../index.php");
                            exit();
                        }
                    } else {
                        // 3. Si no se encuentra en ninguna tabla
                        $_SESSION["error"] = "Correo no registrado";
                        header("Location: ../index.php");
                        exit();
                    }
                }
            } catch (PDOException $e) {
                $_SESSION["error"] = "Error en la consulta: " . $e->getMessage();
                header("Location: ../index.php");
                exit();
            }
        }
    }

    // op=2 es para Recuperar Contraseña
    if ($op == "2") {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $correo = $_POST["correo"];
            $nueva_contraseña = $_POST["nueva_contraseña"];
            $confirmar_contraseña = $_POST["confirmar_contraseña"];

            if ($nueva_contraseña !== $confirmar_contraseña) {
                $_SESSION["error"] = "Las contraseñas no coinciden";
                header("Location: recuperar.php");
                exit();
            }

            $hashed_password = password_hash($nueva_contraseña, PASSWORD_DEFAULT);

            try {
                // 1. Intentar buscar en 'usuario'
                $stmt = $pdo->prepare("SELECT id, rol FROM usuario WHERE correo = ?");
                $stmt->execute([$correo]);
                $user = $stmt->fetch();

                if ($user) {
                    // Encontrado en 'usuario', actualizar allí
                    $stmt_update = $pdo->prepare("UPDATE usuario SET contraseña = ? WHERE correo = ?");
                    $stmt_update->execute([$hashed_password, $correo]);

                    $_SESSION["online"] = true;
                    $_SESSION["id"] = $user['id'];
                    $_SESSION["rol"] = $user['rol']; // Guardamos el rol
                    header("Location: ../index.php");
                    exit();

                } else {
                    // 2. Si no, intentar buscar en 'doctor'
                    $stmt_doctor = $pdo->prepare("SELECT Num_colegiado FROM doctor WHERE correo = ?");
                    $stmt_doctor->execute([$correo]);
                    $doctor = $stmt_doctor->fetch();

                    if ($doctor) {
                        // Encontrado en 'doctor', actualizar allí
                        $stmt_update = $pdo->prepare("UPDATE doctor SET contraseña = ? WHERE correo = ?");
                        $stmt_update->execute([$hashed_password, $correo]);

                        $_SESSION["online"] = true;
                        $_SESSION["id"] = $doctor['Num_colegiado'];
                        $_SESSION["rol"] = "doctor"; // Asignamos rol
                        header("Location: ../index.php");
                        exit();
                    } else {
                        // 3. No encontrado en ninguna tabla
                        $_SESSION["error"] = "El correo no está registrado";
                        header("Location: recuperar.php");
                        exit();
                    }
                }
            } catch (PDOException $e) {
                $_SESSION["error"] = "Error en la consulta: " . $e->getMessage();
                header("Location: recuperar.php");
                exit();
            }
        }
    }
}
?>