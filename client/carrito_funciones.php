<?php
session_start();
require_once "../includes/db.php";

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit();
}

// Inicializar carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Función para añadir un libro al carrito
function agregarAlCarrito($libro_id, $cantidad) {
    global $conn;
    
    // Verificar que el libro existe y tiene stock suficiente
    $sql = "SELECT id, titulo, precio, stock FROM libros WHERE id = ? AND stock >= ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $libro_id, $cantidad);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return ['success' => false, 'error' => 'Libro no disponible o stock insuficiente'];
    }
    
    $libro = $result->fetch_assoc();
    
    // Verificar si el libro ya está en el carrito
    $libro_encontrado = false;
    foreach ($_SESSION['carrito'] as &$item) {
        if ($item['id'] == $libro_id) {
            // Verificar que no exceda el stock disponible
            $nueva_cantidad = $item['cantidad'] + $cantidad;
            if ($nueva_cantidad > $libro['stock']) {
                return ['success' => false, 'error' => 'No hay suficiente stock disponible'];
            }
            $item['cantidad'] = $nueva_cantidad;
            $libro_encontrado = true;
            break;
        }
    }
    
    // Si el libro no está en el carrito, añadirlo
    if (!$libro_encontrado) {
        $_SESSION['carrito'][] = [
            'id' => $libro['id'],
            'titulo' => $libro['titulo'],
            'precio' => $libro['precio'],
            'cantidad' => $cantidad
        ];
    }
    
    return [
        'success' => true, 
        'mensaje' => 'Libro añadido al carrito', 
        'total_items' => count($_SESSION['carrito'])
    ];
}

// Función para actualizar la cantidad de un libro en el carrito
function actualizarCantidad($libro_id, $cantidad) {
    global $conn;
    
    // Verificar que el libro existe y tiene stock suficiente
    $sql = "SELECT stock FROM libros WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $libro_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return ['success' => false, 'error' => 'Libro no encontrado'];
    }
    
    $libro = $result->fetch_assoc();
    
    if ($cantidad > $libro['stock']) {
        return ['success' => false, 'error' => 'No hay suficiente stock disponible'];
    }
    
    // Actualizar cantidad en el carrito
    foreach ($_SESSION['carrito'] as &$item) {
        if ($item['id'] == $libro_id) {
            $item['cantidad'] = $cantidad;
            break;
        }
    }
    
    return ['success' => true, 'mensaje' => 'Cantidad actualizada'];
}

// Función para eliminar un libro del carrito
function eliminarDelCarrito($libro_id) {
    foreach ($_SESSION['carrito'] as $key => $item) {
        if ($item['id'] == $libro_id) {
            unset($_SESSION['carrito'][$key]);
            $_SESSION['carrito'] = array_values($_SESSION['carrito']); // Reindexar array
            break;
        }
    }
    
    return [
        'success' => true, 
        'mensaje' => 'Libro eliminado del carrito',
        'total_items' => count($_SESSION['carrito'])
    ];
}

// Función para obtener el contenido del carrito
function obtenerCarrito() {
    global $conn;
    $carrito = [];
    $total = 0;
    
    foreach ($_SESSION['carrito'] as $item) {
        // Verificar que el libro siga existiendo y su stock actual
        $sql = "SELECT titulo, precio, stock, imagen FROM libros WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $item['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $libro = $result->fetch_assoc();
            
            // Si el stock actual es menor que la cantidad en el carrito, ajustar
            $cantidad = ($item['cantidad'] > $libro['stock']) ? $libro['stock'] : $item['cantidad'];
            
            $subtotal = $libro['precio'] * $cantidad;
            $total += $subtotal;
            
            $carrito[] = [
                'id' => $item['id'],
                'titulo' => $libro['titulo'],
                'precio' => $libro['precio'],
                'cantidad' => $cantidad,
                'subtotal' => $subtotal,
                'stock' => $libro['stock'],
                'imagen' => $libro['imagen']
            ];
        }
    }
    
    return [
        'items' => $carrito,
        'total' => $total,
        'total_items' => count($carrito)
    ];
}

// Procesar solicitudes AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    
    switch ($accion) {
        case 'agregar':
            if (isset($_POST['id']) && isset($_POST['cantidad']) && is_numeric($_POST['id']) && is_numeric($_POST['cantidad'])) {
                $resultado = agregarAlCarrito($_POST['id'], $_POST['cantidad']);
                header('Content-Type: application/json');
                echo json_encode($resultado);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Parámetros inválidos']);
            }
            break;
            
        case 'actualizar':
            if (isset($_POST['id']) && isset($_POST['cantidad']) && is_numeric($_POST['id']) && is_numeric($_POST['cantidad'])) {
                $resultado = actualizarCantidad($_POST['id'], $_POST['cantidad']);
                
                // Si la actualización fue exitosa, devolver el carrito actualizado
                if ($resultado['success']) {
                    $carrito = obtenerCarrito();
                    $resultado = array_merge($resultado, $carrito);
                }
                
                header('Content-Type: application/json');
                echo json_encode($resultado);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Parámetros inválidos']);
            }
            break;
            
        case 'eliminar':
            if (isset($_POST['id']) && is_numeric($_POST['id'])) {
                $resultado = eliminarDelCarrito($_POST['id']);
                
                // Devolver el carrito actualizado
                $carrito = obtenerCarrito();
                $resultado = array_merge($resultado, $carrito);
                
                header('Content-Type: application/json');
                echo json_encode($resultado);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Parámetros inválidos']);
            }
            break;
            
        case 'obtener':
            $carrito = obtenerCarrito();
            header('Content-Type: application/json');
            echo json_encode($carrito);
            break;
            
        default:
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Acción no válida']);
            break;
    }
}
?>