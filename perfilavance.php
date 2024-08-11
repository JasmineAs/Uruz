<?php
session_start();
require_once 'conexion.php';

// Verifica si el usuario ha iniciado sesión
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

function formatDate($date) {
    return date('d/m/Y', strtotime($date));
}

// Obtener el rol del usuario
$user_role = $_SESSION['user_role'];
$user = $_SESSION['user'];
$id_miembro = $_SESSION['user']['id'];

$progreso_wod = [];
$progreso_skill = [];

// Consulta SQL para WODs
$sql_wod = "SELECT c.fecha, pw.tiempo, pw.rondas, pw.repeticiones, pw.peso_usado, e.nombre AS ejercicio 
    FROM progreso_wod pw
    JOIN clases c ON pw.id_clase = c.id
    JOIN wod_ejercicio we ON pw.id_clase = we.id_clasewod
    JOIN ejercicio e ON we.id_ejercicio = e.id
    WHERE pw.id_miembro = ?";
$stmt_wod = $conn->prepare($sql_wod);
$stmt_wod->bind_param("i", $id_miembro);
$stmt_wod->execute();
$result_wod = $stmt_wod->get_result();

if ($result_wod->num_rows > 0) {
    while($row = $result_wod->fetch_assoc()) {
        $progreso_wod[] = $row;
    }
}

// Consulta SQL para Skills
$sql_skill = "SELECT c.fecha, ps.tiempo, ps.rondas, ps.repeticiones, ps.peso_usado, e.nombre AS ejercicio 
    FROM progreso_skill ps
    JOIN clases c ON ps.id_clase = c.id
    JOIN clase_skill cs ON ps.id_clase = cs.id_clase
    JOIN ejercicio e ON cs.id_ejercicio = e.id
    WHERE ps.id_miembro = ?";
$stmt_skill = $conn->prepare($sql_skill);
$stmt_skill->bind_param("i", $id_miembro);
$stmt_skill->execute();
$result_skill = $stmt_skill->get_result();

if ($result_skill->num_rows > 0) {
    while ($row = $result_skill->fetch_assoc()) {
        $progreso_skill[] = $row;
    }
}

// Convertir los datos a JSON para ser usados en JavaScript
$progreso_wod_json = json_encode($progreso_wod);
$progreso_skill_json = json_encode($progreso_skill);

// Recupera el nombre del plan desde la tabla planes usando el id_tipoplan del usuario
$planId = $user['id_tipoplan'] ?? null;
$planName = 'Desconocido';

