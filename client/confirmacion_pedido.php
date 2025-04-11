<?php
session_start();
require_once "../includes/db.php";

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit();
}

// Verificar si hay un mensaje de éxito
if (!isset($_SESSION['mensaje_exito'])) {
    header("Location: index.php");
    exit();
}

// Obtener información del usuario
$usuario_id = $_SESSION['usuario_id'];
$sql = "SELECT * FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

// Obtener los pedidos más recientes del usuario
$sql = "SELECT p.id, p.fecha_pedido, p.cantidad, l.titulo, l.precio, l.imagen 
        FROM pedidos p
        JOIN libros l ON p.libro_id = l.id
        WHERE p.usuario_id = ?
        ORDER BY p.fecha_pedido DESC
        LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result_pedidos = $stmt->get_result();

// Mensaje de éxito
$mensaje = $_SESSION['mensaje_exito'];
unset($_SESSION['mensaje_exito']); // Limpiar el mensaje para que no se muestre nuevamente
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Read A Book - Confirmación de Pedido</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/cliente.css">
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
                <li><a href="mis_pedidos.php" class="active">Mis Pedidos</a></li>
                <li><a href="perfil.php">Mi Perfil</a></li>
                <li>
                    <a href="carrito.php" class="cart-icon">
                        <i class="fas fa-shopping-cart"></i>
                        <span id="cart-count">0</span>
                    </a>
                </li>
                <li><a href="../logout.php">Cerrar Sesión</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="container">
            <div class="confirmation-container">
                <div class="confirmation-header">
                    <div class="confirmation-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h2>¡Pedido Completado!</h2>
                    <p class="success-message"><?php echo $mensaje; ?></p>
                </div>
                
                <div class="order-details">
                    <h3>Resumen del Pedido</h3>
                    
                    <?php if ($result_pedidos->num_rows > 0): ?>
                        <div class="order-items">
                            <?php 
                            $total = 0;
                            while ($pedido = $result_pedidos->fetch_assoc()): 
                                $subtotal = $pedido['precio'] * $pedido['cantidad'];
                                $total += $subtotal;
                            ?>
                                <div class="order-item">
                                    <div class="order-item-image">
                                        <?php if (!empty($pedido['imagen'])): ?>
                                            <img src="../img/books/<?php echo htmlspecialchars($pedido['imagen']); ?>" alt="<?php echo htmlspecialchars($pedido['titulo']); ?>">
                                        <?php else: ?>
                                            <img src="../img/books/default-book.jpg" alt="Portada no disponible">
                                        <?php endif; ?>
                                    </div>
                                    <div class="order-item-details">
                                        <h4><?php echo htmlspecialchars($pedido['titulo']); ?></h4>
                                        <p>Cantidad: <?php echo $pedido['cantidad']; ?></p>
                                        <p>Precio: $<?php echo number_format($pedido['precio'], 2); ?></p>
                                        <p>Subtotal: $<?php echo number_format($subtotal, 2); ?></p>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                        
                        <div class="order-summary">
                            <div class="summary-row">
                                <span>Total del Pedido:</span>
                                <span>$<?php echo number_format($total, 2); ?></span>
                            </div>
                            <div class="summary-row">
                                <span>Fecha del Pedido:</span>
                                <span><?php echo date("d/m/Y H:i", strtotime($pedido['fecha_pedido'])); ?></span>
                            </div>
                        </div>
                    <?php else: ?>
                        <p>No se encontraron detalles del pedido.</p>
                    <?php endif; ?>
                </div>
                
                <div class="confirmation-actions">
                    <a href="index.php" class="btn-primary">
                        <i class="fas fa-book"></i> Seguir comprando
                    </a>
                    <a href="mis_pedidos.php" class="btn-secondary">
                        <i class="fas fa-list"></i> Ver todos mis pedidos
                    </a>
                </div>
            </div>
        </div>
    </main>

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

    <script src="../js/cliente.js"></script>
</body>
</html>
