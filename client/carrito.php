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

// Inicializar carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Función para calcular el total del carrito
function calcularTotal() {
    global $conn;
    $total = 0;
    
    foreach ($_SESSION['carrito'] as $item) {
        // Obtener el precio actual del libro
        $sql = "SELECT precio FROM libros WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $item['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $libro = $result->fetch_assoc();
            $total += $libro['precio'] * $item['cantidad'];
        }
    }
    
    return $total;
}

// Procesar la finalización del pedido
if (isset($_POST['finalizar_pedido'])) {
    $errores = [];
    $libros_sin_stock = [];
    
    // Verificar stock disponible para cada libro
    foreach ($_SESSION['carrito'] as $item) {
        $sql = "SELECT titulo, stock FROM libros WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $item['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $libro = $result->fetch_assoc();
        
        if ($libro['stock'] < $item['cantidad']) {
            $libros_sin_stock[] = [
                'titulo' => $libro['titulo'],
                'solicitados' => $item['cantidad'],
                'disponibles' => $libro['stock']
            ];
        }
    }
    
    if (!empty($libros_sin_stock)) {
        $errores[] = "Algunos libros no tienen suficiente stock disponible.";
    }
    
    // Si no hay errores, procesar el pedido
    if (empty($errores)) {
        // Iniciar transacción
        $conn->begin_transaction();
        
        try {
            // Insertar cada libro del pedido
            foreach ($_SESSION['carrito'] as $item) {
                // Insertar el pedido
                $sql = "INSERT INTO pedidos (usuario_id, libro_id, cantidad) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iii", $usuario_id, $item['id'], $item['cantidad']);
                $stmt->execute();
                
                // Actualizar el stock del libro
                $sql = "UPDATE libros SET stock = stock - ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $item['cantidad'], $item['id']);
                $stmt->execute();
            }
            
            // Confirmar transacción
            $conn->commit();
            
            // Crear mensaje de éxito
            $_SESSION['mensaje_exito'] = "¡Pedido realizado con éxito! Puedes ver el estado en 'Mis Pedidos'.";
            
            // Vaciar el carrito
            $_SESSION['carrito'] = [];
            
            // Redirigir a la página de confirmación
            header("Location: confirmacion_pedido.php");
            exit();
            
        } catch (Exception $e) {
            // Revertir la transacción en caso de error
            $conn->rollback();
            $errores[] = "Error al procesar el pedido: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Read A Book - Carrito de Compras</title>
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
                <li><a href="perfil.php">Mi Perfil</a></li>
                <li>
                    <a href="carrito.php" class="cart-icon active">
                        <i class="fas fa-shopping-cart"></i>
                        <span id="cart-count">
                            <?php echo count($_SESSION['carrito']); ?>
                        </span>
                    </a>
                </li>
                <li><a href="../logout.php">Cerrar Sesión</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="container">
            <h2>Carrito de Compras</h2>
            
            <?php if (!empty($errores)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errores as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                    
                    <?php if (!empty($libros_sin_stock)): ?>
                        <div class="stock-details">
                            <?php foreach ($libros_sin_stock as $libro): ?>
                                <p>
                                    <strong><?php echo htmlspecialchars($libro['titulo']); ?></strong>: 
                                    Solicitados: <?php echo $libro['solicitados']; ?>, 
                                    Disponibles: <?php echo $libro['disponibles']; ?>
                                </p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <?php if (empty($_SESSION['carrito'])): ?>
                <div class="empty-cart">
                    <div class="empty-cart-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <h3>Tu carrito está vacío</h3>
                    <p>Parece que aún no has añadido ningún libro a tu carrito.</p>
                    <a href="index.php" class="btn-continue-shopping">Seguir comprando</a>
                </div>
            <?php else: ?>
                <div class="cart-container">
                    <div class="cart-items">
                        <table class="cart-table">
                            <thead>
                                <tr>
                                    <th>Libro</th>
                                    <th>Precio</th>
                                    <th>Cantidad</th>
                                    <th>Subtotal</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $total = 0;
                                foreach ($_SESSION['carrito'] as $indice => $item): 
                                    // Obtener información actualizada del libro
                                    $sql = "SELECT titulo, precio, stock, imagen FROM libros WHERE id = ?";
                                    $stmt = $conn->prepare($sql);
                                    $stmt->bind_param("i", $item['id']);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    $libro = $result->fetch_assoc();
                                    
                                    // Calcular subtotal
                                    $subtotal = $libro['precio'] * $item['cantidad'];
                                    $total += $subtotal;
                                ?>
                                    <tr data-id="<?php echo $item['id']; ?>">
                                        <td class="cart-item-info">
                                            <div class="cart-item-image">
                                                <?php if (!empty($libro['imagen'])): ?>
                                                    <img src="../img/books/<?php echo htmlspecialchars($libro['imagen']); ?>" alt="<?php echo htmlspecialchars($libro['titulo']); ?>">
                                                <?php else: ?>
                                                    <img src="../img/books/default-book.jpg" alt="Portada no disponible">
                                                <?php endif; ?>
                                            </div>
                                            <div class="cart-item-details">
                                                <h4><?php echo htmlspecialchars($libro['titulo']); ?></h4>
                                                <p class="stock-info">
                                                    <?php if ($libro['stock'] < $item['cantidad']): ?>
                                                        <span class="stock-warning">Solo <?php echo $libro['stock']; ?> disponibles</span>
                                                    <?php else: ?>
                                                        <span class="in-stock"><?php echo $libro['stock']; ?> disponibles</span>
                                                    <?php endif; ?>
                                                </p>
                                            </div>
                                        </td>
                                        <td class="cart-item-price">$<?php echo number_format($libro['precio'], 2); ?></td>
                                        <td class="cart-item-quantity">
                                            <div class="quantity-controls">
                                                <button type="button" class="quantity-decrease">-</button>
                                                <input type="number" class="item-quantity" value="<?php echo $item['cantidad']; ?>" min="1" max="<?php echo $libro['stock']; ?>" data-id="<?php echo $item['id']; ?>">
                                                <button type="button" class="quantity-increase">+</button>
                                            </div>
                                        </td>
                                        <td class="cart-item-subtotal">$<?php echo number_format($subtotal, 2); ?></td>
                                        <td class="cart-item-actions">
                                            <button class="btn-remove-item" data-id="<?php echo $item['id']; ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="cart-summary">
                        <h3>Resumen del Pedido</h3>
                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <span>$<?php echo number_format($total, 2); ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Envío:</span>
                            <span>Gratis</span>
                        </div>
                        <div class="summary-row total">
                            <span>Total:</span>
                            <span>$<?php echo number_format($total, 2); ?></span>
                        </div>
                        
                        <form method="POST" action="carrito.php">
                            <button type="submit" name="finalizar_pedido" class="btn-checkout">
                                Finalizar Pedido
                            </button>
                        </form>
                        
                        <a href="index.php" class="btn-continue-shopping">
                            <i class="fas fa-arrow-left"></i> Seguir comprando
                        </a>
                    </div>
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