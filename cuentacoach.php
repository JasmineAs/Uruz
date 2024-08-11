<?php
require_once 'conexion.php';

// Consulta para obtener los datos de los coaches
$sql = "
    SELECT 
        c.id,
        c.id_usuario,
        c.nombre,
        c.correo_electronico,
        c.telefono
    FROM 
        coach c
";

$result = $conn->query($sql);

if ($result === false) {
    die("Error en la consulta: " . $conn->error);
}

// Procesar el formulario de creación de coach
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $nombre = $_POST['nombre'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $contrasena = $_POST['contrasena'] ?? '';

    if ($nombre && $correo && $telefono && $contrasena) {
        // Encriptar la contraseña
        $hashedPassword = password_hash($contrasena, PASSWORD_DEFAULT);

        // Insertar en la tabla usuarios
        $sqlInsertUsuario = "
            INSERT INTO usuarios (correo_electronico, password, id_tipoUsuario, primer_inicio)
            VALUES (?, ?, ?, ?)
        ";
        $stmtUsuario = $conn->prepare($sqlInsertUsuario);
        if ($stmtUsuario === false) {
            die("Error en la preparación de la consulta para usuarios: " . $conn->error);
        }
        // Asumiendo que el id_tipoUsuario para coach es 2
        $idTipoUsuario = 2;
        $primer_inicio = 1;
        $stmtUsuario->bind_param("ssis", $correo, $hashedPassword, $idTipoUsuario, $primer_inicio);

        if ($stmtUsuario->execute()) {
            // Obtener el ID del usuario recién insertado
            $id_usuario = $conn->insert_id;

            // Insertar en la tabla coach
            $sqlInsertCoach = "
                INSERT INTO coach (id_usuario, nombre, correo_electronico, telefono)
                VALUES (?, ?, ?, ?)
            ";
            $stmtCoach = $conn->prepare($sqlInsertCoach);
            if ($stmtCoach === false) {
                die("Error en la preparación de la consulta para coach: " . $conn->error);
            }
            $stmtCoach->bind_param("isss", $id_usuario, $nombre, $correo, $telefono);

            if ($stmtCoach->execute()) {
                echo "<p>Nuevo coach agregado exitosamente.</p>";
                header("Location: cuentacoach.php"); // Recargar la página para ver los cambios
                exit();
            } else {
                echo "<p>Error al agregar el coach: " . $stmtCoach->error . "</p>";
            }
            $stmtCoach->close();
        } else {
            echo "<p>Error al registrar el usuario: " . $stmtUsuario->error . "</p>";
        }
        $stmtUsuario->close();
    } else {
        echo "<p>Por favor, completa todos los campos.</p>";
    }
}

// Procesar el formulario de edición de coach
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = $_POST['id'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $telefono = $_POST['telefono'] ?? '';

    if ($id && $nombre && $correo && $telefono) {
        // Actualizar en la tabla coach
        $sqlUpdateCoach = "
            UPDATE coach
            SET nombre = ?, correo = ?, telefono = ?
            WHERE id = ?
        ";
        $stmtCoach = $conn->prepare($sqlUpdateCoach);
        if ($stmtCoach === false) {
            die("Error en la preparación de la consulta para coach: " . $conn->error);
        }
        $stmtCoach->bind_param("sssi", $nombre, $correo, $telefono, $id);

        if ($stmtCoach->execute()) {
            echo "<p>Datos del coach actualizados exitosamente.</p>";
            header("Location: cuentacoach.php"); // Recargar la página para ver los cambios
            exit();
        } else {
            echo "<p>Error al actualizar los datos del coach: " . $stmtCoach->error . "</p>";
        }
        $stmtCoach->close();
    } else {
        echo "<p>Por favor, completa todos los campos.</p>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuentas de Coach</title>
    <link rel="stylesheet" href="Css/cuentacoach.css">
</head>
<body>
    
<div class="navbar">
    <h2>Panel de Administración</h2>
    <!-- Enlace al archivo de transacciones.php, sin clase active -->
    <a href="transacciones.php">Transacciones</a>
    <!-- Enlace al archivo administrar_coach.php -->
    <a href="cuentacoach.php">Administrar cuentas de coach</a>
    <a href="logout.php">Cerrar sesion</a>
</div>

<h1>Cuentas de Coach</h1>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>ID Usuario</th>
            <th>Nombre</th>
            <th>Correo</th>
            <th>Teléfono</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr onclick='editCoach(" . htmlspecialchars($row['id']) . ", \"" . htmlspecialchars($row['nombre']) . "\", \"" . htmlspecialchars($row['correo_electronico']) . "\", \"" . htmlspecialchars($row['telefono']) . "\")'>";
                echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                echo "<td>" . htmlspecialchars($row['id_usuario']) . "</td>";
                echo "<td>" . htmlspecialchars($row['nombre']) . "</td>";
                echo "<td>" . htmlspecialchars($row['correo_electronico']) . "</td>";
                echo "<td>" . htmlspecialchars($row['telefono']) . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No hay coaches disponibles.</td></tr>";
        }
        ?>
    </tbody>
</table>

<!-- Botón para abrir el formulario de agregar coach -->
<button class="slide-form-toggle" onclick="toggleAddForm()">+</button>

<!-- Formulario deslizante para agregar un nuevo coach -->
<div class="slide-form-container" id="addCoachForm">
    <h2>Agregar Nuevo Coach</h2>
    <form method="POST">
        <input type="hidden" name="action" value="add">
        <input type="text" name="nombre" placeholder="Nombre" required>
        <input type="email" name="correo" placeholder="Correo" required>
        <input type="tel" name="telefono" placeholder="Teléfono" required>
        <input type="password" name="contrasena" placeholder="Contraseña" required>
        <input type="submit" value="Agregar Coach">
    </form>
</div>

<!-- Formulario deslizante para editar un coach -->
<div class="slide-form-container" id="editCoachForm">
    <h2>Editar Coach</h2>
    <form method="POST">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" id="editCoachId" name="id">
        <input type="text" id="editCoachName" name="nombre" placeholder="Nombre" required>
        <input type="email" id="editCoachEmail" name="correo" placeholder="Correo" required>
        <input type="tel" id="editCoachPhone" name="telefono" placeholder="Teléfono" required>
        <input type="submit" value="Guardar Cambios">
    </form>
</div>

<script>
    function toggleAddForm() {
        var form = document.getElementById('addCoachForm');
        form.classList.toggle('active');
        // Close edit form if open
        document.getElementById('editCoachForm').classList.remove('active');
    }

    function toggleEditForm() {
        var form = document.getElementById('editCoachForm');
        form.classList.toggle('active');
        // Close add form if open
        document.getElementById('addCoachForm').classList.remove('active');
    }

    function editCoach(id, nombre, correo, telefono) {
        document.getElementById('editCoachId').value = id;
        document.getElementById('editCoachName').value = nombre;
        document.getElementById('editCoachEmail').value = correo;
        document.getElementById('editCoachPhone').value = telefono;
        toggleEditForm();
    }
</script>

</body>
</html>





