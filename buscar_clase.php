<?php
session_start();
require_once 'conexion.php';

// Verifica si el usuario ha iniciado sesión
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

// Obtener el rol del usuario
$user_role = $_SESSION['user_role'];

// Consultar coaches
$coach_query = "SELECT id, nombre FROM Coach";
$result_coach = $conn->query($coach_query);
$coaches = array();
if ($result_coach->num_rows > 0) {
    while ($row = $result_coach->fetch_assoc()) {
        $coaches[] = $row;
    }
}

// Inicializar variables de alerta
$alert_message = '';
$alert_type = '';

// Procesar la reserva de clase (Solo para miembros)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reservar']) && $user_role === 'member') {
    $id_miembro = $_SESSION['user']['id']; // Obtener el id del miembro desde la sesión
    $id_clase = $_POST['id_clase'];
    $fecha_reserva = date('Y-m-d');

    $query = "INSERT INTO Reserva (id_miembro, id_clase, fecha_reserva) VALUES ('$id_miembro', '$id_clase', '$fecha_reserva')";
    if ($conn->query($query) === TRUE) {
        $alert_message = 'Reserva realizada con éxito';
        $alert_type = 'success';
    } else {
        $alert_message = 'Error: ' . $query . "<br>" . $conn->error;
        $alert_type = 'error';
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $user_role === 'coach') {
    $id_clase = $_POST['id_clase'];

    // Validar id_clase
    if (!is_numeric($id_clase)) {
        $alert_message = 'ID de clase no válido.';
        $alert_type = 'error';
        exit;
    }

    if ($_POST['action'] === 'Cancelar') {
        // Usar sentencias preparadas para prevenir inyección SQL
        $query = $conn->prepare("UPDATE Clases SET estado = 1 WHERE id = ?");
        $query->bind_param("i", $id_clase);

        if ($query->execute()) {
            $alert_message = 'La clase se ha cancelado con éxito.';
            $alert_type = 'success';
        } else {
            $alert_message = 'Error: ' . $query->error;
            $alert_type = 'error';
        }
    } elseif ($_POST['action'] === 'Ir a clase') {
        header('Location: detalle_clase.php?id_clase=' . $id_clase);
        exit();
    }
}

