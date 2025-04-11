<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['id'])) {
    // Redirigir al login si no está logueado
    header("Location: ../index.php");
    exit();
}

require_once "../includes/db.php";

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
    session_destroy();
    header("Location: ../index.php");
    exit();
}

// Obtener categorías para el filtro
$sql_categorias = "SELECT DISTINCT categoria FROM libros ORDER BY categoria";
$result_categorias = $conn->query($sql_categorias);

// Obtener autores para el filtro
$sql_autores = "SELECT DISTINCT autor FROM libros ORDER BY autor";
$result_autores = $conn->query($sql_autores);

// Procesar filtros
$where_conditions = [];
$params = [];
$types = "";

// Filtro por categoría
if (isset($_GET['categoria']) && !empty($_GET['categoria'])) {
    $where_conditions[] = "categoria = ?";
    $params[] = $_GET['categoria'];
    $types .= "s";
}

// Filtro por autor
if (isset($_GET['autor']) && !empty($_GET['autor'])) {
    $where_conditions[] = "autor = ?";
    $params[] = $_GET['autor'];
    $types .= "s";
}

// Filtro por precio
if (isset($_GET['precio_min']) && is_numeric($_GET['precio_min'])) {
    $where_conditions[] = "precio >= ?";
    $params[] = $_GET['precio_min'];
    $types .= "d";
}

if (isset($_GET['precio_max']) && is_numeric($_GET['precio_max'])) {
    $where_conditions[] = "precio <= ?";
    $params[] = $_GET['precio_max'];
    $types .= "d";
}

// Filtro por búsqueda
if (isset($_GET['busqueda']) && !empty($_GET['busqueda'])) {
    $where_conditions[] = "(titulo LIKE ? OR autor LIKE ? OR descripcion LIKE ?)";
    $search_term = "%" . $_GET['busqueda'] . "%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "sss";
}

// Construir la consulta SQL
$sql = "SELECT * FROM libros";
if (!empty($where_conditions)) {
    $sql .= " WHERE " . implode(" AND ", $where_conditions);
}
$sql .= " ORDER BY titulo ASC";

