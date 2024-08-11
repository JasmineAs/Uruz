<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="Css/login.css">
</head>
<body>
    <div class="form-container">
        <h1>Iniciar Sesión</h1>
        <form action="process_login.php" method="post">
            <div class="form-group">
                <label for="email">Correo Electrónico:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <button type="submit">Iniciar Sesión</button>
            </div>
            <div class="form-group">
                <a href="restablecercontrasena.php" class="forgot-password">¿Has olvidado tu contraseña?</a>
            </div>
        </form>
    </div>
</body>
</html>
