<?php 
session_start();

// Conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "admin";
$dbname = "readabook";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']); // Prevención de SQL Injection
    $password = $_POST['password']; // Contraseña ingresada
    
    // Consulta para verificar el usuario
    $sql = "SELECT * FROM usuarios WHERE email = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stored_password = $row['password']; // Contraseña en la base de datos
        
        // Compara las contraseñas (directa sin hash)
        if ($password === $stored_password) {
            // Inicio de sesión exitoso
            $_SESSION['id'] = $row['id'];
            $_SESSION['nombre'] = $row['nombre'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['rol'] = $row['rol'];
            
            // Recordar sesión si está marcada la casilla
            if(isset($_POST['remember']) && $_POST['remember'] == 'on') {
                setcookie("user_email", $email, time() + (86400 * 30), "/"); // Cookie por 30 días
            }
            
            // Redirigir según el rol del usuario
            if ($row['rol'] == 'admin') {
                header("Location: admin/index.php"); // Redirige al panel de administración si es admin
            } else {
                header("Location: cliente/index.php"); // Redirige a la página principal si es usuario común
            }
            exit();
        } else {
            $error = "Contraseña incorrecta";
        }
    } else {
        $error = "Correo electrónico no encontrado";
    }
    
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Read a Book</title>
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <div class="logo-container">
            <div class="logo">
                <div class="logo-icon">
                    <i class="fas fa-book-open"></i>
                </div>
                <h1>Read a Book</h1>
                <p>Tu portal literario</p>
            </div>
        </div>
        
        <div class="form-container">
            <h2>Iniciar sesión</h2>
            
            <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="login-form">
                <div class="input-group">
                    <label for="email">Correo electrónico</label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" required value="<?php echo isset($_COOKIE['user_email']) ? $_COOKIE['user_email'] : ''; ?>" placeholder="Ingresa tu correo electrónico">
                    </div>
                </div>
                
                <div class="input-group">
                    <label for="password">Contraseña</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" required placeholder="Ingresa tu contraseña">
                        <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                    </div>
                </div>
                
                <div class="form-options">
                    <div class="remember-me">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Recordar sesión</label>
                    </div>
                    <a href="reset-password.php" class="forgot-password">¿Olvidaste tu contraseña?</a>
                </div>
                
                <button type="submit">Iniciar sesión</button>
                
                <div class="social-login">
                    <p>O inicia sesión con</p>
                    <div class="social-icons">
                        <a href="#" class="social-button facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-button google"><i class="fab fa-google"></i></a>
                        <a href="#" class="social-button twitter"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
            </form>
            
            <div class="register-link">
                ¿No tienes una cuenta? <a href="register.php">Regístrate aquí</a>
            </div>
        </div>
    </div>
    
    <script>
    document.getElementById('togglePassword').addEventListener('click', function() {
        const passwordInput = document.getElementById('password');
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            this.classList.remove('fa-eye');
            this.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            this.classList.remove('fa-eye-slash');
            this.classList.add('fa-eye');
        }
    });
    </script>
</body>
</html>
