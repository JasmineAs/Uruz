<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
    <link rel="stylesheet" href="Css/admin.css">
</head>
<body>

<div class="sidebar">
    <h2>Panel de Administración</h2>
    <a href="transacciones.php">Transacciones</a>
    <a href="cuentacoach.php">Administrar cuentas de coach</a>
    <a href="logout.php">Cerrar sesión</a>
</div>
<div class="content">
    <div class="header">
        <h1>Bienvenido al Panel de Administración</h1>
        <p>Seleccione una opción del menú para empezar.</p>
    </div>
    <div class="dashboard">
        <div class="card">
            <h3>Miembros</h3>
            <p>
                <?php
                // Conectar a la base de datos y obtener la cantidad de miembros
                include('conexion.php'); // Asegúrate de incluir tu archivo de conexión a la base de datos
                $result = $conn->query("SELECT COUNT(id) AS total FROM miembros");
                $row = $result->fetch_assoc();
                echo $row['total'];
                ?>
            </p>
        </div>
        <div class="card">
            <h3>Coaches</h3>
            <p>
                <?php
                // Obtener la cantidad de coaches
                $result = $conn->query("SELECT COUNT(id) AS total FROM coach");
                $row = $result->fetch_assoc();
                echo $row['total'];
                ?>
            </p>
        </div>
    </div>
</div>

</body>
</html>
