<?php
// Total de pedidos en los últimos 30 días
$stmt = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE fecha_pedido > NOW() - INTERVAL 30 DAY");
$totalPedidos = $stmt->fetchColumn();

// Ingresos en los últimos 30 días (precio * cantidad)
$stmt = $pdo->query("
    SELECT SUM(l.precio * p.cantidad) AS ingresos
    FROM pedidos p
    JOIN libros l ON p.libro_id = l.id
    WHERE p.fecha_pedido > NOW() - INTERVAL 30 DAY
");
$ingresosMensuales = $stmt->fetchColumn();
$ingresosMensuales = $ingresosMensuales ?? 0;

// Datos de ventas por categoría para el gráfico (últimos 30 días)
$stmt = $pdo->query("
    SELECT l.categoria, SUM(p.cantidad) AS cantidad_vendida
    FROM pedidos p
    JOIN libros l ON p.libro_id = l.id
    WHERE p.fecha_pedido > NOW() - INTERVAL 30 DAY
    GROUP BY l.categoria
");
$datosPorCategoria = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Total de usuarios activos
$stmt = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE estado = 'activo'");
$totalUsuarios = $stmt->fetchColumn();

// Total de usuarios inactivos
$stmt = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE estado = 'inactivo'");
$totalUsuariosInactivos = $stmt->fetchColumn();

// Total de usuarios (todos)
$stmt = $pdo->query("SELECT COUNT(*) FROM usuarios");
$totalUsuariosTotales = $stmt->fetchColumn();

// Total de libros disponibles
$stmt = $pdo->query("SELECT COUNT(*) FROM libros WHERE estado = 'disponible'");
$totalLibros = $stmt->fetchColumn();

// Total de libros agotados
$stmt = $pdo->query("SELECT COUNT(*) FROM libros WHERE stock = 0");
$totalLibrosAgotados = $stmt->fetchColumn();

// Total de categorías de libros
$stmt = $pdo->query("SELECT COUNT(DISTINCT categoria) FROM libros");
$totalCategorias = $stmt->fetchColumn();
?>
