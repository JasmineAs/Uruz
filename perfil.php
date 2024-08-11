<?php
session_start();

// Verifica si el usuario ha iniciado sesión
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

// Recupera los datos del usuario desde la sesión
$user = $_SESSION['user'];
$user_role = $_SESSION['user_role'];

// Función para formatear fechas en formato día/mes/año
function formatDate($date) {
    return date('d/m/Y', strtotime($date));
}

// Conexión a la base de datos
require_once 'conexion.php';

// Recupera el nombre del plan desde la tabla planes usando el id_tipoplan del usuario
$planId = $user['id_tipoplan'] ?? null;
$planName = 'Desconocido'; // Valor por defecto en caso de que no se encuentre el plan

if ($planId) {
    try {
        // Prepara la consulta para obtener el nombre del plan usando el id_tipoplan
        $stmt = $conn->prepare("SELECT plan FROM tipoplan WHERE id = ?");
        if ($stmt === false) {
            throw new Exception("Error en la preparación de la consulta: " . $conn->error);
        }

        // Asocia el id_tipoplan a la consulta
        $stmt->bind_param("i", $planId);
        $stmt->execute();
        $result = $stmt->get_result();

        // Si se encuentra el plan, almacena su nombre en la variable $planName
        if ($result->num_rows > 0) {
            $plan = $result->fetch_assoc();
            $planName = htmlspecialchars($plan['plan']);
        } else {
            $planName = 'Plan no encontrado'; // Mensaje por defecto si el plan no se encuentra
        }

        $stmt->close(); // Cierra la declaración
    } catch (Exception $e) {
        // Muestra un mensaje de error en caso de excepciones durante la conexión con la base de datos
        echo "<p>Error al conectar con la base de datos: " . $e->getMessage() . "</p>";
    }
}

// Cierra la conexión con la base de datos
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Usuario</title>
    <link rel="stylesheet" href="Css/perfil.css">
</head>
<body>
    <div class="sidebar">
    <h2>Menú</h2>
        <ul>
            <li><a href="perfilavance.php">Perfil</a></li>
            <?php if ($user_role === 'coach'): ?>
                <li><a href="buscar_clase.php">Clases</a></li>
                <li><a href="Crearclases.php">Crear Clase</a></li>
            <?php endif; ?>
            <?php if ($user_role === 'member'): ?>
                <li><a href="buscar_clase.php">Buscar Clase</a></li>
                <li><a href="horario.php">Mi Horario</a></li>
            <?php endif; ?>
                <li><a href="login.php">Cerrar Sesión</a></li>
        </ul>
    </div>
    <div class="profile-container">
        <div class="profile-picture">
            <img src="<?php echo htmlspecialchars($user['foto']); ?>" alt="Foto de perfil">
        </div>
        <div class="profile-header">
            <h1>Perfil de Usuario</h1>
        </div>
        <form id="profileForm" method="post" enctype="multipart/form-data">
            <div class="profile-group">
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($user['nombre']); ?>" readonly>
                <span class="edit-icon" onclick="enableEdit('nombre')">&#9998;</span>
            </div>
            <div class="profile-group">
                <label for="correo">Correo Electrónico:</label>
                <input type="email" id="correo" name="correo" value="<?php echo htmlspecialchars($user['correo_electronico']); ?>" readonly>
                <span class="edit-icon" onclick="enableEdit('correo')">&#9998;</span>
            </div>
            <div class="profile-group">
                <label for="telefono">Teléfono:</label>
                <input type="tel" id="telefono" name="telefono" value="<?php echo htmlspecialchars($user['telefono']); ?>" readonly>
                <span class="edit-icon" onclick="enableEdit('telefono')">&#9998;</span>
            </div>
            <?php if ($user_role === 'member'): ?>
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
                <div class="profile-group">
                    <label for="foto">Cambiar Foto de Perfil:</label>
                    <input type="file" id="foto" name="foto" accept="image/*">
                </div>
            <?php endif; ?>
            <button type="submit" class="submit-button">Actualizar Datos</button>
        </form>
    </div>

    <script>
        document.getElementById('profileForm').addEventListener('submit', function(event) {
            event.preventDefault(); // Evita el envío del formulario por defecto

            let formData = new FormData(this);

            fetch('actualizar_perfil.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message); // Muestra mensaje de éxito
                     // Actualiza la interfaz si es necesario
                        document.getElementById('nombre').value = formData.get('nombre');
                        document.getElementById('correo').value = formData.get('correo');
                        document.getElementById('telefono').value = formData.get('telefono');
                        
                        // Actualiza la imagen de perfil si se ha subido una nueva
                        const fileInput = document.getElementById('foto');
                        if (fileInput.files.length > 0) {
                            const file = fileInput.files[0];
                            const reader = new FileReader();
                            reader.onload = function (e) {
                                document.querySelector('.profile-picture img').src = e.target.result;
                            };
                            reader.readAsDataURL(file);
                        }
                } else {
                    alert(data.message); // Muestra mensaje de error
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Ha ocurrido un error al actualizar el perfil.');
            });
        });

        function enableEdit(fieldId) {
            document.getElementById(fieldId).removeAttribute('readonly');
        }
    </script>
</body>
</html>









