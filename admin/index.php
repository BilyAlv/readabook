<?php
session_start();
include_once '../includes/db.php';
include_once '../php/logica-index.php';

// Verifica si el usuario está autenticado y si es un admin
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login.php"); // Redirige al login si no es admin
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Read a Book</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
</head>

<body>
    <div class="admin-wrapper">
        <!-- Navegation bar -->
        <?php include '../includes/admin_header.php'; ?>
        
        <main>
            <div class="admin-container">
                <div class="admin-welcome">
                    <h2>Bienvenido, <?php echo isset($_SESSION['nombre']) ? $_SESSION['nombre'] : 'Administrador'; ?></h2>
                    <p>Panel de control principal de la plataforma "Read a Book"</p>
                </div>

                <!-- Stats Cards -->
                <div class="stats-container">
                    <div class="stat-card">
                        <div class="stat-icon users">
                            <i class="fa-solid fa-users"></i>
                        </div>
                        <div class="stat-details">
                            <h3><?php echo $totalUsuarios; ?></h3>
                            <p>Usuarios Activos</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon books">
                            <i class="fa-solid fa-book"></i>
                        </div>
                        <div class="stat-details">
                            <h3><?php echo $totalLibros; ?></h3>
                            <p>Libros Disponibles</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon orders">
                            <i class="fa-solid fa-shopping-cart"></i>
                        </div>
                        <div class="stat-details">
                            <h3><?php echo $totalPedidos; ?></h3>
                            <p>Pedidos Recientes</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon revenue">
                            <i class="fa-solid fa-dollar-sign"></i>
                        </div>
                        <div class="stat-details">
                            <h3>$<?php echo number_format($ingresosMensuales, 2); ?></h3>
                            <p>Ingresos Mensuales</p>
                        </div>
                    </div>
                </div>

                <!-- Management Sections -->
                <div class="admin-sections">
                    <div class="admin-section-card">
                        <div class="section-header">
                            <i class="fa-solid fa-users"></i>
                            <h3>Gestión de Usuarios</h3>
                        </div>
                        <p>Administra todos los usuarios registrados en la plataforma. Añade, edita o elimina cuentas de usuario.</p>
                        <div class="quick-stats">
                            <div class="quick-stat">
                                <span><?php echo $totalUsuariosTotales; ?></span>
                                <p>Totales</p>
                            </div>
                            <div class="quick-stat">
                                <span><?php echo $totalUsuarios; ?></span>
                                <p>Activos</p>
                            </div>
                            <div class="quick-stat">
                                <span><?php echo $totalUsuariosInactivos; ?></span>
                                <p>Inactivos</p>
                            </div>
                        </div>
                        <a href="gestionar_usuarios.php" class="admin-btn">Gestionar Usuarios</a>
                    </div>

                    <div class="admin-section-card">
                        <div class="section-header">
                            <i class="fa-solid fa-book"></i>
                            <h3>Gestión de Libros</h3>
                        </div>
                        <p>Administra el catálogo de libros disponibles. Añade nuevos títulos, actualiza información o gestiona el inventario.</p>
                        <div class="quick-stats">
                            <div class="quick-stat">
                                <span><?php echo $totalLibros; ?></span>
                                <p>Totales</p>
                            </div>
                            <div class="quick-stat">
                                <span><?php echo $totalCategorias; ?></span>
                                <p>Categorías</p>
                            </div>
                            <div class="quick-stat">
                                <span><?php echo $totalLibrosAgotados; ?></span>
                                <p>Agotados</p>
                            </div>
                        </div>
                        <a href="gestionar_libros.php" class="admin-btn">Gestionar Libros</a>
                    </div>

                    <div class="admin-section-card">
                        <div class="section-header">
                            <i class="fa-solid fa-chart-line"></i>
                            <h3>Ver Reportes</h3>
                        </div>
                        <p>Accede a estadísticas detalladas y reportes sobre ventas, usuarios y actividad en la plataforma.</p>
                        <div class="quick-stats">
                            <div class="quick-stat">
                                <span><?php echo $totalPedidos; ?></span>
                                <p>Pedidos Totales</p>
                            </div>
                            <div class="quick-stat">
                                <span><?php echo $ingresosMensuales; ?></span>
                                <p>Ingresos Mensuales</p>
                            </div>
                            <div class="quick-stat">
                                <span><?php echo count($datosPorCategoria); ?></span>
                                <p>Categorías Vendidas</p>
                            </div>
                        </div>
                        <a href="reportes.php" class="admin-btn">Ver Reportes</a>
                    </div>
                </div>
            </div>
        </main>
        
        <!-- Footer incluido dentro del body y dentro de admin-wrapper -->
        <?php
        include '../includes/admin_footer.php'; 
        ?>
    </div>
    
    <script src="../js/script.js"></script>
</body>
</html>