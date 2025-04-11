<?php
session_start();
require_once "../includes/db.php";

// Obtener información del usuario
$sql = "SELECT * FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

// Obtener los pedidos del usuario
$sql = "SELECT p.id, p.fecha_pedido, p.cantidad, l.id as libro_id, l.titulo, l.autor, 
        l.categoria, l.precio, l.imagen 
        FROM pedidos p
        JOIN libros l ON p.libro_id = l.id
        WHERE p.usuario_id = ?
        ORDER BY p.fecha_pedido DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result_pedidos = $stmt->get_result();

// Agrupar pedidos por fecha
$pedidos_agrupados = [];
while ($pedido = $result_pedidos->fetch_assoc()) {
    $fecha = date("Y-m-d", strtotime($pedido['fecha_pedido']));
    if (!isset($pedidos_agrupados[$fecha])) {
        $pedidos_agrupados[$fecha] = [];
    }
    $pedidos_agrupados[$fecha][] = $pedido;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Read A Book - Mis Pedidos</title>
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
                <li><a href="mis_pedidos.php" class="active">Mis Pedidos</a></li>
                <li><a href="perfil.php">Mi Perfil</a></li>
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
        <div class="container">
            <h2>Mis Pedidos</h2>
            
            <?php if (empty($pedidos_agrupados)): ?>
                <div class="empty-orders">
                    <div class="empty-orders-icon">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <h3>No tienes pedidos realizados</h3>
                    <p>¡Explora nuestro catálogo y encuentra libros interesantes!</p>
                    <a href="index.php" class="btn-primary">Ver catálogo</a>
                </div>
            <?php else: ?>
                <div class="orders-container">
                    <?php foreach ($pedidos_agrupados as $fecha => $pedidos): ?>
                        <div class="order-group">
                            <div class="order-date">
                                <h3><?php echo date("d de F, Y", strtotime($fecha)); ?></h3>
                            </div>
                            
                            <div class="order-items">
                                <?php 
                                $total_pedido = 0;
                                foreach ($pedidos as $pedido): 
                                    $subtotal = $pedido['precio'] * $pedido['cantidad'];
                                    $total_pedido += $subtotal;
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
                                            <p class="order-item-author"><?php echo htmlspecialchars($pedido['autor']); ?></p>
                                            <p class="order-item-category"><?php echo htmlspecialchars($pedido['categoria']); ?></p>
                                            <div class="order-item-price-quantity">
                                                <p>Precio: $<?php echo number_format($pedido['precio'], 2); ?></p>
                                                <p>Cantidad: <?php echo $pedido['cantidad']; ?></p>
                                                <p>Subtotal: $<?php echo number_format($subtotal, 2); ?></p>
                                            </div>
                                            <div class="order-item-time">
                                                <p><i class="far fa-clock"></i> <?php echo date("H:i", strtotime($pedido['fecha_pedido'])); ?></p>
                                            </div>
                                        </div>
                                        <div class="order-item-actions">
                                            <a href="index.php?libro_id=<?php echo $pedido['libro_id']; ?>" class="btn-view-book">
                                                <i class="fas fa-eye"></i> Ver libro
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="order-summary">
                                <div class="order-total">
                                    <span>Total del pedido:</span>
                                    <span>$<?php echo number_format($total_pedido, 2); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
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