if ($planId) {
    try {
        $stmt = $conn->prepare("SELECT plan FROM tipoplan WHERE id = ?");
        if ($stmt === false) {
            throw new Exception("Error en la preparación de la consulta: " . $conn->error);
        }

        $stmt->bind_param("i", $planId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $plan = $result->fetch_assoc();
            $planName = htmlspecialchars($plan['plan']);
        } else {
            $planName = 'Plan no encontrado';
        }

        $stmt->close();
    } catch (Exception $e) {
        echo "<p>Error al conectar con la base de datos: " . $e->getMessage() . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Usuario</title>
    <link rel="stylesheet" href="Css/perfilavance.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="sidebar">
        <h2>Menú</h2>
        <ul>
            <?php if ($user_role === 'coach'): ?>
                <li><a href="buscar_clase.php">Clases</a></li>
                <li><a href="Crearclases.php">Crear Clase</a></li>
            <?php endif; ?>
            <?php if ($user_role === 'member'): ?>
                <li><a href="buscar_clase.php">Buscar Clase</a></li>
                <li><a href="horario.php">Mi Horario</a></li>
            <?php endif; ?>
            <li><a href="perfil.php">Editar perfil</a></li>
            <li><a href="login.php">Cerrar Sesión</a></li>
        </ul>
    </div>
    <div class="main-content">
        <div class="profile-container">
            <h1>Perfil de Usuario</h1>
            <div class="profile-group">
                <label>Nombre:</label>
                <p><?php echo htmlspecialchars($user['nombre']); ?></p>
            </div>
            <div class="profile-group">
                <label>Correo Electrónico:</label>
                <p><?php echo htmlspecialchars($user['correo_electronico']); ?></p>
            </div>
            <div class="profile-group">
                <label>Teléfono:</label>
                <p><?php echo htmlspecialchars($user['telefono']); ?></p>
            </div>
        <?php if ($user_role === 'member'): ?>
                <div class="profile-picture">
                    <img src="<?php echo htmlspecialchars($user['foto']); ?>" alt="Foto de Perfil">
                </div>
                <div class="profile-group">
                    <label>Plan:</label>
                    <p><?php echo $planName; ?></p>
                </div>
                <div class="profile-group">
                    <label>Fecha de Inicio del Plan:</label>
                    <p><?php echo formatDate($user['fecha_inicioplan']); ?></p>
                </div>
                <div class="profile-group">
                    <label>Fecha de Término del Plan:</label>
                    <p><?php echo formatDate($user['fecha_terminoplan']); ?></p>
                </div>
            </div>
            <div class="profile-container chart-container">
                <h1>Progreso en WODs</h1>
                <div class="chart-section">
                    <label for="exerciseSelectWod">Selecciona Ejercicio:</label>
                    <select id="exerciseSelectWod">
                        <option value="">Todos los Ejercicios</option>
                        <?php foreach ($progreso_wod as $wod): ?>
                            <option value="<?php echo htmlspecialchars($wod['ejercicio']); ?>"><?php echo htmlspecialchars($wod['ejercicio']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label for="wodChartType">Selecciona tipo de gráfico:</label>
                    <select id="wodChartType">
                        <option value="rondas-tiempo">Rondas-Tiempo</option>
                        <option value="repeticiones-tiempo">Repeticiones-Tiempo</option>
                        <option value="ejercicio-repeticiones">Ejercicio-Repeticiones</option>
                        <option value="ejercicio-rondas">Ejercicio-Rondas</option>
                        <option value="ejercicio-tiempo">Ejercicio-Tiempo</option>
                        <option value="repeticiones-fecha">Repeticiones-Fecha</option>
                        <option value="rondas-fecha">Rondas-Fecha</option>
                    </select>
                    <canvas id="wodChart"></canvas>
                </div>
                <h1>Progreso en Skills</h1>
                <div class="chart-section">
                    <label for="exerciseSelectSkill">Selecciona Ejercicio:</label>
                    <select id="exerciseSelectSkill">
                        <option value="">Todos los Ejercicios</option>
                        <?php foreach ($progreso_skill as $skill): ?>
                            <option value="<?php echo htmlspecialchars($skill['ejercicio']); ?>"><?php echo htmlspecialchars($skill['ejercicio']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label for="skillChartType">Selecciona tipo de gráfico:</label>
                    <select id="skillChartType">
                        <option value="rondas-tiempo">Rondas-Tiempo</option>
                        <option value="repeticiones-tiempo">Repeticiones-Tiempo</option>
                        <option value="ejercicio-repeticiones">Ejercicio-Repeticiones</option>
                        <option value="ejercicio-rondas">Ejercicio-Rondas</option>
                        <option value="ejercicio-tiempo">Ejercicio-Tiempo</option>
                        <option value="repeticiones-fecha">Repeticiones-Fecha</option>
                        <option value="rondas-fecha">Rondas-Fecha</option>
                    </select>
                    <canvas id="skillChart"></canvas>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Obtener los datos desde PHP
        const progresoWod = <?php echo $progreso_wod_json; ?>;
        const progresoSkill = <?php echo $progreso_skill_json; ?>;

        // Función para crear gráfico
        function createChart(ctx, labels, data, label) {
            return new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: label,
                        data: data,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        // Función para filtrar datos por ejercicio seleccionado
        function filterByExercise(data, exercise) {
            return exercise ? data.filter(item => item.ejercicio === exercise) : data;
        }

        // Actualizar gráfico según la selección
        function updateChart(chart, chartType, data) {
            let labels = [], datasetData = [], label = '';

            switch (chartType) {
                case 'rondas-tiempo':
                    labels = data.map(item => item.tiempo);
                    datasetData = data.map(item => item.rondas);
                    label = 'Rondas por Tiempo';
                    break;
                case 'repeticiones-tiempo':
                    labels = data.map(item => item.tiempo);
                    datasetData = data.map(item => item.repeticiones);
                    label = 'Repeticiones por Tiempo';
                    break;
                case 'ejercicio-repeticiones':
                    labels = data.map(item => item.ejercicio);
                    datasetData = data.map(item => item.repeticiones);
                    label = 'Repeticiones por Ejercicio';
                    break;
                case 'ejercicio-rondas':
                    labels = data.map(item => item.ejercicio);
                    datasetData = data.map(item => item.rondas);
                    label = 'Rondas por Ejercicio';
                    break;
                case 'ejercicio-tiempo':
                    labels = data.map(item => item.ejercicio);
                    datasetData = data.map(item => item.tiempo);
                    label = 'Tiempo por Ejercicio';
                    break;
                case 'repeticiones-fecha':
                    labels = data.map(item => new Date(item.fecha).toLocaleDateString());
                    datasetData = data.map(item => item.repeticiones);
                    label = 'Repeticiones por Fecha';
                    break;
                case 'rondas-fecha':
                    labels = data.map(item => new Date(item.fecha).toLocaleDateString());
                    datasetData = data.map(item => item.rondas);
                    label = 'Rondas por Fecha';
                    break;
                default:
                    return;
            }

            chart.data.labels = labels;
            chart.data.datasets[0].data = datasetData;
            chart.data.datasets[0].label = label;
            chart.update();
        }

        // Inicialización de gráficos
        const wodCtx = document.getElementById('wodChart').getContext('2d');
        const skillCtx = document.getElementById('skillChart').getContext('2d');
        const wodChart = createChart(wodCtx, [], [], '');
        const skillChart = createChart(skillCtx, [], [], '');

        // Manejo de eventos para actualizar gráficos según la selección
        document.getElementById('exerciseSelectWod').addEventListener('change', function() {
            const selectedExercise = this.value;
            const filteredData = filterByExercise(progresoWod, selectedExercise);
            updateChart(wodChart, document.getElementById('wodChartType').value, filteredData);
        });

        document.getElementById('exerciseSelectSkill').addEventListener('change', function() {
            const selectedExercise = this.value;
            const filteredData = filterByExercise(progresoSkill, selectedExercise);
            updateChart(skillChart, document.getElementById('skillChartType').value, filteredData);
        });

        document.getElementById('wodChartType').addEventListener('change', function() {
            const selectedExercise = document.getElementById('exerciseSelectWod').value;
            const filteredData = filterByExercise(progresoWod, selectedExercise);
            updateChart(wodChart, this.value, filteredData);
        });

        document.getElementById('skillChartType').addEventListener('change', function() {
            const selectedExercise = document.getElementById('exerciseSelectSkill').value;
            const filteredData = filterByExercise(progresoSkill, selectedExercise);
            updateChart(skillChart, this.value, filteredData);
        });
    </script>
</body>
</html>
