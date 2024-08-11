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

// Consultar tipos de WOD
$tipo_wod_query = "SELECT id, tipo FROM WODs";
$result_tipo_wod = $conn->query($tipo_wod_query);
$tipos_wod = array();
if ($result_tipo_wod->num_rows > 0) {
    while($row = $result_tipo_wod->fetch_assoc()) {
        $tipos_wod[] = $row;
    }
}

// Consultar coaches
$coach_query = "SELECT id, nombre FROM Coach";
$result_coach = $conn->query($coach_query);
$coaches = array();
if ($result_coach->num_rows > 0) {
    while($row = $result_coach->fetch_assoc()) {
        $coaches[] = $row;
    }
}

// Consultar ejercicios
$ejercicio_query = "SELECT id, nombre, id_entrenamiento FROM Ejercicio";
$result_ejercicio = $conn->query($ejercicio_query);
$ejercicios = array();
if ($result_ejercicio->num_rows > 0) {
    while($row = $result_ejercicio->fetch_assoc()) {
        $ejercicios[] = $row;
    }
}

// Filtrar ejercicios por tipo_entrenamiento
$movimiento_articular_ejercicios = array_filter($ejercicios, function($ejercicio) {
    return $ejercicio['id_entrenamiento'] == 5;
});

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Clase</title>
    <script>
        let wodIndex = 0;
        function toggleFieldset(checkbox, fieldsetId) {
            const fieldset = document.getElementById(fieldsetId);
            fieldset.style.display = checkbox.checked ? 'block' : 'none';
            if (!checkbox.checked) {
                clearFieldset(fieldsetId);
            }
        }

        function clearFieldset(fieldsetId) {
            const container = document.getElementById(fieldsetId).querySelector('div');
            container.innerHTML = '';
        }

        function addField(section, type) {
            let container = document.getElementById(section);
            let div = document.createElement("div");
            div.classList.add("field-group");

            if (type === 'WOD') {
                div.innerHTML = `
                    <label for="tipo_wod[]">Tipo WOD:</label>
                    <select name="tipo_wod[]">
                        <?php foreach($tipos_wod as $tipo): ?>
                            <option value="<?= $tipo['id'] ?>"><?= $tipo['tipo'] ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label for="minutos_tipo_wod[]">Minutos:</label>
                    <input type="number" name="minutos_tipo_wod[]" required>
                    <label for="segundos_tipo_wod[]">Segundos:</label>
                    <input type="number" name="segundos_tipo_wod[]" required>
                    <button type="button" onclick="addWODExerciseField(this, ${wodIndex})">Agregar Ejercicio</button>
                    <div class="wod-exercises" data-wod-index="${wodIndex}"></div>
                `;
                wodIndex++;
            } else if (type === 'Movimiento') {
                div.innerHTML = `
                    <label for="id_ejercicio_${type.toLowerCase()}[]">Ejercicio ${type}:</label>
                    <select name="id_ejercicio_${type.toLowerCase()}[]" required>
                        <?php foreach($movimiento_articular_ejercicios as $ejercicio): ?>
                            <option value="<?= $ejercicio['id'] ?>"><?= $ejercicio['nombre'] ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label for="minutos_id_ejercicio_${type.toLowerCase()}[]">Minutos:</label>
                    <input type="number" name="minutos_id_ejercicio_${type.toLowerCase()}[]" required>
                    <label for="segundos_id_ejercicio_${type.toLowerCase()}[]">Segundos:</label>
                    <input type="number" name="segundos_id_ejercicio_${type.toLowerCase()}[]" required>
                `;
            } else {
                div.innerHTML = `
                    <label for="id_ejercicio_${type.toLowerCase()}[]">Ejercicio ${type}:</label>
                    <select name="id_ejercicio_${type.toLowerCase()}[]" required>
                        <?php foreach($ejercicios as $ejercicio): ?>
                            <option value="<?= $ejercicio['id'] ?>"><?= $ejercicio['nombre'] ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label for="minutos_id_ejercicio_${type.toLowerCase()}[]">Minutos:</label>
                    <input type="number" name="minutos_id_ejercicio_${type.toLowerCase()}[]" required>
                    <label for="segundos_id_ejercicio_${type.toLowerCase()}[]">Segundos:</label>
                    <input type="number" name="segundos_id_ejercicio_${type.toLowerCase()}[]" required>
                `;
            }
            container.appendChild(div);
        }

        function addWODExerciseField(button, wodIndex) {
            let container = button.nextElementSibling;
            let div = document.createElement("div");
            div.classList.add("wod-exercise-group");

            div.innerHTML = `
                <label for="id_ejercicio_wod[${wodIndex}][]">Ejercicio:</label>
                <select name="id_ejercicio_wod[${wodIndex}][]" required>
                    <?php foreach($ejercicios as $ejercicio): ?>
                        <option value="<?= $ejercicio['id'] ?>"><?= $ejercicio['nombre'] ?></option>
                    <?php endforeach; ?>
                </select>
                <label for="repeticiones_wod[${wodIndex}][]">Repeticiones:</label>
                <input type="number" name="repeticiones_wod[${wodIndex}][]" required>
            `;
            container.appendChild(div);
        }
    </script>

    <link rel="stylesheet" href="Css/CC.css">
