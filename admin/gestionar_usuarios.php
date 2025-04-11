<?php
session_start();
include_once '../includes/db.php';
include_once '../php/logica-index.php';

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if (isset($_POST['add_user'])) {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    ;$password = $_POST['password'];
    $rol = $_POST['rol'];
    
    $sql = "INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $nombre, $email, $password, $rol);
    
    if ($stmt->execute()) {
        $message = "Usuario agregado exitosamente";
    } else {
        $error = "Error al agregar el usuario: " . $conn->error;
    }
    $stmt->close();
}

if (isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];

    if ($user_id == $_SESSION['id']) {
        $error = "No puedes eliminar tu propia cuenta de administrador";
    } else {
        $sql = "DELETE FROM usuarios WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            $message = "Usuario eliminado exitosamente";
        } else {
            $error = "Error al eliminar el usuario: " . $conn->error;
        }
        $stmt->close();
    }
}

$sql = "SELECT id, nombre, email, rol FROM usuarios ORDER BY id DESC";
$result = $conn->query($sql);

// Verificar si la consulta fue exitosa
if ($result === false) {
    die('Error al ejecutar la consulta: ' . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Usuarios - Read a Book</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/gestion_usuarios.css">
    <link rel="stylesheet" href="../css/admin.css">
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
</head>
<body>
    <?php include '../includes/admin_header.php'; ?>

    <div class="admin-wrapper">
        <div class="admin-content">
            <div class="admin-container">
                <div class="user-management-container">
                    <h1 class="user-management-title">Gestión de Usuarios</h1>

                    <?php if (isset($message)): ?>
                        <div class="message">
                            <i class="fas fa-check-circle"></i> <?php echo $message; ?>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($error)): ?>
                        <div class="error">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <div class="user-filters">
                        <div class="search-users">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchUser" placeholder="Buscar usuarios...">
                        </div>
                        <div class="filter-group">
                            <label for="filterRole">Rol:</label>
                            <select id="filterRole">
                                <option value="all">Todos</option>
                                <option value="admin">Administrador</option>
                                <option value="editor">Editor</option>
                                <option value="usuario">Usuario</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-container">
                        <h2>Agregar Nuevo Usuario</h2>
                        <form method="POST">
                            <div class="user-form">
                                <div class="form-group">
                                    <label for="nombre">Nombre:</label>
                                    <input type="text" id="nombre" name="nombre" required>
                                </div>
                                <div class="form-group">
                                    <label for="email">Correo electrónico:</label>
                                    <input type="email" id="email" name="email" required>
                                </div>
                                <div class="form-group">
                                    <label for="password">Contraseña:</label>
                                    <input type="password" id="password" name="password" required>
                                </div>
                                <div class="form-group">
                                    <label for="rol">Rol:</label>
                                    <select id="rol" name="rol" required>
                                        <option value="admin">Administrador</option>
                                        <option value="editor">Editor</option>
                                        <option value="usuario">Usuario</option>
                                    </select>
                                </div>
                            </div>
                            <div class="user-actions">
                                <button type="submit" name="add_user" class="btn-add-user">
                                    <i class="fas fa-user-plus"></i> Agregar Usuario
                                </button>
                                <button type="reset" class="btn-reset">
                                    <i class="fas fa-undo"></i> Limpiar
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="users-table-container">
                        <h2>Lista de Usuarios</h2>
                        <table class="users-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Rol</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($result && $result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        $rolClass = '';
                                        switch ($row['rol']) {
                                            case 'admin':
                                                $rolClass = 'status-active'; break;
                                            case 'editor':
                                                $rolClass = 'status-pending'; break;
                                            default:
                                                $rolClass = '';
                                        }
                                        echo "<tr>
                                                <td>{$row['id']}</td>
                                                <td>{$row['nombre']}</td>
                                                <td>{$row['email']}</td>
                                                <td><span class='user-status {$rolClass}'>{$row['rol']}</span></td>
                                                <td class='user-table-actions'>
                                                    <a href='editar_usuario.php?id={$row['id']}' class='btn-edit'>
                                                        <i class='fas fa-edit'></i> Editar
                                                    </a>
                                                    <form method='POST' onsubmit=\"return confirm('¿Estás seguro de que deseas eliminar este usuario?');\">
                                                        <input type='hidden' name='user_id' value='{$row['id']}'>
                                                        <button type='submit' name='delete_user' class='btn-delete'>
                                                            <i class='fas fa-trash-alt'></i> Eliminar
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='5' class='no-data'>No hay usuarios registrados</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="user-pagination">
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
        const searchInput = document.getElementById('searchUser');
        const filterRole = document.getElementById('filterRole');
        const userRows = document.querySelectorAll('.users-table tbody tr');

        function filterUsers() {
            const searchValue = searchInput.value.toLowerCase();
            const roleValue = filterRole.value;

            userRows.forEach(row => {
                const name = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                const email = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                const role = row.querySelector('td:nth-child(4)').textContent.toLowerCase();

                const matchesSearch = name.includes(searchValue) || email.includes(searchValue);
                const matchesRole = roleValue === 'all' || role === roleValue;

                row.style.display = (matchesSearch && matchesRole) ? '' : 'none';
            });
        }

        searchInput.addEventListener('input', filterUsers);
        filterRole.addEventListener('change', filterUsers);

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
