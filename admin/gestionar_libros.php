<?php
session_start();
include_once '../includes/db.php';

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Variables para mensajes
$message = '';
$error = '';

// Procesar formulario de agregar libro
if (isset($_POST['add_book'])) {
    $titulo = trim($_POST['titulo']);
    $autor = trim($_POST['autor']);
    $categoria = trim($_POST['categoria']);
    $precio = floatval($_POST['precio']);
    $descripcion = trim($_POST['descripcion']);
    $stock = intval($_POST['stock'] ?? 0);
    
    try {
        // Validaciones básicas
        if (empty($titulo) || empty($autor) || empty($categoria) || empty($descripcion)) {
            throw new Exception("Todos los campos obligatorios deben ser completados");
        }
        
        if ($precio <= 0) {
            throw new Exception("El precio debe ser mayor que cero");
        }
        
        // Procesar imagen
        $imagen = 'default-book.jpg'; // Imagen por defecto
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['imagen'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $maxSize = 2 * 1024 * 1024; // 2MB
            
            if (!in_array($file['type'], $allowedTypes)) {
                throw new Exception("Solo se permiten imágenes JPG, PNG o GIF");
            }
            
            if ($file['size'] > $maxSize) {
                throw new Exception("La imagen no debe superar los 2MB");
            }
            
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $imagen = uniqid('book_') . '.' . $extension;
            $uploadPath = '../uploads/' . $imagen;
            
            if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
                throw new Exception("Error al subir la imagen");
            }
        }
        
        // Insertar libro en la base de datos
        $stmt = $conn->prepare("INSERT INTO libros (titulo, autor, categoria, precio, descripcion, imagen, stock) 
                               VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssdssi", $titulo, $autor, $categoria, $precio, $descripcion, $imagen, $stock);
        
        if ($stmt->execute()) {
            $message = "Libro agregado exitosamente";
        } else {
            throw new Exception("Error al agregar el libro: " . $conn->error);
        }
        $stmt->close();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Procesar eliminación de libro
if (isset($_POST['delete_book'])) {
    $book_id = intval($_POST['book_id']);
    
    try {
        // Primero obtenemos la imagen para borrarla del servidor
        $stmt = $conn->prepare("SELECT imagen FROM libros WHERE id = ?");
        $stmt->bind_param("i", $book_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $libro = $result->fetch_assoc();
        $stmt->close();
        
        if ($libro && $libro['imagen'] !== 'default-book.jpg') {
            $imagePath = '../uploads/' . $libro['imagen'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        
        // Luego borramos el libro
        $stmt = $conn->prepare("DELETE FROM libros WHERE id = ?");
        $stmt->bind_param("i", $book_id);
        
        if ($stmt->execute()) {
            $message = "Libro eliminado exitosamente";
        } else {
            throw new Exception("Error al eliminar el libro: " . $conn->error);
        }
        $stmt->close();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Obtener listado de libros
$sql = "SELECT id, titulo, autor, categoria, precio, stock, imagen FROM libros ORDER BY id DESC";
$result = $conn->query($sql);

// Obtener categorías para el datalist
$sql_categorias = "SELECT DISTINCT categoria FROM libros";
$categorias_result = $conn->query($sql_categorias);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Libros - Read a Book</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/gestion_libros_.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include '../includes/admin_header.php'; ?>

    <div class="admin-wrapper">
        <div class="admin-content">
            <div class="admin-container">
                <div class="book-management-container">
                    <h1 class="book-management-title"><i class="fas fa-book"></i> Gestión de Libros</h1>

                    <?php if (!empty($message)): ?>
                        <div class="message">
                            <i class="fas fa-check-circle"></i> <?php echo $message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($error)): ?>
                        <div class="error">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <div class="form-container">
                        <h2><i class="fas fa-plus-circle"></i> Agregar Nuevo Libro</h2>
                        <form method="POST" enctype="multipart/form-data">
                            <div class="book-form">
                                <div class="form-group">
                                    <label for="titulo">Título:</label>
                                    <input type="text" id="titulo" name="titulo" required>
                                </div>
                                <div class="form-group">
                                    <label for="autor">Autor:</label>
                                    <input type="text" id="autor" name="autor" required>
                                </div>
                                <div class="form-group">
                                    <label for="categoria">Categoría:</label>
                                    <input type="text" id="categoria" name="categoria" list="categorias" required>
                                    <datalist id="categorias">
                                        <?php while ($categoria = $categorias_result->fetch_assoc()): ?>
                                            <option value="<?php echo htmlspecialchars($categoria['categoria']); ?>">
                                        <?php endwhile; ?>
                                    </datalist>
                                </div>
                                <div class="form-group">
                                    <label for="precio">Precio:</label>
                                    <input type="number" id="precio" name="precio" step="0.01" min="0.01" required>
                                </div>
                                <div class="form-group">
                                    <label for="stock">Stock:</label>
                                    <input type="number" id="stock" name="stock" min="0" value="0">
                                </div>
                                <div class="form-group full-width">
                                    <label for="descripcion">Descripción:</label>
                                    <textarea id="descripcion" name="descripcion" required></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="imagen">Portada:</label>
                                    <div class="file-upload">
                                        <label for="imagen" class="file-upload-label">
                                            <i class="fas fa-upload"></i>
                                            <span>Seleccionar imagen</span>
                                        </label>
                                        <input type="file" id="imagen" name="imagen" accept="image/*">
                                        <small class="form-hint">Formatos: JPG, PNG (max 2MB)</small>
                                    </div>
                                    <div id="imagen-preview" class="imagen-preview"></div>
                                </div>
                            </div>
                            <div class="book-actions">
                                <button type="submit" name="add_book" class="btn-add-book">
                                    <i class="fas fa-plus"></i> Agregar Libro
                                </button>
                                <button type="reset" class="btn-reset">
                                    <i class="fas fa-undo"></i> Limpiar
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="books-table-container">
                        <h2><i class="fas fa-list"></i> Listado de Libros</h2>
                        <div class="table-actions">
                            <div class="search-books">
                                <i class="fas fa-search"></i>
                                <input type="text" id="searchBook" placeholder="Buscar libros...">
                            </div>
                            <div class="filter-group">
                                <label for="filterCategory">Categoría:</label>
                                <select id="filterCategory">
                                    <option value="all">Todas</option>
                                    <?php 
                                    $categorias_result->data_seek(0); // Reiniciar el puntero del resultado
                                    while ($categoria = $categorias_result->fetch_assoc()): ?>
                                        <option value="<?php echo htmlspecialchars($categoria['categoria']); ?>">
                                            <?php echo htmlspecialchars($categoria['categoria']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <table class="books-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Título</th>
                                    <th>Autor</th>
                                    <th>Categoría</th>
                                    <th>Precio</th>
                                    <th>Stock</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result && $result->num_rows > 0): ?>
                                    <?php while ($libro = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $libro['id']; ?></td>
                                            <td>
                                                <div class="book-info">
                                                    <?php if ($libro['imagen'] !== 'default-book.jpg'): ?>
                                                        <img src="../uploads/<?php echo htmlspecialchars($libro['imagen']); ?>" 
                                                             alt="<?php echo htmlspecialchars($libro['titulo']); ?>" 
                                                             class="book-thumbnail">
                                                    <?php endif; ?>
                                                    <span><?php echo htmlspecialchars($libro['titulo']); ?></span>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($libro['autor']); ?></td>
                                            <td><?php echo htmlspecialchars($libro['categoria']); ?></td>
                                            <td>$<?php echo number_format($libro['precio'], 2); ?></td>
                                            <td><?php echo $libro['stock']; ?></td>
                                            <td class="book-table-actions">
                                                <a href="editar_libro.php?id=<?php echo $libro['id']; ?>" class="btn-edit">
                                                    <i class="fas fa-edit"></i> Editar
                                                </a>
                                                <form method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este libro?');">
                                                    <input type="hidden" name="book_id" value="<?php echo $libro['id']; ?>">
                                                    <button type="submit" name="delete_book" class="btn-delete">
                                                        <i class="fas fa-trash-alt"></i> Eliminar
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="no-data">
                                            <i class="fas fa-book-open"></i>
                                            <span>No hay libros registrados</span>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="book-pagination">
                        <ul class="pagination-list">
                            <li class="pagination-item active"><a href="#">1</a></li>
                            <li class="pagination-item"><a href="#">2</a></li>
                            <li class="pagination-item"><a href="#">3</a></li>
                            <li class="pagination-item"><a href="#">&raquo;</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/admin_footer.php'; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Filtrado de libros
        const searchInput = document.getElementById('searchBook');
        const filterCategory = document.getElementById('filterCategory');
        const bookRows = document.querySelectorAll('.books-table tbody tr');

        function filterBooks() {
            const searchValue = searchInput.value.toLowerCase();
            const categoryValue = filterCategory.value;

            bookRows.forEach(row => {
                const title = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                const author = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                const category = row.querySelector('td:nth-child(4)').textContent.toLowerCase();

                const matchesSearch = title.includes(searchValue) || author.includes(searchValue);
                const matchesCategory = categoryValue === 'all' || category === categoryValue.toLowerCase();

                row.style.display = (matchesSearch && matchesCategory) ? '' : 'none';
            });
        }

        searchInput.addEventListener('input', filterBooks);
        filterCategory.addEventListener('change', filterBooks);

        // Previsualización de imagen
        const imagenInput = document.getElementById('imagen');
        const imagenPreview = document.getElementById('imagen-preview');

        if (imagenInput && imagenPreview) {
            imagenInput.addEventListener('change', function(e) {
                if (e.target.files && e.target.files[0]) {
                    const reader = new FileReader();
                    
                    reader.onload = function(event) {
                        imagenPreview.innerHTML = `
                            <img src="${event.target.result}" alt="Vista previa de la portada">
                            <button class="btn-remove-preview">&times;</button>
                        `;
                        
                        // Botón para eliminar la previsualización
                        const removeBtn = imagenPreview.querySelector('.btn-remove-preview');
                        removeBtn.addEventListener('click', function() {
                            imagenPreview.innerHTML = '';
                            imagenInput.value = '';
                        });
                    };
                    
                    reader.readAsDataURL(e.target.files[0]);
                }
            });
        }

        // Toggle del menú lateral
        const toggleMenu = document.querySelector('.toggle-menu');
        const adminWrapper = document.querySelector('.admin-wrapper');

        if (toggleMenu) {
            toggleMenu.addEventListener('click', function() {
                adminWrapper.classList.toggle('sidebar-collapsed');
            });
        }
    });
    </script>
</body>
</html>

<?php $conn->close(); ?>