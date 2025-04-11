<?php
// Database connection parameters
$host = 'localhost';
$db = 'read_a_book';
$user = 'root';
$pass = 'admin';
$charset = 'utf8mb4';

// PDO connection
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
    $pdo = null;
}

// For legacy code that uses mysqli
$servername = $host;
$username = $user;
$password = $pass;
$dbname = $db;

// Create mysqli connection for backward compatibility
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set character set
$conn->set_charset($charset);
?>