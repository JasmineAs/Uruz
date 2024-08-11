<?php
session_start();
require_once 'conexion.php';

if (isset($_SESSION['transaccion'])) {
    $transaccion = $_SESSION['transaccion'];
    unset($_SESSION['transaccion']); // Limpiar los datos de la sesión después de mostrar el comprobante
} else {
    echo "<p>No se ha encontrado información de la transacción.</p>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante de Compra</title>
</head>
<body>
    <h1>Comprobante de Compra</h1>
    <p><strong>Orden de Compra:</strong> <?php echo htmlspecialchars($transaccion['buyOrder']); ?></p>
    <p><strong>Monto:</strong> <?php echo htmlspecialchars($transaccion['amount']); ?> CLP</p>
    <p><strong>Estado:</strong> <?php echo htmlspecialchars($transaccion['status']); ?></p>
    <p><a href="index.php">Volver al inicio</a></p>
</body>
</html>
