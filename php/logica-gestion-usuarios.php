<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Función para manejar errores críticos
function handleCriticalError($message, $errorDetails = null) {
    // Log del error (en producción)
    error_log("Error crítico: $message - " . ($errorDetails ?? 'Sin detalles'));
    
    $displayMessage = $_SERVER['SERVER_NAME'] == 'localhost' ? 
        "Error: $message - " . ($errorDetails ?? '') : 
        "Ha ocurrido un error. Contacte al administrador.";
    
    $_SESSION['error_message'] = $displayMessage;
    
    if (!headers_sent()) {
        header("Location: ../error.php");
        exit();
    } else {
        echo "<div class='alert alert-danger'>$displayMessage</div>";
        exit();
    }
}

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
    $_SESSION['error_message'] = "Acceso restringido. Debe iniciar sesión como administrador.";
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header("Location: ../login.php");
    exit();
}

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 900)) {
    session_unset();
    session_destroy();
    $_SESSION['error_message'] = "Su sesión ha expirado por inactividad.";
    header("Location: ../login.php");
    exit();
}
$_SESSION['last_activity'] = time();

try {
    require_once '../includes/db.php';
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        throw new Exception("Error de conexión a la base de datos: " . $conn->connect_error);
    }
} catch (Exception $e) {
    handleCriticalError("Error de conexión", $e->getMessage());
}

// Array para mensajes
$messages = [];
$errors = [];

// Función para validar entrada
function validateInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Función para validar email
function validateEmail($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    return true;
}

// Función para validar que el email no exista (excepto para ediciones)
function emailExists($conn, $email, $exclude_id = null) {
    try {
        $sql = "SELECT id FROM usuarios WHERE email = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta: " . $conn->error);
        }
        
        $stmt->bind_param("s", $email);
        if (!$stmt->execute()) {
            throw new Exception("Error en la ejecución de la consulta: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            if ($exclude_id) {
                $row = $result->fetch_assoc();
                if ($row['id'] != $exclude_id) {
                    return true; // Email existe en otro usuario
                }
                return false; // Email pertenece al usuario actual
            }
            return true; // Email existe
        }
        return false; // Email no existe
    } catch (Exception $e) {
        handleCriticalError("Error al verificar email", $e->getMessage());
    }
}

// Función para verificar si un usuario tiene dependencias (notificaciones, libros, etc.)
function userHasDependencies($conn, $user_id) {
    try {
        // Verificar en la tabla notificaciones
        $sql = "SELECT COUNT(*) as count FROM notificaciones WHERE usuario_id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta: " . $conn->error);
        }
        
        $stmt->bind_param("i", $user_id);
        if (!$stmt->execute()) {
            throw new Exception("Error en la ejecución de la consulta: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['count'] > 0) {
            return true; // Tiene notificaciones
        }
        
        return false; // No tiene dependencias
    } catch (Exception $e) {
        handleCriticalError("Error al verificar dependencias", $e->getMessage());
    }
}

