<?php
session_start();
include_once '../includes/db.php';

// Verificar permisos de administrador
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Variables para mensajes
$message = '';
$error = '';
$libro = [];

// Obtener el ID del libro a editar
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: gestionar_libros.php");
    exit();
}

$book_id = intval($_GET['id']);

// Obtener datos del libro
$stmt = $conn->prepare("SELECT * FROM libros WHERE id = ?");
$stmt->bind_param("i", $book_id);
$stmt->execute();
$result = $stmt->get_result();
$libro = $result->fetch_assoc();
$stmt->close();

if (!$libro) {
    header("Location: gestionar_libros.php");
    exit();
}

// Obtener categorías para el datalist
$sql_categorias = "SELECT DISTINCT categoria FROM libros";
$categorias_result = $conn->query($sql_categorias);

// Procesar formulario de actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_book'])) {
    try {
        $titulo = trim($_POST['titulo']);
        $autor = trim($_POST['autor']);
        $categoria = trim($_POST['categoria']);
        $precio = floatval($_POST['precio']);
        $descripcion = trim($_POST['descripcion']);
        $stock = intval($_POST['stock']);
        
        // Validaciones básicas
        if (empty($titulo) || empty($autor) || empty($categoria) || empty($descripcion)) {
            throw new Exception("Todos los campos obligatorios deben ser completados");
        }
        
        if ($precio <= 0) {
            throw new Exception("El precio debe ser mayor que cero");
        }
        
        if ($stock < 0) {
            throw new Exception("El stock no puede ser negativo");
        }
        
        // Procesar imagen
        $imagen = $libro['imagen']; // Mantener la imagen actual por defecto
        
        // Si se sube una nueva imagen
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
            
            // Eliminar la imagen anterior si no es la por defecto
            if ($imagen !== 'default-book.jpg') {
                $oldImagePath = '../uploads/' . $imagen;
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            
            // Subir la nueva imagen
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $imagen = uniqid('book_') . '.' . $extension;
            $uploadPath = '../uploads/' . $imagen;
            
            if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
                throw new Exception("Error al subir la imagen");
            }
        }
        
        // Si se marca para eliminar la imagen
        if (isset($_POST['remove_image']) && $_POST['remove_image'] === '1') {
            if ($imagen !== 'default-book.jpg') {
                $oldImagePath = '../uploads/' . $imagen;
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            $imagen = 'default-book.jpg';
        }
        
        // Actualizar libro en la base de datos
        $stmt = $conn->prepare("UPDATE libros SET 
                               titulo = ?, autor = ?, categoria = ?, precio = ?, 
                               descripcion = ?, imagen = ?, stock = ? 
                               WHERE id = ?");
        $stmt->bind_param("sssdssii", $titulo, $autor, $categoria, $precio, 
                          $descripcion, $imagen, $stock, $book_id);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Libro actualizado exitosamente";
            header("Location: gestionar_libros.php");
            exit();
        } else {
            throw new Exception("Error al actualizar el libro: " . $conn->error);
        }
        $stmt->close();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Libro - Read a Book</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/editar_libro.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include '../includes/admin_header.php'; ?>

    <div class="admin-wrapper">
        <div class="admin-content">
            <div class="admin-container">
                <div class="book-management-container">
                    <h1 class="book-management-title">
                        <i class="fas fa-edit"></i> Editar Libro: <?php echo htmlspecialchars($libro['titulo']); ?>
                    </h1>

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
                        <form method="POST" enctype="multipart/form-data" id="editBookForm">
                            <div class="book-form">
                                <div class="form-group">
                                    <label for="titulo">Título:</label>
                                    <input type="text" id="titulo" name="titulo" required
                                           value="<?php echo htmlspecialchars($libro['titulo']); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="autor">Autor:</label>
                                    <input type="text" id="autor" name="autor" required
                                           value="<?php echo htmlspecialchars($libro['autor']); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="categoria">Categoría:</label>
                                    <input type="text" id="categoria" name="categoria" 
                                           list="categorias" required
                                           value="<?php echo htmlspecialchars($libro['categoria']); ?>">
                                    <datalist id="categorias">
                                        <?php while ($categoria = $categorias_result->fetch_assoc()): ?>
                                            <option value="<?php echo htmlspecialchars($categoria['categoria']); ?>">
                                        <?php endwhile; ?>
                                    </datalist>
                                </div>
                                <div class="form-group">
                                    <label for="precio">Precio:</label>
                                    <input type="number" id="precio" name="precio" 
                                           step="0.01" min="0.01" required
                                           value="<?php echo htmlspecialchars($libro['precio']); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="stock">Stock:</label>
                                    <input type="number" id="stock" name="stock" min="0"
                                           value="<?php echo htmlspecialchars($libro['stock']); ?>">
                                </div>
                                <div class="form-group full-width">
                                    <label for="descripcion">Descripción:</label>
                                    <textarea id="descripcion" name="descripcion" required><?php 
                                        echo htmlspecialchars($libro['descripcion']); 
                                    ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Portada actual:</label>
                                    <?php if ($libro['imagen'] !== 'default-book.jpg'): ?>
                                        <div class="current-image-preview">
                                            <img src="../uploads/<?php echo htmlspecialchars($libro['imagen']); ?>" 
                                                 alt="Portada actual" class="current-cover">
                                            <div class="image-actions">
                                                <label class="btn-change-image">
                                                    <i class="fas fa-sync-alt"></i> Cambiar imagen
                                                    <input type="file" id="imagen" name="imagen" accept="image/*">
                                                </label>
                                                <label class="btn-remove-image">
                                                    <i class="fas fa-trash-alt"></i> Eliminar imagen
                                                    <input type="checkbox" name="remove_image" value="1">
                                                </label>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="file-upload">
                                            <label for="imagen" class="file-upload-label">
                                                <i class="fas fa-upload"></i>
                                                <span>Seleccionar imagen</span>
                                            </label>
                                            <input type="file" id="imagen" name="imagen" accept="image/*">
                                            <small class="form-hint">Formatos: JPG, PNG (max 2MB)</small>
                                        </div>
                                    <?php endif; ?>
                                    <div id="imagen-preview" class="imagen-preview"></div>
                                </div>
                            </div>
                            <div class="book-actions">
                                <button type="submit" name="update_book" class="btn-add-book">
                                    <i class="fas fa-save"></i> Guardar Cambios
                                </button>
                                <a href="gestionar_libros.php" class="btn-reset">
                                    <i class="fas fa-times"></i> Cancelar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/admin_footer.php'; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Previsualización de imagen al seleccionar nueva
        const imagenInput = document.getElementById('imagen');
        const imagenPreview = document.getElementById('imagen-preview');

        if (imagenInput && imagenPreview) {
            imagenInput.addEventListener('change', function(e) {
                if (e.target.files && e.target.files[0]) {
                    const reader = new FileReader();
                    
                    reader.onload = function(event) {
                        imagenPreview.innerHTML = `
                            <img src="${event.target.result}" alt="Vista previa de la nueva portada">
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

        // Confirmación antes de eliminar imagen
        const removeImageCheckbox = document.querySelector('input[name="remove_image"]');
        if (removeImageCheckbox) {
            removeImageCheckbox.addEventListener('change', function(e) {
                if (this.checked && !confirm('¿Estás seguro de que deseas eliminar la imagen actual?')) {
                    this.checked = false;
                }
            });
        }

        // Validación de formulario
        const form = document.getElementById('editBookForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                // Validar campos requeridos
                const requiredFields = form.querySelectorAll('[required]');
                let isValid = true;
                
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        field.style.borderColor = '#ef233c';
                        isValid = false;
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    alert('Por favor complete todos los campos requeridos.');
                }
            });
        }
    });
    </script>
</body>
</html>

<?php $conn->close(); ?>