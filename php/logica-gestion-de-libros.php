<?php
/**
 * 
 * 
 * @package ReadABook
 * @version 2.0
 */

declare(strict_types=1);

// ==================== CONFIGURACIÓN INICIAL ====================

// Inicialización estricta de variables
$conn = null;
$result = [];
$categorias_result = [];
$autores_result = [];
$message = '';
$error = '';
$libro_actual = null;

// Configuración de manejo de errores
ini_set('display_errors', '0');
error_reporting(E_ALL);

// ==================== MANEJO DE SESIÓN ====================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificación de permisos
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// ==================== CONEXIÓN A BASE DE DATOS ====================

if (!isset($conn) || $conn === null) {
    $db_config_paths = [
        __DIR__ . '/../../includes/db.php',
        __DIR__ . '/../includes/db.php',
        __DIR__ . '/includes/db.php'
    ];
    
    $db_config_loaded = false;
    foreach ($db_config_paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            $db_config_loaded = true;
            break;
        }
    }
    
    if (!$db_config_loaded) {
        error_log("Error: No se pudo cargar la configuración de la base de datos");
        die("Error en el sistema. Por favor, intente más tarde.");
    }

    try {
        $conn = new mysqli($servername, $username, $password, $dbname);
        
        if ($conn->connect_error) {
            throw new RuntimeException("Error de conexión a la base de datos");
        }
        
        $conn->set_charset("utf8mb4");
    } catch (Throwable $e) {
        error_log("Error de conexión: " . $e->getMessage());
        die("Error en el sistema. Por favor, intente más tarde.");
    }
}

// ==================== FUNCIONES AUXILIARES ====================

/**
 * Sanitiza y valida una entrada de texto
 */
function sanitizarTexto(mysqli $conn, string $input, int $max_length = 255): string {
    $input = trim($input);
    if (empty($input)) {
        throw new InvalidArgumentException("El campo no puede estar vacío");
    }
    
    if (mb_strlen($input) > $max_length) {
        throw new InvalidArgumentException("El texto excede el máximo de caracteres permitidos");
    }
    
    return $conn->real_escape_string($input);
}

/**
 * Valida y procesa el precio
 */
function validarPrecio(string $precio): float {
    if (!is_numeric($precio)) {
        throw new InvalidArgumentException("El precio debe ser un número válido");
    }
    
    $precio = (float) $precio;
    if ($precio <= 0) {
        throw new InvalidArgumentException("El precio debe ser mayor que cero");
    }
    
    return round($precio, 2);
}

/**
 * Procesa la imagen subida
 */
function procesarImagen(?array $imagen, string $upload_dir): string {
    if (!$imagen || $imagen['error'] !== UPLOAD_ERR_OK) {
        return 'default-book.jpg';
    }
    
    // Validar tipo de archivo
    $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($imagen['tmp_name']);
    
    if (!in_array($mime, $allowed_types)) {
        throw new InvalidArgumentException("Tipo de archivo no permitido. Solo se aceptan JPEG, PNG y WebP");
    }
    
    // Validar tamaño (máx 2MB)
    if ($imagen['size'] > 2097152) {
        throw new InvalidArgumentException("La imagen es demasiado grande (máx 2MB)");
    }
    
    // Crear directorio si no existe
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Generar nombre único
    $ext = pathinfo($imagen['name'], PATHINFO_EXTENSION);
    $new_filename = 'book_' . uniqid() . '.' . $ext;
    $destination = $upload_dir . $new_filename;
    
    if (!move_uploaded_file($imagen['tmp_name'], $destination)) {
        throw new RuntimeException("Error al guardar la imagen");
    }
    
    return 'img/uploads/' . $new_filename;
}

