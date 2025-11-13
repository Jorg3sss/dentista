<?php
// session_start() ya está en index.php, no es necesario aquí si se incluye desde allí.
// db/conexion.php ya está incluido en index.php, por lo que $pdo está global.

class Cita {
    private $id_usuario_actual;
    private $rol; // Propiedad para guardar el rol

    public function __construct() {
        $this->id_usuario_actual = $_SESSION["id"] ?? null;
        // Leer el rol desde la sesión al construir
        $this->rol = $_SESSION["rol"] ?? null;
    }

    public function getRol() {
        // Devuelve el rol que ya obtuvimos de la sesión
        // Si no está en la sesión (p.ej. sesión antigua), búscalo como antes.
        if ($this->rol) {
            return $this->rol;
        }

        // --- Lógica anterior como fallback ---
        global $pdo;
        if ($this->id_usuario_actual) {
            try {
                // Solo buscamos en 'usuario' porque 'doctor' se establece manualmente
                $stmt = $pdo->prepare("SELECT rol FROM usuario WHERE id = ?");
                $stmt->execute([$this->id_usuario_actual]);
                $user = $stmt->fetch();
                if ($user) {
                    $_SESSION["rol"] = $user['rol']; // Guardar para la próxima vez
                    $this->rol = $user['rol']; // Actualizar en el objeto
                    return $user['rol'];
                }
            } catch (PDOException $e) {
                $_SESSION["error"] = "Error en la consulta de rol: " . $e->getMessage();
                return null;
            }
        }
        return null; // No se pudo determinar el rol
    }

    /**
     * Obtiene el Num_colegiado de un doctor basado en su id de usuario.
     * Asume que el correo en la tabla 'usuario' coincide con el correo en la tabla 'doctor'.
     */
    public function getDoctorNumColegiado($id_usuario) {
        global $pdo;

        // Si el rol es 'doctor', el ID en sesión YA ES el Num_colegiado
        if ($this->rol == 'doctor') {
            return $id_usuario;
        }

        // Si es admin/recepcionista, busca por ID de usuario (si es necesario)
        try {
            $stmt = $pdo->prepare("SELECT d.Num_colegiado FROM doctor d JOIN usuario u ON d.correo = u.correo WHERE u.id = ?");
            $stmt->execute([$id_usuario]);
            $doctor = $stmt->fetch();
            return $doctor ? $doctor['Num_colegiado'] : null;
        } catch (PDOException $e) {
            $_SESSION["error"] = "Error al obtener número de colegiado: " . $e->getMessage();
            return null;
        }
    }

