<?php
/**
 * Admin Footer for ReadABook Admin Panel
 * 
 * @package ReadABook
 */
?>
<footer id="admin_footer">
    <div class="container">
        <div id="admin_footer_content">
            <div class="footer-section" id="admin_footer_about">
                <h3>Panel Administrativo</h3>
                <p>Sistema de administración para ReadABook, plataforma digital dedicada a la venta y distribución de libros.</p>
            </div>
            <div class="footer-section" id="admin_footer_links">
                <h3>Enlaces administrativos</h3>
                <ul>
                    <li><a href="<?php echo isset($base_path) ? $base_path : ''; ?>pages/gestionar_usuarios.php">Gestión de Usuarios</a></li>
                    <li><a href="<?php echo isset($base_path) ? $base_path : ''; ?>pages/gestionar_libros.php">Gestión de Libros</a></li>
                    <li><a href="<?php echo isset($base_path) ? $base_path : ''; ?>pages/reportes.php">Reportes</a></li>
                    <li><a href="<?php echo isset($base_path) ? $base_path : ''; ?>pages/configuracion.php">Configuración</a></li>
                </ul>
            </div>
            <div class="footer-section" id="admin_footer_contact">
                <h3>Soporte técnico</h3>
                <p>Email: <?php echo htmlspecialchars('soporte@readabook.com'); ?></p>
                <p>Teléfono: (829) 267-9095</p>
                <div id="admin_social_icons">
                    <a href="#" class="social-icon"><i class="fab fa-github"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-slack"></i></a>
                    <a href="#" class="social-icon"><i class="fas fa-headset"></i></a>
                </div>
            </div>
        </div>
        <div id="admin_copyright">
            <p>&copy; <?php echo date('Y'); ?> ReadABook | Panel de Administración | Todos los derechos reservados.</p>
        </div>
    </div>
</footer>