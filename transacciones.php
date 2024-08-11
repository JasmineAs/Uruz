<?php
require_once 'conexion.php';

// Consulta para obtener los datos de transacciones con el nombre del miembro y del plan, ordenados por id de transacción
$sql = "
    SELECT 
        t.id AS transaccion_id,
        m.nombre AS miembro_nombre,
        t.monto AS monto,
        t.fecha AS fecha,
        p.plan AS plan_nombre
    FROM 
        transaccion t
    JOIN 
        miembros m ON t.id_miembro = m.id
    JOIN 
        tipoplan p ON t.id_tipoplan = p.id
    ORDER BY 
        t.id ASC
";

$result = $conn->query($sql);

if ($result === false) {
    die("Error en la consulta: " . $conn->error);
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transacciones</title>
    <link rel="stylesheet" href="Css/transacciones.css">
</head>
<body>
    
<div class="navbar">
    <h2>Panel de Administración</h2>
    <!-- Enlace al archivo de transacciones.php, sin clase active -->
    <a href="transacciones.php">Transacciones</a>
    <!-- Enlace al archivo administrar_coach.php -->
    <a href="cuentacoach.php">Administrar cuentas de coach</a>
    <!-- Agregamos un enlace para administrar usuarios -->
    <a href="usuarios.php">Administrar usuarios</a>
    <a href="logout.php">Cerrar sesión</a>
</div>

<h1>Lista de Transacciones</h1>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre del Miembro</th>
            <th>Monto</th>
            <th>Fecha</th>
            <th>Nombre del Plan</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Formatear la fecha en formato d/m/Y
                $fechaFormateada = date('d/m/Y', strtotime($row['fecha']));
                
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['transaccion_id']) . "</td>";
                echo "<td>" . htmlspecialchars($row['miembro_nombre']) . "</td>";
                echo "<td>$" . number_format($row['monto'], 0, ',', '.') . "</td>";
                echo "<td>" . htmlspecialchars($fechaFormateada) . "</td>";
                echo "<td>" . htmlspecialchars($row['plan_nombre']) . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No hay transacciones disponibles.</td></tr>";
        }
        ?>
    </tbody>
</table>

</body>
</html>

<?php
$conn->close();
?>
