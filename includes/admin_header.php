<?php
/**
 * Determina si el enlace actual debe marcarse como activo
 * @param string $currentPage La página que se está verificando
 * @param string $pageName El nombre de la página para comparar
 * @return string 'active' si coincide, cadena vacía si no
 */
function isActive($currentPage, $pageName) {
    return strpos($currentPage, $pageName) !== false ? 'active' : '';
}

// Obtener el nombre de la página actual
$currentPage = basename($_SERVER['PHP_SELF']);

// Obtener el ID del usuario desde la sesión
if (isset($_SESSION['usuario_id'])) {
    $usuarioId = $_SESSION['usuario_id']; 
} else {
    $usuarioId = null; 
}

// Obtener el nombre del usuario desde la base de datos
if ($usuarioId) {
    $stmt = $pdo->prepare("SELECT nombre FROM usuarios WHERE id = :id");
    $stmt->execute(['id' => $usuarioId]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    $nombreUsuario = $usuario ? $usuario['nombre'] : 'Administrador';
} else {
    $nombreUsuario = 'Administrador'; 
}

// Obtener el número de notificaciones pendientes para el usuario
if ($usuarioId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notificaciones WHERE usuario_id = :usuario_id AND estado = 'pendiente'");
    $stmt->execute(['usuario_id' => $usuarioId]);
    $totalNotificaciones = $stmt->fetchColumn();
} else {
    $totalNotificaciones = 0; // Si no hay usuario autenticado
}
?>

<!-- Para Pages/ -->
<link rel="stylesheet" href="../css/admin_header.css">
<link rel="stylesheet" href="../css/admin_footer.css">

<!-- Header -->
<header id="catalogo_header">
    <!-- Sidebar Navigation -->
    <nav id="admin_sidebar">
        <div class="admin-logo">
            <h1>Read a Book</h1>
            <p>Panel de Administración</p>
        </div>
        
        <div class="admin-profile">
            <div class="profile-image">
                <i class="fa-solid fa-user-shield"></i>
            </div>
            <div class="profile-info">
                <h3><?php echo isset($_SESSION['nombre']) ? $_SESSION['nombre'] : 'Administrador'; ?></h3>
                <span>Administrador</span>
            </div>
        </div>
        
        <ul class="admin-menu">
            <li class="<?php echo isActive($currentPage, 'index.php'); ?>">
                <a href="index.php">
                    <i class="fa-solid fa-gauge-high"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="<?php echo isActive($currentPage, 'gestionar_usuarios.php'); ?>">
                <a href="gestionar_usuarios.php">
                    <i class="fa-solid fa-users"></i>
                    <span>Gestionar Usuarios</span>
                </a>
            </li>
            <li class="<?php echo isActive($currentPage, 'gestionar_libros.php'); ?>">
                <a href="gestionar_libros.php">
                    <i class="fa-solid fa-book"></i>
                    <span>Gestionar Libros</span>
                </a>
            </li>
            <li class="<?php echo isActive($currentPage, 'reportes.php'); ?>">
                <a href="reportes.php">
                    <i class="fa-solid fa-chart-line"></i>
                    <span>Ver Reportes</span>
                </a>
            </li>
            <li class="logout-link">
                <a href="../logout.php">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Cerrar sesión</span>
                </a>
            </li>
        </ul>
    </nav>
</header>

        <!-- Main Content Area -->
        <main id="admin_content">
        <header id="admin_header">
            <div class="toggle-menu">
                <i class="fa-solid fa-bars"></i>
            </div>
            <div class="admin-search">
                <input type="text" placeholder="Buscar...">
                <button><i class="fa-solid fa-search"></i></button>
            </div>
            <div class="admin-actions">
                <div class="notification">
                    <i class="fa-solid fa-bell"></i>
                    <span class="badge"><?php echo $totalNotificaciones; ?></span>
                </div>
                <div class="admin-dropdown">
                    <div class="dropdown-toggle">
                        <i class="fa-solid fa-user"></i>
                        <span><?php echo $nombreUsuario; ?></span>
                        <i class="fa-solid fa-chevron-down" id="dropdown-arrow"></i>
                    </div>
                    <ul class="dropdown-list" id="admin-dropdown-menu">
                        <li><a href="#">Mi Perfil</a></li>
                        <li><a href="#">Configuración</a></li>
                        <li><a href="#">Cerrar Sesión</a></li>
                    </ul>
                </div>
            </div>
        </header>
