<?php
session_start();
require_once "../includes/db.php";

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit();
}

// Verificar si se recibió un ID válido
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'ID de libro no válido']);
    exit();
}

$libro_id = $_POST['id'];

// Consultar información del libro
$sql = "SELECT * FROM libros WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $libro_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Libro no encontrado']);
    exit();
}

$libro = $result->fetch_assoc();

// Generar el HTML para el modal
$html = '
<div class="book-detail">
    <div class="book-detail-flex">
        <div class="book-detail-image">
            ' . (!empty($libro['imagen']) ? 
                '<img src="../img/books/' . htmlspecialchars($libro['imagen']) . '" alt="' . htmlspecialchars($libro['titulo']) . '">' : 
                '<img src="../img/books/default-book.jpg" alt="Portada no disponible">') . '
        </div>
        <div class="book-detail-info">
            <h2>' . htmlspecialchars($libro['titulo']) . '</h2>
            <p class="author"><strong>Autor:</strong> ' . htmlspecialchars($libro['autor']) . '</p>
            <p class="category"><strong>Categoría:</strong> ' . htmlspecialchars($libro['categoria']) . '</p>
            <p class="price"><strong>Precio:</strong> $' . number_format($libro['precio'], 2) . '</p>
            <p class="stock">
                <strong>Disponibilidad:</strong> 
                ' . ($libro['stock'] > 0 ? 
                    '<span class="in-stock">En stock (' . $libro['stock'] . ' disponibles)</span>' : 
                    '<span class="out-of-stock">Agotado</span>') . '
            </p>
            <div class="book-description">
                <h3>Descripción:</h3>
                <p>' . nl2br(htmlspecialchars($libro['descripcion'] ?? 'No hay descripción disponible para este libro.')) . '</p>
            </div>
            
            <div class="book-actions">
                ' . ($libro['stock'] > 0 ? 
                    '<div class="quantity-selector">
                        <label for="quantity">Cantidad:</label>
                        <div class="quantity-controls">
                            <button type="button" class="quantity-decrease">-</button>
                            <input type="number" id="quantity" name="quantity" value="1" min="1" max="' . $libro['stock'] . '">
                            <button type="button" class="quantity-increase">+</button>
                        </div>
                    </div>
                    <button class="btn-add-cart-modal" data-id="' . $libro['id'] . '">
                        <i class="fas fa-cart-plus"></i> Añadir al carrito
                    </button>' : 
                    '<button class="btn-add-cart disabled" disabled>
                        <i class="fas fa-cart-plus"></i> Agotado
                    </button>') . '
            </div>
        </div>
    </div>
</div>';

header('Content-Type: application/json');
echo json_encode(['html' => $html]);
?>