    /**
     * Obtiene los horarios de todos los doctores para mostrarlos en agendar.php
     */
    public function getHorariosDoctores() {
        global $pdo;
        try {
            // Asumimos que horario.id_consultorio es el NÚMERO de consultorio
            $stmt = $pdo->query("SELECT d.nombre, d.apellido, d.Num_colegiado, h.hora_entrada, h.hora_salida, h.dias, h.id_consultorio AS num_consultorio 
                                 FROM doctor d 
                                 JOIN horario h ON d.Num_colegiado = h.num_colegiado
                                 ORDER BY d.apellido, d.nombre");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $_SESSION["error"] = "Error al obtener horarios: " . $e->getMessage();
            return [];
        }
    }

    /**
     * Obtiene el horario de un doctor específico.
     */
    public function getMiHorario($num_colegiado) {
        global $pdo;
        try {
            $stmt = $pdo->prepare("SELECT d.nombre, d.apellido, h.hora_entrada, h.hora_salida, h.dias, h.id_consultorio AS num_consultorio 
                                   FROM doctor d 
                                   JOIN horario h ON d.Num_colegiado = h.num_colegiado
                                   WHERE d.Num_colegiado = ?");
            $stmt->execute([$num_colegiado]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $_SESSION["error"] = "Error al obtener mi horario: " . $e->getMessage();
            return [];
        }
    }

    /**
     * Lógica principal para agendar una cita con todas las validaciones.
     */
    public function agendarCita() {
        global $pdo;
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return false;
        }

        $correo_paciente = $_POST['correo_paciente'] ?? null;
        $num_colegiado = $_POST['num_colegiado'] ?? null;
        $num_consultorio = $_POST['num_consultorio'] ?? null;
        $fecha = $_POST['fecha'] ?? null;
        $hora = $_POST['hora'] ?? null;

        if (!$correo_paciente || !$num_colegiado || !$num_consultorio || !$fecha || !$hora) {
            $_SESSION["error"] = "Faltan datos para agendar la cita.";
            return false;
        }

        try {
            // 1. Verificar paciente
            $stmt = $pdo->prepare("SELECT id FROM cliente WHERE correo = ?");
            $stmt->execute([$correo_paciente]);
            $paciente = $stmt->fetch();
            if (!$paciente) {
                $_SESSION["error"] = "El correo del paciente no existe.";
                return false;
            }
            $id_paciente = $paciente['id'];

            // 2. Verificar doctor
            $stmt = $pdo->prepare("SELECT Num_colegiado FROM doctor WHERE Num_colegiado = ?");
            $stmt->execute([$num_colegiado]);
            if (!$stmt->fetch()) {
                $_SESSION["error"] = "El número de colegiado no es válido.";
                return false;
            }

            // 3. Verificar consultorio
            $stmt = $pdo->prepare("SELECT num_consultorio FROM consultorio WHERE num_consultorio = ?");
            $stmt->execute([$num_consultorio]);
            if (!$stmt->fetch()) {
                $_SESSION["error"] = "El número de consultorio no es válido.";
                return false;
            }

            // 4. Verificar horario del doctor
            $stmt = $pdo->prepare("SELECT * FROM horario WHERE num_colegiado = ?");
            $stmt->execute([$num_colegiado]);
            $horario = $stmt->fetch();
            if (!$horario) {
                $_SESSION["error"] = "El doctor no tiene un horario asignado.";
                return false;
            }

            // 5. Verificar si el consultorio coincide
            // Asumimos que horario.id_consultorio es el NÚMERO de consultorio
            if ($horario['id_consultorio'] != $num_consultorio) {
                $_SESSION["error"] = "El doctor no atiende en el consultorio indicado.";
                return false;
            }

            // 6. Validar fecha (no más de 1 año en el futuro, no en el pasado)
            $fecha_cita = new DateTime($fecha);
            $fecha_actual = new DateTime();
            $fecha_limite = (new DateTime())->add(new DateInterval('P1Y'));

            if ($fecha_cita < $fecha_actual->setTime(0, 0, 0)) {
                $_SESSION["error"] = "La fecha no puede ser en el pasado.";
                return false;
            }
            if ($fecha_cita > $fecha_limite) {
                $_SESSION["error"] = "La fecha no puede ser más de un año en el futuro.";
                return false;
            }

            // 7. Validar día de la semana
            // 'N' devuelve 1 (Lunes) a 7 (Domingo)
            $dia_semana_num = $fecha_cita->format('N');
            $dias_doctor = strtolower($horario['dias']); // ej: "lunes - viernes" o "sabado - domingo"
            
            $diaValido = false;
            if (str_contains($dias_doctor, "lunes") && $dia_semana_num >= 1 && $dia_semana_num <= 5) $diaValido = true;
            if (str_contains($dias_doctor, "sabado") && $dia_semana_num == 6) $diaValido = true;
            if (str_contains($dias_doctor, "domingo") && $dia_semana_num == 7) $diaValido = true;
            // TODO: Mejorar esta lógica si los rangos son más complejos (ej: "lunes - viernes")

            if (!$diaValido) {
                $_SESSION["error"] = "El doctor no atiende en el día seleccionado. Días de atención: " . $horario['dias'];
                return false;
            }

            // 8. Validar hora
            $hora_cita_time = strtotime($hora);
            $hora_entrada_time = strtotime($horario['hora_entrada']);
            $hora_salida_time = strtotime($horario['hora_salida']);

            if ($hora_cita_time < $hora_entrada_time || $hora_cita_time >= $hora_salida_time) {
                $_SESSION["error"] = "La hora está fuera del horario del doctor (" . $horario['hora_entrada'] . " - " . $horario['hora_salida'] . ").";
                return false;
            }

            // 9. Verificar disponibilidad en 'consulta'
            $stmt = $pdo->prepare("SELECT id_consulta FROM consulta WHERE fecha = ? AND hora = ? AND num_consultorio = ? AND (estado = 'agendada' OR estado = 'reprogramada')");
            $stmt->execute([$fecha, $hora, $num_consultorio]);
            if ($stmt->fetch()) {
                $_SESSION["error"] = "Ya existe una consulta agendada para esa fecha, hora y consultorio.";
                return false;
            }

            // 10. Insertar cita
            return $this->insertarCita($id_paciente, $num_colegiado, $num_consultorio, $fecha, $hora);

        } catch (PDOException $e) {
            $_SESSION["error"] = "Error en la consulta: " . $e->getMessage();
            return false;
        } catch (Exception $e) {
            $_SESSION["error"] = "Error en la validación de fecha/hora: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Inserta la cita en la tabla 'consulta'.
     */
    public function insertarCita($id_paciente, $num_colegiado, $num_consultorio, $fecha, $hora) {
        global $pdo;
        try {
            // Asumimos que la tabla 'consulta' tiene estas columnas
            $stmt = $pdo->prepare("INSERT INTO consulta (id_cliente, num_colegiado, num_consultorio, fecha, hora, estado) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$id_paciente, $num_colegiado, $num_consultorio, $fecha, $hora, 'agendada']);
            
            $_SESSION["success"] = "¡Cita agendada con éxito!";
            return true;
        } catch (PDOException $e) {
            $_SESSION["error"] = "Error al agendar la cita: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Obtiene todas las citas (agendadas o reprogramadas).
     */
    public function getAllCitas() {
        global $pdo;
        try {
            $stmt = $pdo->query("SELECT con.*, 
                                        cli.nombre AS cli_nombre, cli.apellidos AS cli_apellidos, 
                                        doc.nombre AS doc_nombre, doc.apellido AS doc_apellido 
                                 FROM consulta con 
                                 JOIN cliente cli ON con.id_cliente = cli.id 
                                 JOIN doctor doc ON con.num_colegiado = doc.Num_colegiado
                                 WHERE con.estado = 'agendada'
                                 ORDER BY con.fecha, con.hora");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $_SESSION["error"] = "Error al obtener todas las citas: " . $e->getMessage();
            return [];
        }
    }

    /**
     * Obtiene las citas de un doctor específico.
     */
    public function getMisCitas($num_colegiado) {
        global $pdo;
        try {
            $stmt = $pdo->prepare("SELECT con.*, 
                                          cli.nombre AS cli_nombre, cli.apellidos AS cli_apellidos
                                   FROM consulta con 
                                   JOIN cliente cli ON con.id_cliente = cli.id
                                   WHERE con.num_colegiado = ? AND con.estado = 'agendada'
                                   ORDER BY con.fecha, con.hora");
            $stmt->execute([$num_colegiado]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $_SESSION["error"] = "Error al obtener mis citas: " . $e->getMessage();
            return [];
        }
    }

    /**
     * Cancela una cita.
     */
    public function cancelarCita($id_consulta) {
        global $pdo;
        try {
            $stmt = $pdo->prepare("UPDATE consulta SET estado = 'cancelada' WHERE id_consulta = ?");
            $stmt->execute([$id_consulta]);
            $_SESSION["success"] = "Cita cancelada correctamente.";
            return true;
        } catch (PDOException $e) {
            $_SESSION["error"] = "Error al cancelar la cita: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Marca una cita como 'reprogramada'. El admin deberá crear una nueva.
     */
    public function reprogramarCita($id_consulta) {
        global $pdo;
        try {
            // El requisito dice "actualizar... 'actualizada'". 
            // Usar un estado es más limpio que cambiar la fecha/hora a un texto.
            $stmt = $pdo->prepare("UPDATE consulta SET estado = 'reprogramada' WHERE id_consulta = ?");
            $stmt->execute([$id_consulta]);
            $_SESSION["success"] = "Cita marcada como 'reprogramada'. Por favor, agende la nueva cita.";
            return true;
        } catch (PDOException $e) {
            $_SESSION["error"] = "Error al reprogramar la cita: " . $e->getMessage();
            return false;
        }
    }
}
?>