// Manejo de las acciones con manejo de errores
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // AGREGAR USUARIO
        if (isset($_POST['add_user'])) {
            // Validación de campos
            if (empty($_POST['nombre']) || empty($_POST['email']) || empty($_POST['password']) || empty($_POST['rol'])) {
                throw new Exception("Todos los campos son obligatorios.");
            }
            
            $nombre = validateInput($_POST['nombre']);
            $email = validateInput($_POST['email']);
            $password = $_POST['password'];
            $rol = validateInput($_POST['rol']);
            
            // Validar email
            if (!validateEmail($email)) {
                throw new Exception("El formato del correo electrónico no es válido.");
            }
            
            // Verificar que el email no exista ANTES de intentar la inserción
            if (emailExists($conn, $email)) {
                throw new Exception("El correo electrónico '$email' ya está registrado en el sistema.");
            }
            
            // Validar contraseña (mínimo 8 caracteres, al menos una letra y un número)
            if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/', $password)) {
                throw new Exception("La contraseña debe tener al menos 8 caracteres, una letra y un número.");
            }
            
            // Validar rol
            if ($rol !== 'admin' && $rol !== 'usuario') {
                throw new Exception("Rol no válido.");
            }
            
            // Cifrar contraseña
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Preparar consulta
            $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception("Error en la preparación de la consulta: " . $conn->error);
            }
            
            $stmt->bind_param("ssss", $nombre, $email, $hashed_password, $rol);
            
            try {
                if ($stmt->execute()) {
                    $messages[] = "Usuario '$nombre' agregado correctamente.";
                } else {
                    throw new Exception("Error al agregar usuario: " . $stmt->error);
                }
            } catch (mysqli_sql_exception $e) {
                // Capturar específicamente errores de duplicado
                if ($e->getCode() == 1062) { // Error de duplicado de MySQL
                    throw new Exception("El correo electrónico '$email' ya está registrado en el sistema.");
                } else {
                    throw new Exception("Error al agregar usuario: " . $e->getMessage());
                }
            }
            
            $stmt->close();
        }
        
        // ELIMINAR USUARIO
        if (isset($_POST['delete_user'])) {
            if (!isset($_POST['user_id']) || !is_numeric($_POST['user_id'])) {
                throw new Exception("ID de usuario no válido.");
            }
            
            $user_id = (int)$_POST['user_id'];
            
            // Evitar auto-eliminación
            if ($user_id == $_SESSION['id']) {
                throw new Exception("No puede eliminar su propia cuenta.");
            }
            
            // Verificar dependencias ANTES de intentar eliminar
            if (userHasDependencies($conn, $user_id)) {
                throw new Exception("No se puede eliminar este usuario porque tiene notificaciones u otros registros asociados.");
            }
            
            // Ahora sí proceder con la eliminación
            try {
                // Iniciar transacción para operaciones más complejas
                $conn->begin_transaction();
                
                // Opción 2: Marcar usuario como inactivo en lugar de eliminar
                $stmt = $conn->prepare("UPDATE usuarios SET activo = 0, deleted_at = NOW() WHERE id = ?");
                $stmt->bind_param("i", $user_id);
                
                if ($stmt->execute()) {
                    $conn->commit();
                    $messages[] = "Usuario desactivado correctamente. Sus datos no se eliminaron por motivos de integridad referencial.";
                } else {
                    throw new Exception("Error al desactivar usuario: " . $stmt->error);
                }
                
                $stmt->close();
                
            } catch (mysqli_sql_exception $e) {
                $conn->rollback();
                
                // Capturar específicamente errores de restricción de clave foránea
                if ($e->getCode() == 1451) { // Error de restricción de clave foránea de MySQL
                    throw new Exception("No se puede eliminar este usuario porque tiene registros relacionados (notificaciones, préstamos, etc.).");
                } else {
                    throw new Exception("Error al eliminar usuario: " . $e->getMessage());
                }
            }
        }
        
        // EDITAR USUARIO
        if (isset($_POST['edit_user'])) {
            // Validación de campos
            if (empty($_POST['user_id']) || empty($_POST['nombre']) || empty($_POST['email']) || empty($_POST['rol'])) {
                throw new Exception("Los campos ID, nombre, email y rol son obligatorios.");
            }
            
            $user_id = (int)$_POST['user_id'];
            $nombre = validateInput($_POST['nombre']);
            $email = validateInput($_POST['email']);
            $rol = validateInput($_POST['rol']);
            
            // Validar email
            if (!validateEmail($email)) {
                throw new Exception("El formato del correo electrónico no es válido.");
            }
            
            // Verificar que el email no exista (exceptuando este usuario)
            if (emailExists($conn, $email, $user_id)) {
                throw new Exception("El correo electrónico '$email' ya está registrado por otro usuario.");
            }
            
            try {
                // Si se va a actualizar la contraseña
                if (!empty($_POST['password'])) {
                    $password = $_POST['password'];
                    
                    // Validar contraseña
                    if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/', $password)) {
                        throw new Exception("La contraseña debe tener al menos 8 caracteres, una letra y un número.");
                    }
                    
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    $stmt = $conn->prepare("UPDATE usuarios SET nombre = ?, email = ?, password = ?, rol = ? WHERE id = ?");
                    if (!$stmt) {
                        throw new Exception("Error en la preparación de la consulta: " . $conn->error);
                    }
                    
                    $stmt->bind_param("ssssi", $nombre, $email, $hashed_password, $rol, $user_id);
                } else {
                    // Actualizar sin cambiar la contraseña
                    $stmt = $conn->prepare("UPDATE usuarios SET nombre = ?, email = ?, rol = ? WHERE id = ?");
                    if (!$stmt) {
                        throw new Exception("Error en la preparación de la consulta: " . $conn->error);
                    }
                    
                    $stmt->bind_param("sssi", $nombre, $email, $rol, $user_id);
                }
                
                if ($stmt->execute()) {
                    $messages[] = "Usuario '$nombre' actualizado correctamente.";
                } else {
                    throw new Exception("Error al actualizar usuario: " . $stmt->error);
                }
                
                $stmt->close();
            } catch (mysqli_sql_exception $e) {
                // Capturar específicamente errores de duplicado
                if ($e->getCode() == 1062) { // Error de duplicado de MySQL
                    throw new Exception("El correo electrónico '$email' ya está registrado por otro usuario.");
                } else {
                    throw new Exception("Error al actualizar usuario: " . $e->getMessage());
                }
            }
        }
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }
}

// Consulta para obtener todos los usuarios con manejo de errores
try {
    // Modificamos la consulta para mostrar solo usuarios activos por defecto
    $stmt = $conn->prepare("SELECT id, nombre, email, rol, created_at, activo FROM usuarios WHERE activo = 1 OR activo IS NULL ORDER BY created_at DESC");
    if (!$stmt) {
        throw new Exception("Error en la preparación de la consulta: " . $conn->error);
    }
    
    if (!$stmt->execute()) {
        throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    $stmt->close();
} catch (Exception $e) {
    $errors[] = "Error al obtener usuarios: " . $e->getMessage();
    $result = null;
}

// Registrar esta actividad de administrador
try {
    $action = "Acceso a gestión de usuarios";
    $admin_id = $_SESSION['id'];
    $details = "El administrador ha accedido al panel de gestión de usuarios";
    $ip = $_SERVER['REMOTE_ADDR'];
    
    $log_stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
    if ($log_stmt) {
        $log_stmt->bind_param("isss", $admin_id, $action, $details, $ip);
        $log_stmt->execute();
        $log_stmt->close();
    }
} catch (Exception $e) {
    error_log("Error al registrar actividad del administrador: " . $e->getMessage());
}

// Cerrar conexión
$conn->close();
?>