</head>
<body> 
    <div class="sidebar">
    <h2>Menú</h2>
        <ul>
            <li><a href="perfilavance.php">Perfil</a></li>
            <?php if ($user_role === 'coach'): ?>
                <li><a href="buscar_clase.php">Clase</a></li>
            <?php endif; ?>
            <?php if ($user_role === 'member'): ?>
                <li><a href="buscar_clase.php">Buscar Clase</a></li>
                <li><a href="horario.php">Mi Horario</a></li>
            <?php endif; ?>
                <li><a href="perfil.php">Editar perfil</a></li>
                <li><a href="login.php">Cerrar Sesión</a></li>
        </ul>
    </div>
    <div class="container">
        <h2>Crea una clase!</h2>
        <form action="previsualizar_clase.php" method="post">
            <div class="input-group">
                <div>
                    <label for="fecha">Fecha:</label>
                    <input type="date" id="fecha" name="fecha" required><br>
                </div>
            </div>
            <div class="checkbox-group">
                <div>
                    <label for="movimiento_articular">Movimiento Articular:</label>
                    <input type="checkbox" id="movimiento_articular" name="movimiento_articular" onclick="toggleFieldset(this, 'movimiento-container')"><br>
                </div>
                <div>
                    <label for="skill">Skill:</label>
                    <input type="checkbox" id="skill" name="skill" onclick="toggleFieldset(this, 'skill-container')"><br>
                </div>
                <div>
                    <label for="wod">WOD:</label>
                    <input type="checkbox" id="wod" name="wod" onclick="toggleFieldset(this, 'wod-container')"><br>
                </div>
            </div>
            <div class="button-group">
                <fieldset id="wod-container" class="hidden">
                    <h2>Agregar WOD</h2>
                    <div id="wod-fields"></div>
                    <button type="button" onclick="addField('wod-fields', 'WOD')">Agregar WOD</button>
                </fieldset>
                <fieldset id="movimiento-container" class="hidden">
                    <h2>Agregar Movimiento Articular</h2>
                    <div id="movimiento-fields"></div>
                    <button type="button" onclick="addField('movimiento-fields', 'Movimiento')">Agregar Movimiento Articular</button>
                </fieldset>
                <fieldset id="skill-container" class="hidden">
                    <h2>Agregar Skill</h2>
                    <div id="skill-fields"></div>
                    <button type="button" onclick="addField('skill-fields', 'Skill')">Agregar Skill</button>
                </fieldset>
                <button type="submit">Previsualizar Clase</button>
            </div>
        </form>
    </div>
</body>
</html>
