<?php 
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar</title>
</head>
<body>
    <h2>Recuperar contraseña</h2>
    <?php if(isset($_SESSION["error"])): ?>
        <p style="color:red;"><?php echo $_SESSION["error"]; unset($_SESSION["error"]); ?></p>
    <?php endif; ?>

    <form action="login.php?id=2" method="POST">
        <label>Correo</label><br>
        <input type="email" name="correo" required><br>
        <label>Nueva contraseña</label><br>
        <input type="password" name="nueva_contraseña" required><br> <!-- Cambiado a type="password" -->
        <label>Confirmar contraseña</label><br>
        <input type="password" name="confirmar_contraseña" required><br> <!-- Cambiado a type="password" -->
        <input type="submit" value="Recuperar"><br>
        <a href="../index.php">Volver a inicio</a>
    </form>
</body>
</html>