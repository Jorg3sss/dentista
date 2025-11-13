<body>
    <?php 
    $consulta = new Cita();
    if(!isset($_SESSION["online"])): ?>
        <!-- Formulario de Login -->
        <?php if(isset($_SESSION["error"])): ?>
            <p style="color:red;"><?php echo $_SESSION["error"]; unset($_SESSION["error"]); ?></p>
        <?php endif; ?>
        <h2>Iniciar sesión</h2>
        <form action="src/login.php?id=1" method="POST">
            <label>Correo</label><br>
            <input type="email" name="correo" required><br>
            <label>Contraseña</label><br>
            <input type="password" name="contraseña" required><br> <!-- Cambiado a type="password" -->
            <input type="submit" value="Iniciar Sesión"><br>
            <a href="src/recuperar.php">Recuperar contraseña</a><br>
        </form>
    <?php else: ?>
        <!-- Contenido para usuarios logueados -->
        <?php
        // $consulta ya está instanciado en index.php
        $rol = $consulta->getRol();

        if($rol == 'admin' || $rol == 'recepcionista'){
            echo '<h2>Panel de Administración/Recepción</h2>';
            echo '<a href="src/ver_citas.php">Ver y Gestionar Citas</a><br><br>';
            require_once("src/agendar.php");
        }
        else if ($rol == 'odontologo') {
            echo '<h2>Panel de odontologo</h2>';
            require_once("src/mis_citas.php");
        }
        else {
            echo '<p>Rol no reconocido.</p>';
        }
        ?>
        <br><br>
        <a href="src/logout.php">Cerrar sesión</a>
    <?php endif; ?>
</body>