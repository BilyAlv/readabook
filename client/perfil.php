<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['id'])) {
    // Redirigir al login si no está logueado
    header("Location: ../index.php");
    exit();
}

require_once "../includes/db.php";

// Obtener información del usuario
$usuario_id = $_SESSION['id'];
$sql = "SELECT * FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

// Verificar si se encontró el usuario
if ($result->num_rows > 0) {
    $usuario = $result->fetch_assoc();
} else {
    // Si no se encuentra el usuario, redirigir al login
    session_destroy();
    header("Location: ../index.php");
    exit();
}

// Procesar el formulario cuando se envía
$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar y procesar los datos del formulario
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password_actual = $_POST['password_actual'];
    $password_nueva = $_POST['password_nueva'];
    $confirmar_password = $_POST['confirmar_password'];
    
    // Validaciones básicas
    if (empty($nombre) || empty($email)) {
        $mensaje = 'Por favor, completa los campos obligatorios.';
        $tipo_mensaje = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje = 'Por favor, introduce un email válido.';
        $tipo_mensaje = 'error';
    } else {
        // Verificar si el correo ya existe (excluyendo el usuario actual)
        $check_email = "SELECT id FROM usuarios WHERE email = ? AND id != ?";
        $stmt_check = $conn->prepare($check_email);
        $stmt_check->bind_param("si", $email, $usuario_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        
        if ($result_check->num_rows > 0) {
            $mensaje = 'Este correo electrónico ya está registrado por otro usuario.';
            $tipo_mensaje = 'error';
        } else {
            // Actualizar información básica del usuario
            $sql_update = "UPDATE usuarios SET nombre = ?, email = ? WHERE id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("ssi", $nombre, $email, $usuario_id);
            
            if ($stmt_update->execute()) {
                // Cambio de contraseña (opcional)
                if (!empty($password_actual) && !empty($password_nueva) && !empty($confirmar_password)) {
                    // Verificar la contraseña actual
                    if (password_verify($password_actual, $usuario['password'])) {
                        // Verificar que las contraseñas nuevas coincidan
                        if ($password_nueva === $confirmar_password) {
                            // Actualizar la contraseña
                            $password_hash = password_hash($password_nueva, PASSWORD_DEFAULT);
                            $sql_update_pwd = "UPDATE usuarios SET password = ? WHERE id = ?";
                            $stmt_update_pwd = $conn->prepare($sql_update_pwd);
                            $stmt_update_pwd->bind_param("si", $password_hash, $usuario_id);
                            
                            if ($stmt_update_pwd->execute()) {
                                $mensaje = 'Tu perfil ha sido actualizado correctamente, incluyendo la contraseña.';
                                $tipo_mensaje = 'success';
                            } else {
                                $mensaje = 'Tu información se actualizó pero hubo un error al cambiar la contraseña.';
                                $tipo_mensaje = 'warning';
                            }
                        } else {
                            $mensaje = 'Las contraseñas nuevas no coinciden.';
                            $tipo_mensaje = 'error';
                        }
                    } else {
                        $mensaje = 'La contraseña actual es incorrecta.';
                        $tipo_mensaje = 'error';
                    }
                } else {
                    $mensaje = 'Tu perfil ha sido actualizado correctamente.';
                    $tipo_mensaje = 'success';
                }
                
                // Actualizar la información del usuario en la sesión
                $sql = "SELECT * FROM usuarios WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $usuario_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $usuario = $result->fetch_assoc();
            } else {
                $mensaje = 'Hubo un error al actualizar tu perfil.';
                $tipo_mensaje = 'error';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Read A Book - Mi Perfil</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/cliente.css">
</head>
<body>
    <header>
        <div class="logo">
            <a href="index.php">
                <h1>Read A Book</h1>
            </a>
        </div>
        <nav>
            <ul>
                <li><a href="index.php">Catálogo</a></li>
                <li><a href="mis_pedidos.php">Mis Pedidos</a></li>
                <li><a href="perfil.php" class="active">Mi Perfil</a></li>
                <li>
                    <a href="carrito.php" class="cart-icon">
                        <i class="fas fa-shopping-cart"></i>
                        <span id="cart-count">
                            <?php
                            if (isset($_SESSION['carrito']) && is_array($_SESSION['carrito'])) {
                                echo count($_SESSION['carrito']);
                            } else {
                                echo "0";
                            }
                            ?>
                        </span>
                    </a>
                </li>
                <li><a href="../logout.php">Cerrar Sesión</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="container profile-container">
            <div class="sidebar">
                <div class="welcome-section">
                    <h3>Bienvenido, <?php echo isset($usuario['nombre']) ? htmlspecialchars($usuario['nombre']) : 'Usuario'; ?></h3>
                </div>
                
                <div class="user-stats">
                    <h3>Tu actividad</h3>
                    <ul>
                        <li>
                            <i class="fas fa-shopping-bag"></i>
                            <span>Pedidos realizados:</span>
                            <?php
                            // Obtener el número de pedidos del usuario
                            $sql_pedidos = "SELECT COUNT(*) as total FROM pedidos WHERE usuario_id = ?";
                            $stmt_pedidos = $conn->prepare($sql_pedidos);
                            $stmt_pedidos->bind_param("i", $usuario_id);
                            $stmt_pedidos->execute();
                            $result_pedidos = $stmt_pedidos->get_result();
                            $pedidos = $result_pedidos->fetch_assoc()['total'];
                            echo $pedidos;
                            ?>
                        </li>
                        <li>
                            <i class="fas fa-bell"></i>
                            <span>Notificaciones:</span>
                            <?php
                            // Obtener el número de notificaciones del usuario
                            $sql_notificaciones = "SELECT COUNT(*) as total FROM notificaciones WHERE usuario_id = ? AND estado = 'pendiente'";
                            $stmt_notificaciones = $conn->prepare($sql_notificaciones);
                            $stmt_notificaciones->bind_param("i", $usuario_id);
                            $stmt_notificaciones->execute();
                            $result_notificaciones = $stmt_notificaciones->get_result();
                            $notificaciones = $result_notificaciones->fetch_assoc()['total'];
                            echo $notificaciones;
                            ?>
                        </li>
                        <li>
                            <i class="fas fa-calendar-alt"></i>
                            <span>Miembro desde:</span>
                            <?php 
                            $fecha_registro = new DateTime($usuario['fecha_registro']);
                            echo $fecha_registro->format('d/m/Y');
                            ?>
                        </li>
                    </ul>
                </div>
                
                <div class="quick-links">
                    <h3>Enlaces rápidos</h3>
                    <ul>
                        <li><a href="mis_pedidos.php"><i class="fas fa-shopping-bag"></i> Ver mis pedidos</a></li>
                        <li><a href="carrito.php"><i class="fas fa-shopping-cart"></i> Ver mi carrito</a></li>
                        <li><a href="" id="btnCambiarPassword"><i class="fas fa-key"></i> Cambiar contraseña</a></li>
                        <li><a href="eliminar-cuenta.php" id="btnEliminarCuenta"><i class="fas fa-user-times"></i> Eliminar mi cuenta</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="content">
                <h2>Mi Perfil</h2>
                
                <?php if (!empty($mensaje)): ?>
                <div class="alert alert-<?php echo $tipo_mensaje; ?>">
                    <?php echo $mensaje; ?>
                </div>
                <?php endif; ?>
                
                <div class="profile-card">
                    <div class="profile-header">
                        <div class="profile-avatar">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <div class="profile-title">
                            <h3><?php echo htmlspecialchars($usuario['nombre']); ?></h3>
                            <p><?php echo htmlspecialchars($usuario['email']); ?></p>
                            <span class="user-role"><?php echo ucfirst($usuario['rol'] ?? 'usuario'); ?></span>
                        </div>
                    </div>
                    
                    <div class="profile-form">
                        <form action="perfil.php" method="POST" id="profileForm">
                            <div class="form-section">
                                <h4>Información personal</h4>
                                
                                <div class="form-group">
                                    <label for="nombre">Nombre completo *</label>
                                    <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email">Correo electrónico *</label>
                                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-section password-section" id="passwordSection">
                                <h4>Cambiar contraseña</h4>
                                <p class="section-info">Deja estos campos en blanco si no deseas cambiar tu contraseña</p>
                                
                                <div class="form-group">
                                    <label for="password_actual">Contraseña actual</label>
                                    <input type="password" id="password_actual" name="password_actual">
                                </div>
                                
                                <div class="form-group">
                                    <label for="password_nueva">Nueva contraseña</label>
                                    <input type="password" id="password_nueva" name="password_nueva">
                                </div>
                                
                                <div class="form-group">
                                    <label for="confirmar_password">Confirmar nueva contraseña</label>
                                    <input type="password" id="confirmar_password" name="confirmar_password">
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn-save">Guardar cambios</button>
                                <button type="reset" class="btn-cancel">Cancelar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Modal para confirmar eliminación de cuenta -->
    <div id="eliminarCuentaModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Eliminar cuenta</h2>
            <p>¿Estás seguro de que quieres eliminar tu cuenta? Esta acción no se puede deshacer y perderás todos tus datos y pedidos.</p>
            <form action="eliminar_cuenta.php" method="POST" id="deleteAccountForm">
                <div class="form-group">
                    <label for="confirm_password">Introduce tu contraseña para confirmar:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-danger">Eliminar mi cuenta</button>
                    <button type="button" class="btn-cancel" id="btnCancelDelete">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>Read A Book</h3>
                <p>Tu librería online de confianza</p>
            </div>
            <div class="footer-section">
                <h3>Enlaces Rápidos</h3>
                <ul>
                    <li><a href="index.php">Catálogo</a></li>
                    <li><a href="mis_pedidos.php">Mis Pedidos</a></li>
                    <li><a href="perfil.php">Mi Perfil</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Contacto</h3>
                <p>Email: contacto@readabook.com</p>
                <p>Teléfono: (123) 456-7890</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Read A Book. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Modal para eliminar cuenta
        var eliminarCuentaModal = document.getElementById('eliminarCuentaModal');
        var btnEliminarCuenta = document.getElementById('btnEliminarCuenta');
        var btnCancelDelete = document.getElementById('btnCancelDelete');
        var closes = document.getElementsByClassName('close');
        
        btnEliminarCuenta.addEventListener('click', function(e) {
            e.preventDefault();
            eliminarCuentaModal.style.display = 'block';
        });
        
        btnCancelDelete.addEventListener('click', function() {
            eliminarCuentaModal.style.display = 'none';
        });
        
        // Cerrar modales con la X
        for (var i = 0; i < closes.length; i++) {
            closes[i].addEventListener('click', function() {
                eliminarCuentaModal.style.display = 'none';
            });
        }
        
        // Cerrar modales cuando se hace clic fuera de ellos
        window.addEventListener('click', function(event) {
            if (event.target == eliminarCuentaModal) {
                eliminarCuentaModal.style.display = 'none';
            }
        });
        
        // Toggle para la sección de contraseña
        var btnCambiarPassword = document.getElementById('btnCambiarPassword');
        var passwordSection = document.getElementById('passwordSection');
        
        btnCambiarPassword.addEventListener('click', function(e) {
            e.preventDefault();
            if (passwordSection.style.display === 'none' || getComputedStyle(passwordSection).display === 'none') {
                passwordSection.style.display = 'block';
                document.getElementById('password_actual').focus();
            } else {
                passwordSection.style.display = 'none';
                // Limpiar los campos de contraseña
                document.getElementById('password_actual').value = '';
                document.getElementById('password_nueva').value = '';
                document.getElementById('confirmar_password').value = '';
            }
        });
        
        // Validación del formulario
        var profileForm = document.getElementById('profileForm');
        
        profileForm.addEventListener('submit', function(e) {
            var password_actual = document.getElementById('password_actual').value;
            var password_nueva = document.getElementById('password_nueva').value;
            var confirmar_password = document.getElementById('confirmar_password').value;
            
            // Validar que si se ingresa una contraseña, todos los campos de contraseña estén completos
            if ((password_actual || password_nueva || confirmar_password) && 
                !(password_actual && password_nueva && confirmar_password)) {
                e.preventDefault();
                alert('Por favor, completa todos los campos de contraseña o déjalos todos en blanco.');
                return false;
            }
            
            // Validar que las contraseñas nuevas coincidan
            if (password_nueva && password_nueva !== confirmar_password) {
                e.preventDefault();
                alert('Las contraseñas nuevas no coinciden.');
                return false;
            }
            
            return true;
        });
    });
    </script>
</body>
</html>