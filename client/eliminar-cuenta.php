<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['id'])) {
    // Redirigir al login si no está logueado
    header("Location: ../index.php");
    exit();
}

require_once "../includes/db.php";

// Verificación básica de método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: perfil.php");
    exit();
}

$usuario_id = $_SESSION['id'];
$password = $_POST['confirm_password'];

// Obtener contraseña actual del usuario
$sql = "SELECT password FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Usuario no encontrado.";
    header("Location: perfil.php");
    exit();
}

$usuario = $result->fetch_assoc();

// Verificar la contraseña
if (!password_verify($password, $usuario['password'])) {
    $_SESSION['error'] = "La contraseña es incorrecta.";
    header("Location: perfil.php");
    exit();
}

// Comenzar transacción para eliminar al usuario y datos relacionados
$conn->begin_transaction();

try {
    // Eliminar notificaciones relacionadas con el usuario
    $sql = "DELETE FROM notificaciones WHERE usuario_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    
    // Eliminar pedidos relacionados con el usuario
    $sql = "DELETE FROM pedidos WHERE usuario_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    
    // Finalmente, eliminar al usuario
    $sql = "DELETE FROM usuarios WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    
    // Confirmar la transacción
    $conn->commit();
    
    // Destruir la sesión
    session_destroy();
    
    // Redirigir al login con mensaje de éxito
    session_start();
    $_SESSION['success'] = "Tu cuenta ha sido eliminada correctamente.";
    header("Location: ../index.php");
    exit();
} catch (Exception $e) {
    // Revertir cambios en caso de error
    $conn->rollback();
    
    $_SESSION['error'] = "Ha ocurrido un error al eliminar tu cuenta. Por favor, inténtalo de nuevo.";
    header("Location: perfil.php");
    exit();
}
?>