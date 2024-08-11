<?php
session_start();
require_once 'conexion.php';

// Consulta para recuperar los planes
$sql = "SELECT id, plan, valor, duracion FROM tipoplan";
$result = $conn->query($sql);

if ($result === false) {
    die("Error al recuperar los planes: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Miembro</title>
    <link rel="stylesheet" href="Css/miembro.css">
</head>
<body>
    <div class="form-container">
        <h1>Formulario de Miembro</h1>
        <form action="checkout.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Nombre:</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="email">Correo Electrónico:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="phone">Teléfono:</label>
                <input type="tel" id="phone" name="phone" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="photo">Foto de Perfil:</label>
                <input type="file" id="photo" name="photo" accept="image/*">
            </div>
            <h2>Elige tu Plan</h2>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $planId = $row['id'];
                    $nombre = htmlspecialchars($row['plan']);
                    // Formatear el valor con separador de miles
                    $valor = number_format($row['valor'], 0, ',', '.');
                    $duracion = htmlspecialchars($row['duracion']);
                    echo "<div class='form-group'>";
                    echo "<input type='radio' id='plan$planId' name='plan' value='$planId' required>";
                    echo "<label for='plan$planId'>Plan $nombre - \$$valor Duración: $duracion días</label>";
                    echo "</div>";
                }
            } else {
                echo "<p>No hay planes disponibles.</p>";
            }
            ?>
            <div class="form-group">
                <button type="submit">Enviar</button>
            </div>
        </form>
    </div>
</body>
</html>

<?php
// Cerrar la conexión a la base de datos
$conn->close();
?>

