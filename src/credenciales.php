<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Credenciales</title>
</head>
<body>
    <h2>Iniciar sesión</h2>
    <form action="login.php?id=1" method="POST">
        <label>Correo</label><br>
        <input type="text" name="correo" required><br>
        <label>contraseña</label><br>
        <input type="text" name="contraseña" required><br>
        <input type="submit"><br>
        <a href="login.php?id=2">Recuperar contraseña</a><br>
    </form>
</body>
</html>