// Preparar y ejecutar la consulta
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result_libros = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Read A Book - Catálogo</title>
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
                <li><a href="index.php" class="active">Catálogo</a></li>
                <li><a href="mis_pedidos.php">Mis Pedidos</a></li>
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
            <div class="sidebar">
                <div class="welcome-section">
                    <h3>Bienvenido, <?php echo isset($usuario['nombre']) ? htmlspecialchars($usuario['nombre']) : 'Usuario'; ?></h3>
                </div>
                
                <div class="filter-section">
                    <h3>Filtros</h3>
                    <form action="index.php" method="GET">
                        <div class="form-group">
                            <label for="busqueda">Buscar:</label>
                            <input type="text" id="busqueda" name="busqueda" 
                                value="<?php echo isset($_GET['busqueda']) ? htmlspecialchars($_GET['busqueda']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="categoria">Categoría:</label>
                            <select id="categoria" name="categoria">
                                <option value="">Todas las categorías</option>
                                <?php while ($result_categorias && $categoria = $result_categorias->fetch_assoc()): ?>
                                    <option value="<?php echo htmlspecialchars($categoria['categoria']); ?>" 
                                        <?php echo (isset($_GET['categoria']) && $_GET['categoria'] == $categoria['categoria']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($categoria['categoria']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="autor">Autor:</label>
                            <select id="autor" name="autor">
                                <option value="">Todos los autores</option>
                                <?php while ($result_autores && $autor = $result_autores->fetch_assoc()): ?>
                                    <option value="<?php echo htmlspecialchars($autor['autor']); ?>"
                                        <?php echo (isset($_GET['autor']) && $_GET['autor'] == $autor['autor']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($autor['autor']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Precio:</label>
                            <div class="price-range">
                                <input type="number" id="precio_min" name="precio_min" placeholder="Min" min="0" step="0.01"
                                    value="<?php echo isset($_GET['precio_min']) ? htmlspecialchars($_GET['precio_min']) : ''; ?>">
                                <span>-</span>
                                <input type="number" id="precio_max" name="precio_max" placeholder="Max" min="0" step="0.01"
                                    value="<?php echo isset($_GET['precio_max']) ? htmlspecialchars($_GET['precio_max']) : ''; ?>">
                            </div>
                        </div>
                        
                        <button type="submit" class="btn-filter">Aplicar Filtros</button>
                        <a href="index.php" class="btn-reset">Limpiar Filtros</a>
                    </form>
                </div>
                
                <div class="subscription-section">
                    <h3>Suscríbete</h3>
                    <p>Recibe notificaciones sobre nuevos libros y ofertas especiales.</p>
                    <button id="btnSubscribe" class="btn-subscribe">Suscribirse</button>
                </div>
            </div>
            
            <div class="content">
                <h2>Catálogo de Libros</h2>
                
                <?php if ($result_libros->num_rows > 0): ?>
                    <div class="books-grid">
                        <?php while ($libro = $result_libros->fetch_assoc()): ?>
                            <div class="book-card" data-id="<?php echo $libro['id']; ?>">
                                <div class="book-image">
                                    <?php if (!empty($libro['imagen'])): ?>
                                        <img src="../img/books/<?php echo htmlspecialchars($libro['imagen']); ?>" alt="<?php echo htmlspecialchars($libro['titulo']); ?>">
                                    <?php else: ?>
                                        <img src="../img/books/default-book.jpg" alt="Portada no disponible">
                                    <?php endif; ?>
                                </div>
                                <div class="book-info">
                                    <h3><?php echo htmlspecialchars($libro['titulo']); ?></h3>
                                    <p class="author"><?php echo htmlspecialchars($libro['autor']); ?></p>
                                    <p class="category"><?php echo htmlspecialchars($libro['categoria']); ?></p>
                                    <p class="price">$<?php echo number_format($libro['precio'], 2); ?></p>
                                    <p class="stock">
                                        <?php if ($libro['stock'] > 0): ?>
                                            <span class="in-stock">En stock (<?php echo $libro['stock']; ?>)</span>
                                        <?php else: ?>
                                            <span class="out-of-stock">Agotado</span>
                                        <?php endif; ?>
                                    </p>
                                    <div class="book-actions">
                                        <button class="btn-details" data-id="<?php echo $libro['id']; ?>">Ver detalles</button>
                                        <?php if ($libro['stock'] > 0): ?>
                                            <button class="btn-add-cart" data-id="<?php echo $libro['id']; ?>">
                                                <i class="fas fa-cart-plus"></i> Añadir
                                            </button>
                                        <?php else: ?>
                                            <button class="btn-add-cart disabled" disabled>
                                                <i class="fas fa-cart-plus"></i> Agotado
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="no-results">
                        <p>No se encontraron libros que coincidan con los filtros seleccionados.</p>
                        <a href="index.php" class="btn-reset">Ver todo el catálogo</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Modal de detalles del libro -->
    <div id="bookDetailsModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div id="bookDetailsContent"></div>
        </div>
    </div>

    <!-- Modal de suscripción -->
    <div id="subscriptionModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Suscríbete a Nuestras Novedades</h2>
            <p>Completa el formulario para recibir notificaciones sobre nuevos libros, ofertas especiales y eventos.</p>
            <form id="subscriptionForm">
                <div class="form-group">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" value="<?php echo isset($usuario['nombre']) ? htmlspecialchars($usuario['nombre']) : ''; ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo isset($usuario['email']) ? htmlspecialchars($usuario['email']) : ''; ?>" readonly>
                </div>
                <div class="form-group">
                    <label>Intereses:</label>
                    <div class="checkbox-group">
                        <label><input type="checkbox" name="intereses[]" value="ficcion"> Ficción</label>
                        <label><input type="checkbox" name="intereses[]" value="no-ficcion"> No Ficción</label>
                        <label><input type="checkbox" name="intereses[]" value="ciencia-ficcion"> Ciencia Ficción</label>
                        <label><input type="checkbox" name="intereses[]" value="fantasia"> Fantasía</label>
                        <label><input type="checkbox" name="intereses[]" value="historia"> Historia</label>
                        <label><input type="checkbox" name="intereses[]" value="thriller"> Thriller</label>
                    </div>
                </div>
                <div class="form-group">
                    <label>Frecuencia de notificaciones:</label>
                    <select name="frecuencia">
                        <option value="diaria">Diaria</option>
                        <option value="semanal" selected>Semanal</option>
                        <option value="mensual">Mensual</option>
                    </select>
                </div>
                <button type="submit" class="btn-submit">Suscribirse</button>
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

    <script src="../js/cliente.js"></script>
</body>
</html>