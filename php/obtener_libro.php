<?php
/**
 * Script para obtener los detalles de un libro específico
 */

// Iniciar sesión para verificar autenticación
session_start();

// Incluir la conexión a la base de datos
include_once '../includes/db.php';

// Configurar cabeceras para respuesta JSON
header('Content-Type: application/json');

// Verificar si el usuario está autenticado y es administrador
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit();
}

// Verificar el método de la solicitud
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit();
}

// Verificar si se recibió un ID de libro
if (!isset($_POST['book_id']) || empty($_POST['book_id'])) {
    echo json_encode(['success' => false, 'error' => 'ID de libro no proporcionado']);
    exit();
}

// Sanitizar y validar el ID del libro
$book_id = filter_input(INPUT_POST, 'book_id', FILTER_VALIDATE_INT);
if ($book_id === false || $book_id < 1) {
    echo json_encode(['success' => false, 'error' => 'ID de libro no válido']);
    exit();
}

try {
    // Preparar la consulta para obtener los detalles del libro
    $sql = "SELECT id, titulo, autor, descripcion, categoria, precio, imagen FROM libros WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception('Error al preparar la consulta: ' . $conn->error);
    }
    
    $stmt->bind_param("i", $book_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Error al ejecutar la consulta: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Libro no encontrado']);
        exit();
    }
    
    $libro = $result->fetch_assoc();
    
    // Formatear los datos para la respuesta
    $response = [
        'success' => true,
        'libro' => [
            'id' => $libro['id'],
            'titulo' => $libro['titulo'],
            'autor' => $libro['autor'],
            'descripcion' => $libro['descripcion'],
            'categoria' => $libro['categoria'],
            'precio' => number_format($libro['precio'], 2, '.', ''),
            'imagen' => $libro['imagen'] ?? 'default-book.jpg'
        ]
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    // Cerrar conexiones
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}
?>