// ==================== MANEJO DE SOLICITUDES ====================

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // AGREGAR NUEVO LIBRO
        if (isset($_POST['add_book'])) {
            $required_fields = ['titulo', 'autor', 'descripcion', 'categoria', 'precio'];
            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    throw new InvalidArgumentException("El campo $field es obligatorio");
                }
            }
            
            $titulo = sanitizarTexto($conn, $_POST['titulo']);
            $autor = sanitizarTexto($conn, $_POST['autor']);
            $descripcion = sanitizarTexto($conn, $_POST['descripcion'], 1000);
            $categoria = sanitizarTexto($conn, $_POST['categoria']);
            $precio = validarPrecio($_POST['precio']);
            $imagen_path = procesarImagen($_FILES['imagen'] ?? null, __DIR__ . '/../../img/uploads/');
            
            // Verificar si el libro ya existe
            $stmt = $conn->prepare("SELECT id FROM libros WHERE titulo = ? AND autor = ?");
            $stmt->bind_param("ss", $titulo, $autor);
            $stmt->execute();
            
            if ($stmt->get_result()->num_rows > 0) {
                throw new RuntimeException("El libro '$titulo' ya existe en la base de datos");
            }
            $stmt->close();
            
            // Insertar libro
            $stmt = $conn->prepare("INSERT INTO libros (titulo, autor, categoria, precio, descripcion, imagen) 
                                   VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssdss", $titulo, $autor, $categoria, $precio, $descripcion, $imagen_path);
            
            if (!$stmt->execute()) {
                throw new RuntimeException("Error al agregar libro: " . $stmt->error);
            }
            
            $message = "Libro '$titulo' agregado correctamente";
            $stmt->close();
        }
        
        // ELIMINAR LIBRO
        if (isset($_POST['delete_book'])) {
            if (empty($_POST['book_id'])) {
                throw new InvalidArgumentException("ID de libro no proporcionado");
            }
            
            $book_id = (int)$_POST['book_id'];
            if ($book_id <= 0) {
                throw new InvalidArgumentException("ID de libro inválido");
            }
            
            // Verificar pedidos asociados
            $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM pedidos WHERE libro_id = ?");
            $stmt->bind_param("i", $book_id);
            $stmt->execute();
            $count = $stmt->get_result()->fetch_assoc()['count'];
            $stmt->close();
            
            if ($count > 0) {
                throw new RuntimeException("No se puede eliminar: el libro tiene pedidos asociados");
            }
            
            // Eliminar libro
            $stmt = $conn->prepare("DELETE FROM libros WHERE id = ?");
            $stmt->bind_param("i", $book_id);
            
            if (!$stmt->execute()) {
                throw new RuntimeException("Error al eliminar libro: " . $stmt->error);
            }
            
            $message = "Libro eliminado correctamente";
            $stmt->close();
        }
        
        // EDITAR LIBRO
        if (isset($_POST['edit_book'])) {
            $required_fields = ['book_id', 'titulo', 'autor', 'descripcion', 'categoria', 'precio'];
            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    throw new InvalidArgumentException("El campo $field es obligatorio");
                }
            }
            
            $book_id = (int)$_POST['book_id'];
            if ($book_id <= 0) {
                throw new InvalidArgumentException("ID de libro inválido");
            }
            
            $titulo = sanitizarTexto($conn, $_POST['titulo']);
            $autor = sanitizarTexto($conn, $_POST['autor']);
            $descripcion = sanitizarTexto($conn, $_POST['descripcion'], 1000);
            $categoria = sanitizarTexto($conn, $_POST['categoria']);
            $precio = validarPrecio($_POST['precio']);
            
            // Verificar existencia del libro
            $stmt = $conn->prepare("SELECT imagen FROM libros WHERE id = ?");
            $stmt->bind_param("i", $book_id);
            $stmt->execute();
            $libro = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            
            if (!$libro) {
                throw new RuntimeException("El libro que intenta editar no existe");
            }
            
            // Procesar imagen si se subió una nueva
            $imagen_path = $libro['imagen'];
            try {
                $new_image = procesarImagen($_FILES['imagen'] ?? null, __DIR__ . '/../../img/uploads/');
                if ($new_image !== 'default-book.jpg') {
                    $imagen_path = $new_image;
                }
            } catch (InvalidArgumentException $e) {
                // Si hay error en la imagen pero no es obligatoria, continuar
            }
            
            // Actualizar libro
            $stmt = $conn->prepare("UPDATE libros SET 
                                  titulo = ?, autor = ?, descripcion = ?, 
                                  categoria = ?, precio = ?, imagen = ?
                                  WHERE id = ?");
            $stmt->bind_param("ssssdsi", $titulo, $autor, $descripcion, 
                             $categoria, $precio, $imagen_path, $book_id);
            
            if (!$stmt->execute()) {
                throw new RuntimeException("Error al actualizar libro: " . $stmt->error);
            }
            
            $message = "Libro '$titulo' actualizado correctamente";
            $stmt->close();
        }
        
    } catch (InvalidArgumentException $e) {
        $error = $e->getMessage();
    } catch (RuntimeException $e) {
        $error = $e->getMessage();
        error_log("Error en gestión de libros: " . $error);
    } catch (Throwable $e) {
        $error = "Error inesperado. Por favor, intente nuevamente.";
        error_log("Error inesperado: " . $e->getMessage());
    }
}

// ==================== OBTENER DATOS PARA LA VISTA ====================

try {
    // Obtener lista de libros
    $result = $conn->query("
        SELECT l.*, 
               (SELECT COUNT(*) FROM pedidos p WHERE p.libro_id = l.id) as total_ventas
        FROM libros l
        ORDER BY l.titulo ASC
    ");
    
    if ($result === false) {
        throw new RuntimeException("Error al obtener libros");
    }
    
    // Obtener categorías para formularios
    $categorias_result = $conn->query("SELECT nombre FROM categorias ORDER BY nombre");
    if ($categorias_result === false) {
        throw new RuntimeException("Error al obtener categorías");
    }
    
    // Obtener autores para formularios
    $autores_result = $conn->query("SELECT nombre FROM autores ORDER BY nombre");
    if ($autores_result === false) {
        throw new RuntimeException("Error al obtener autores");
    }
    
} catch (RuntimeException $e) {
    $error = $e->getMessage();
    $result = [];
    $categorias_result = [];
    $autores_result = [];
}