// Buscar clases
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['buscar'])) {
    $fecha = $_POST['fecha'];
    $id_coach = $_POST['id_coach'];

    // Construir la consulta
    $query = "SELECT c.id, c.fecha, c.hora, co.nombre AS coach, c.movimiento_articular, c.skill, c.wod, c.estado 
              FROM Clases c
              JOIN Coach co ON c.id_coach = co.id
              WHERE c.fecha = '$fecha'";

    if (!empty($id_coach)) {
        $query .= " AND c.id_coach = '$id_coach'";
    }

    $result = $conn->query($query);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar Clase</title>
    <link rel="stylesheet" href="Css/buscar.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="sidebar">
    <h2>Menú</h2>
        <ul>
            <li><a href="perfil.php">Perfil</a></li>
            <?php if ($user_role === 'coach'): ?>
                <li><a href="Crearclases.php">Crear Clase</a></li>
            <?php endif; ?>
            <?php if ($user_role === 'member'): ?>
                <li><a href="buscar_clase.php">Buscar Clase</a></li>
                <li><a href="horario.php">Mi Horario</a></li>
            <?php endif; ?>
            <li><a href="logout.php">Cerrar sesión</a></li>
        </ul>
    </div>

    <div class="content">
        <h1>Buscar Clase</h1>
        <div class="form-container">
            <form action="" method="post">
                <label for="fecha">Fecha:</label>
                <input type="date" id="fecha" name="fecha" required><br>

                <label for="id_coach">Coach:</label>
                <select id="id_coach" name="id_coach">
                    <option value="">Seleccione un coach</option>
                    <?php foreach ($coaches as $coach): ?>
                        <option value="<?= $coach['id'] ?>"><?= $coach['nombre'] ?></option>
                    <?php endforeach; ?>
                </select><br>

                <input type="submit" name="buscar" value="Buscar">
            </form>
        </div>

        <?php
        if (isset($result) && $result->num_rows > 0) {
            $clases_disponibles = false;
            echo '<form action="" method="post">';
            while ($row = $result->fetch_assoc()) {
                echo '<div class="class-container">';
                if ($row['estado'] == 1) {
                    echo "<p><strong>Cancelada</strong></p>";
                } else {
                    echo '<input type="radio" name="id_clase" value="' . $row['id'] . '" required> ';
                    $clases_disponibles = true;
                }
                echo "<h2>Clase</h2>";
                echo "Fecha: " . $row['fecha'] . "<br>";
                echo "Hora: " . $row['hora'] . "<br>";
                echo "Coach: " . $row['coach'] . "<br>";

                // Mostrar ejercicios de Movimiento Articular
                if ($row['movimiento_articular']) {
                    $mov_query = "SELECT e.nombre, cma.duracion_mov 
                                  FROM Clase_Movimiento_Articular cma
                                  JOIN Ejercicio e ON cma.id_ejercicio = e.id
                                  WHERE cma.id_clase = " . $row['id'];
                    $mov_result = $conn->query($mov_query);

                    if ($mov_result->num_rows > 0) {
                        echo "<h3>Movimiento Articular</h3>";
                        while ($mov_row = $mov_result->fetch_assoc()) {
                            echo "Ejercicio: " . $mov_row['nombre'] . ", Duración: " . $mov_row['duracion_mov'] . "<br>";
                        }
                    }
                }

                // Mostrar ejercicios de Skill
                if ($row['skill']) {
                    $skill_query = "SELECT e.nombre, cs.duracion_skill 
                                    FROM Clase_Skill cs
                                    JOIN Ejercicio e ON cs.id_ejercicio = e.id
                                    WHERE cs.id_clase = " . $row['id'];
                    $skill_result = $conn->query($skill_query);

                    if ($skill_result->num_rows > 0) {
                        echo "<h3>Skill</h3>";
                        while ($skill_row = $skill_result->fetch_assoc()) {
                            echo "Ejercicio: " . $skill_row['nombre'] . ", Duración: " . $skill_row['duracion_skill'] . "<br>";
                        }
                    }
                }

                // Mostrar WODs y sus ejercicios
                if ($row['wod']) {
                    $wod_query = "SELECT cw.id, w.tipo, cw.duracion_wod 
                                  FROM Clase_WOD cw
                                  JOIN wods w ON cw.tipo_wod = w.id
                                  WHERE cw.id_clase = " . $row['id'];
                    $wod_result = $conn->query($wod_query);

                    if ($wod_result->num_rows > 0) {
                        echo "<h3>WOD</h3>";
                        while ($wod_row = $wod_result->fetch_assoc()) {
                            echo "<h4> Tipo WOD: " . $wod_row['tipo'] . ", Duración: " . $wod_row['duracion_wod'] . "<h4>";

                            // Mostrar ejercicios del WOD
                            $wod_ex_query = "SELECT e.nombre, we.repeticiones 
                                             FROM wod_ejercicio we
                                             JOIN Ejercicio e ON we.id_ejercicio = e.id
                                             WHERE we.id_Clasewod = " . $wod_row['id'];
                            $wod_ex_result = $conn->query($wod_ex_query);

                            if ($wod_ex_result->num_rows > 0) {
                                echo "<h4>Ejercicios del WOD</h4>";
                                while ($wod_ex_row = $wod_ex_result->fetch_assoc()) {
                                    echo "Ejercicio: " . $wod_ex_row['nombre'] . ", Repeticiones: " . $wod_ex_row['repeticiones'] . "<br>";
                                }
                            }
                        }
                    }
                }
                echo '</div>';
            }
            
            if (!$clases_disponibles) {
                echo '<script>
                    Swal.fire({
                        icon: "info",
                        title: "No hay clases disponibles",
                        text: "Todas las clases están canceladas o no hay clases disponibles para la fecha y coach especificados."
                    });
                </script>';
            } else {
                // Mostrar el botón de reservar solo si el usuario es un miembro
                if ($user_role === 'member') {
                    echo '<input type="submit" name="reservar" value="Reservar">';
                }
                // Mostrar botones de editar/eliminar solo si el usuario es un coach
                elseif ($user_role === 'coach') {
                    echo '<input type="submit" name="action" value="Cancelar">';
                    echo '<input type="submit" name="action" value="Ir a clase">';
                }
            }
            echo '</form>';
        } elseif (isset($result)) {
            echo "<p>No se encontraron clases para la fecha y coach especificados.</p>";

        }

        $conn->close();
        ?>

    <?php if ($alert_message): ?>
        <script>
            Swal.fire({
                icon: '<?= $alert_type ?>',
                title: '<?= $alert_message ?>',
                text: '<?= $alert_message ?>'
            }).then(function() {
                <?php if ($alert_type === 'success'): ?>
                    window.location = 'buscar_clase.php';
                <?php endif; ?>
            });
        </script>
     <?php endif; ?>
    </div>
</body>
</html>
