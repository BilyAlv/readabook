<?php
session_start();

// Verifica si el usuario está autenticado y si es un admin
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login.php"); // Redirige al login si no es admin
    exit();
}

// Conexión a la base de datos
include_once '../includes/db.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$user_id = $_GET['id'] ?? null;
if ($user_id === null) {
    header("Location: gestionar_usuarios.php");
    exit();
}

// Obtener datos del usuario
$sql = "SELECT * FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_user'])) {
    // Editar usuario
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $rol = strtolower($_POST['rol']);  // Convertir el rol a minúsculas

    // Validación simple del rol (ajustar si tienes más roles)
    if (!in_array($rol, ['admin', 'usuario', 'editor'])) {
        $error = "Rol inválido.";
    } else {
        // Preparar y ejecutar la consulta de actualización
        $sql = "UPDATE usuarios SET nombre = ?, email = ?, rol = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $nombre, $email, $rol, $user_id);

        if ($stmt->execute()) {
            header("Location: gestionar_usuarios.php"); // Redirigir después de la actualización
            exit();
        } else {
            $error = "Error al actualizar usuario: " . $conn->error;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario - Read a Book</title>
    <link rel="stylesheet" href="../css/editar_usuarios.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar Navigation -->
        <?php include '../includes/admin_header.php'?>

    <div class="container">
        <!-- Mensajes de error -->
        <?php
        if (isset($error)) {
            echo "<p class='error'>$error</p>";
        }
        ?>

        <!-- Formulario para editar el usuario -->
        <div class="form-container">
            <h2>Editar Usuario</h2>
            <form method="POST">
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($user['nombre']); ?>" required>
                <br><br>
                <label for="email">Correo Electrónico:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                <br><br>
                <label for="rol">Rol:</label>
                <select id="rol" name="rol" required>
                    <option value="admin" <?php if ($user['rol'] == 'admin') echo 'selected'; ?>>admin</option>
                    <option value="usuario" <?php if ($user['rol'] == 'usuario') echo 'selected'; ?>>usuario</option>
                    <option value="editor" <?php if ($user['rol'] == 'editor') echo 'selected'; ?>>editor</option>
                </select>
                <br><br>
                <button type="submit" name="edit_user" class="button">Actualizar Usuario</button>
            </form>
        </div>
                <!-- Footer incluido dentro del body y dentro de admin-wrapper -->
        <?php
        include '../includes/admin_footer.php'; 
        ?>
    </div>
</body>
</html>

<?php
$conn->close();
?>
