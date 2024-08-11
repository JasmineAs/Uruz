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

$id_clase = $_GET['id_clase'];

// Consultar los detalles de la clase
$query_clase = $conn->prepare("SELECT c.id, c.fecha, c.hora, co.nombre AS coach, c.movimiento_articular, c.skill, c.wod 
                            FROM Clases c
                            JOIN Coach co ON c.id_coach = co.id
                            WHERE c.id = ?");
$query_clase->bind_param("i", $id_clase);
$query_clase->execute();
$result_clase = $query_clase->get_result();

if ($result_clase->num_rows == 0) {
    echo "Clase no encontrada.";
    exit();
}

$clase = $result_clase->fetch_assoc();
$result_clase->free_result(); // Libera los resultados

// Consultar los miembros que asistirán a la clase
$query_miembros = $conn->prepare("SELECT m.id, m.nombre 
                                FROM Miembros m
                                JOIN Reserva r ON m.id = r.id_miembro
                                WHERE r.id_clase = ?");
$query_miembros->bind_param("i", $id_clase);
$query_miembros->execute();
$result_miembros = $query_miembros->get_result();

// Consultar los ejercicios de la clase
$wod_ejercicios = [];
$skill_ejercicios = [];

if ($clase['wod']) {
    $query_clase_wod = $conn->prepare("SELECT cw.id AS id_clase_wod, w.tipo AS tipo_wod, we.id AS id_wod_ejercicio, e.id AS id_ejercicio, e.nombre AS ejercicio_nombre 
                                    FROM Clase_WOD cw
                                    JOIN WOD_Ejercicio we ON cw.id = we.id_Clasewod
                                    JOIN Ejercicio e ON we.id_ejercicio = e.id
                                    JOIN WODs w ON cw.tipo_wod = w.id
                                    WHERE cw.id_clase = ?");
    $query_clase_wod->bind_param("i", $id_clase);
    $query_clase_wod->execute();
    $result_clase_wod = $query_clase_wod->get_result();
    while ($row = $result_clase_wod->fetch_assoc()) {
        $wod_ejercicios[$row['id_clase_wod']][] = $row;
    }
    $query_clase_wod->free_result(); // Libera los resultados
}



if ($clase['skill']) {
    $query_clase_skill = $conn->prepare("SELECT cs.id AS id_clase_skill, e.id AS id_ejercicio, e.nombre AS ejercicio_nombre 
                                        FROM Clase_Skill cs
                                        JOIN Ejercicio e ON cs.id_ejercicio = e.id
                                        WHERE cs.id_clase = ?");
    $query_clase_skill->bind_param("i", $id_clase);
    $query_clase_skill->execute();
    $result_clase_skill = $query_clase_skill->get_result();
    while ($row = $result_clase_skill->fetch_assoc()) {
        $skill_ejercicios[$row['id_clase_skill']][] = $row;
    }
    $query_clase_skill->free_result(); // Libera los resultados
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles de la Clase</title>
    <link rel="stylesheet" href="Css/detalle_clase.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="sidebar">
    <h2>Menú</h2>
        <ul>
            <li><a href="perfil.php">Perfil</a></li>
            <?php if ($user_role === 'coach'): ?>
                <li><a href="Crearclases.php">Crear Clase</a></li>
                <li><a href="buscar_clase.php">Clases</a></li>
            <?php endif; ?>
            <?php if ($user_role === 'member'): ?>
                <li><a href="buscar_clase.php">Buscar Clase</a></li>
                <li><a href="horario.php">Mi Horario</a></li>
            <?php endif; ?>
            <li><a href="logout.php">Cerrar sesión</a></li>
        </ul>
    </div>

    <div class="content">
        <h1>Detalles de la Clase</h1>

        <h3>Miembros</h3>
        <?php if ($result_miembros->num_rows > 0): ?>
            <select onchange="mostrarFormulario(this.value)">
                <option value="">Seleccione un miembro</option>
                <?php while ($miembro = $result_miembros->fetch_assoc()): ?>
                    <option value="<?= $miembro['id'] ?>"><?= $miembro['nombre'] ?></option>
                <?php endwhile; ?>
            </select>
            <?php
            // Volver a ejecutar la consulta para obtener los datos de los miembros
            $query_miembros->execute();
            $result_miembros = $query_miembros->get_result();
            while ($miembro = $result_miembros->fetch_assoc()): 
            ?>
                <div class="miembro-form-container" id="miembro-form-<?= $miembro['id'] ?>" style="display:none;">
                    <h4><?= $miembro['nombre'] ?></h4>
                    <?php if (!empty($wod_ejercicios)): ?>
                    <?php foreach ($wod_ejercicios as $id_clase_wod => $ejercicios): ?>
                        <h2>WOD: <?= $ejercicios[0]['tipo_wod'] ?></h2>
                        <?php foreach ($ejercicios as $ejercicio): ?>
                            <div class="ejercicio-container">
                                <form class="registro-form" data-member="<?= $miembro['id'] ?>" data-exercise="<?= $ejercicio['id_ejercicio'] ?>" data-wod="<?= $id_clase_wod ?>" data-wod-ejercicio="<?= $ejercicio['id_wod_ejercicio'] ?>">
                                    <h3><?= $ejercicio['ejercicio_nombre'] ?></h3>
                                    <input type="hidden" name="id_clase" value="<?= $id_clase ?>">
                                    <input type="hidden" name="id_miembro" value="<?= $miembro['id'] ?>">
                                    <input type="hidden" name="id_ejercicio" value="<?= $ejercicio['id_ejercicio'] ?>">
                                    <input type="hidden" name="id_wod" value="<?= $id_clase_wod ?>">
                                    <input type="hidden" name="id_wod_ejercicio" value="<?= $ejercicio['id_wod_ejercicio'] ?>"> <!-- Agregado aquí -->
                                    <label for="tiempo">Tiempo (Segundos):</label>
                                    <input type="text" name="tiempo" required><br>
                                    <label for="rondas">Rondas:</label>
                                    <input type="text" name="rondas" required><br>
                                    <label for="repeticiones">Repeticiones:</label>
                                    <input type="text" name="repeticiones" required><br>
                                    <label for="peso_usado">Peso Usado:</label>
                                    <input type="text" name="peso_usado" required><br>
                                    <input type="hidden" name="tipo" value="wod">
                                    <button type="button" onclick="registrarProgreso(this.form)">Registrar Progreso</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                <?php endif; ?>


                    <?php if (!empty($skill_ejercicios)): ?>
                        <h4>Skill</h4>
                        <?php foreach ($skill_ejercicios as $id_clase_skill => $ejercicios): ?>
                            <?php foreach ($ejercicios as $ejercicio): ?>
                                <div class="ejercicio-container">
                                    <form class="registro-form" data-member="<?= $miembro['id'] ?>" data-exercise="<?= $ejercicio['id_ejercicio'] ?>">
                                        <h4><?= $ejercicio['ejercicio_nombre'] ?></h4>
                                        <input type="hidden" name="id_clase" value="<?= $id_clase ?>">
                                        <input type="hidden" name="id_miembro" value="<?= $miembro['id'] ?>">
                                        <input type="hidden" name="id_ejercicio" value="<?= $ejercicio['id_ejercicio'] ?>">
                                        <input type="hidden" name="id_clase_skill" value="<?= $ejercicio['id_clase_skill'] ?>"> <!-- Agregado aquí -->
                                        <label for="tiempo">Tiempo:</label>
                                        <input type="text" name="tiempo" required><br>
                                        <label for="rondas">Rondas:</label>
                                        <input type="text" name="rondas" required><br>
                                        <label for="repeticiones">Repeticiones:</label>
                                        <input type="text" name="repeticiones" required><br>
                                        <label for="peso_usado">Peso Usado:</label>
                                        <input type="text" name="peso_usado" required><br>
                                        <input type="hidden" name="tipo" value="skill">
                                        <button type="button" onclick="registrarProgreso(this.form)">Registrar Progreso</button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No hay miembros registrados en esta clase.</p>
        <?php endif; ?>
    </div>

    <script>
        function mostrarFormulario(id) {
            var forms = document.getElementsByClassName('miembro-form-container');
            for (var i = 0; i < forms.length; i++) {
                forms[i].style.display = 'none';
            }
            if (id) {
                document.getElementById('miembro-form-' + id).style.display = 'block';
            }
        }

        function registrarProgreso(form) {
            var formData = new FormData(form);
            var id_miembro = form.dataset.member;
            var id_ejercicio = form.dataset.exercise;
            var id_wod = form.dataset.wod;
            var tipo = form.tipo.value;
            // Imprimir datos para depuración
            for (var pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }
            fetch('registrar_progreso.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                Swal.fire({
                    icon: data.alert_type,
                    title: data.alert_message,
                });
                if (data.alert_type === 'success') {
                    form.reset();
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error al registrar el progreso',
                    text: error,
                });
            });
        }
    </script>
</body>
</html>
