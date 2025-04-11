<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Read A Book - Catálogo de Libros</title>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        /* Estilos principales */
        :root {
            --primary-color: #1d3557;
            --secondary-color: #457b9d;
            --accent-color: #e63946;
            --light-color: #f1faee;
            --dark-color: #0d1b2a;
            --success-color: #2a9d8f;
            --warning-color: #e9c46a;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f5f5;
            color: var(--dark-color);
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px 0;
        }

        /* Header y navegación */
        header {
            background-color: var(--primary-color);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 1rem;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: bold;
            display: flex;
            align-items: center;
        }

        .logo i {
            margin-right: 10px;
        }

        .nav-links {
            display: flex;
            list-style: none;
        }

        .nav-links li {
            margin-left: 20px;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .nav-links a:hover {
            color: var(--warning-color);
        }

        .user-actions {
            display: flex;
            align-items: center;
        }

        .user-actions .btn {
            margin-left: 10px;
        }

        /* Botones */
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: var(--secondary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: #366b89;
        }

        .btn-danger {
            background-color: var(--accent-color);
            color: white;
        }

        .btn-danger:hover {
            background-color: #d42836;
        }

        .btn-success {
            background-color: var(--success-color);
            color: white;
        }

        .btn-success:hover {
            background-color: #238b7e;
        }

        /* Hero Section */
        .hero {
            background-image: linear-gradient(rgba(29, 53, 87, 0.8), rgba(29, 53, 87, 0.9)), url('img/library-bg.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            text-align: center;
            padding: 3rem 0;
        }

        .hero h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .hero p {
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto 1.5rem;
        }

        /* Sección de catálogo */
        .section-title {
            text-align: center;
            margin: 2rem 0;
            font-size: 1.8rem;
            color: var(--primary-color);
        }

        .book-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .book-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .book-image {
            height: 300px;
            overflow: hidden;
        }

        .book-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .book-details {
            padding: 15px;
        }

        .book-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--primary-color);
        }

        .book-author {
            color: var(--secondary-color);
            margin-bottom: 10px;
        }

        .book-description {
            font-size: 0.9rem;
            margin-bottom: 10px;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .book-actions {
            margin-top: 10px;
            display: flex;
            justify-content: space-between;
        }

        /* Formularios */
        .form-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin: 20px auto;
            max-width: 500px;
        }

        .form-title {
            text-align: center;
            margin-bottom: 20px;
            color: var(--primary-color);
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: var(--dark-color);
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 2px rgba(69, 123, 157, 0.2);
        }

        .alert {
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        /* Modal */
        .modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            display: none;
        }

        .modal {
            background-color: white;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            background-color: var(--primary-color);
            color: white;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-size: 1.2rem;
            font-weight: 600;
        }

        .modal-close {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
        }

        .modal-body {
            padding: 20px;
        }

        .modal-footer {
            padding: 15px;
            background-color: #f8f9fa;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        /* Footer */
        footer {
            background-color: var(--primary-color);
            color: white;
            padding: 2rem 0;
            margin-top: 2rem;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .footer-section {
            flex: 1;
            min-width: 250px;
            margin: 15px;
        }

        .footer-section h3 {
            font-size: 1.2rem;
            margin-bottom: 15px;
            color: var(--light-color);
        }

        .footer-section p {
            margin-bottom: 10px;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 20px;
            margin-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Suscripción */
        .subscription-form {
            display: flex;
            margin-top: 10px;
        }

        .subscription-form input {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 4px 0 0 4px;
        }

        .subscription-form button {
            padding: 10px 15px;
            background-color: var(--accent-color);
            color: white;
            border: none;
            border-radius: 0 4px 4px 0;
            cursor: pointer;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }

            .mobile-menu-btn {
                display: block;
            }

            .book-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }

            .footer-content {
                flex-direction: column;
            }

            .footer-section {
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Header con navegación -->
    <header>
        <nav>
            <div class="logo">
                <i class="fas fa-book-open"></i> Read A Book
            </div>
            <ul class="nav-links">
                <li><a href="index.php">Inicio</a></li>
                <li><a href="catalogo.php">Catálogo</a></li>
                <li><a href="novedades.php">Novedades</a></li>
                <li><a href="contacto.php">Contacto</a></li>
            </ul>
            <div class="user-actions">
                <a href="login.php" class="btn btn-primary"><i class="fas fa-sign-in-alt"></i> Iniciar Sesión</a>
            </div>
        </nav>
    </header>

    <!-- Hero section -->
    <section class="hero">
        <div class="container">
            <h1>Descubre mundos a través de la lectura</h1>
            <p>Explora nuestra colección de libros cuidadosamente seleccionados para todos los gustos y edades.</p>
            <a href="catalogo.php" class="btn btn-success">Ver Catálogo</a>
        </div>
    </section>

    <!-- Catálogo de libros -->
    <section class="container">
        <h2 class="section-title">Destacados del mes</h2>
        <div class="book-grid">
            <!-- Libro 1 -->
            <div class="book-card">
                <div class="book-image">
                    <img src="img/libro1.jpg" alt="Portada del libro">
                </div>
                <div class="book-details">
                    <h3 class="book-title">Cien años de soledad</h3>
                    <p class="book-author">Gabriel García Márquez</p>
                    <p class="book-description">La obra cumbre del realismo mágico que narra la historia de la familia Buendía a lo largo de siete generaciones en el pueblo ficticio de Macondo.</p>
                    <div class="book-actions">
                        <button class="btn btn-primary" onclick="openBookDetail(1)">Ver detalles</button>
                    </div>
                </div>
            </div>
            
            <!-- Libro 2 -->
            <div class="book-card">
                <div class="book-image">
                    <img src="img/libro2.jpg" alt="Portada del libro">
                </div>
                <div class="book-details">
                    <h3 class="book-title">1984</h3>
                    <p class="book-author">George Orwell</p>
                    <p class="book-description">Una novela distópica que presenta una sociedad totalitaria vigilada por el Gran Hermano, donde se controla hasta el pensamiento.</p>
                    <div class="book-actions">
                        <button class="btn btn-primary" onclick="openBookDetail(2)">Ver detalles</button>
                    </div>
                </div>
            </div>
            
            <!-- Libro 3 -->
            <div class="book-card">
                <div class="book-image">
                    <img src="img/libro3.jpg" alt="Portada del libro">
                </div>
                <div class="book-details">
                    <h3 class="book-title">El nombre del viento</h3>
                    <p class="book-author">Patrick Rothfuss</p>
                    <p class="book-description">Primera parte de la trilogía Crónica del asesino de reyes, narra la historia del músico, ladrón y mago Kvothe.</p>
                    <div class="book-actions">
                        <button class="btn btn-primary" onclick="openBookDetail(3)">Ver detalles</button>
                    </div>
                </div>
            </div>
            
            <!-- Libro 4 -->
            <div class="book-card">
                <div class="book-image">
                    <img src="img/libro4.jpg" alt="Portada del libro">
                </div>
                <div class="book-details">
                    <h3 class="book-title">Orgullo y prejuicio</h3>
                    <p class="book-author">Jane Austen</p>
                    <p class="book-description">Una novela romántica que explora temas como el orgullo, los prejuicios, el matrimonio y las clases sociales en la Inglaterra rural del siglo XIX.</p>
                    <div class="book-actions">
                        <button class="btn btn-primary" onclick="openBookDetail(4)">Ver detalles</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal de detalles del libro -->
    <div class="modal-backdrop" id="bookDetailModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Detalles del libro</h3>
                <button class="modal-close" onclick="closeBookDetail()">&times;</button>
            </div>
            <div class="modal-body" id="bookDetailContent">
                <!-- Contenido cargado dinámicamente -->
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" onclick="closeBookDetail()">Cerrar</button>
            </div>
        </div>
    </div>

    <!-- Sección de suscripción -->
    <section class="container">
        <div class="form-container">
            <h2 class="form-title">Suscríbete a nuestras novedades</h2>
            <p style="text-align: center; margin-bottom: 20px;">Recibe información sobre nuevos libros y eventos.</p>
            
            <form id="subscriptionForm">
                <div class="form-group">
                    <label for="email">Correo electrónico</label>
                    <input type="email" id="email" class="form-control" placeholder="tu@email.com" required>
                </div>
                <div class="form-group">
                    <label for="interests">Intereses</label>
                    <select id="interests" class="form-control">
                        <option value="all">Todos los géneros</option>
                        <option value="fiction">Ficción</option>
                        <option value="nonfiction">No ficción</option>
                        <option value="scifi">Ciencia ficción</option>
                        <option value="fantasy">Fantasía</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-success" style="width: 100%;">Suscribirse</button>
            </form>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Acerca de Read A Book</h3>
                    <p>Somos una plataforma dedicada a los amantes de la lectura, ofreciendo una amplia selección de libros de diversos géneros.</p>
                </div>
                <div class="footer-section">
                    <h3>Enlaces rápidos</h3>
                    <p><a href="index.php" style="color: white;">Inicio</a></p>
                    <p><a href="catalogo.php" style="color: white;">Catálogo</a></p>
                    <p><a href="novedades.php" style="color: white;">Novedades</a></p>
                    <p><a href="contacto.php" style="color: white;">Contacto</a></p>
                </div>
                <div class="footer-section">
                    <h3>Contacto</h3>
                    <p><i class="fas fa-envelope"></i> info@readabook.com</p>
                    <p><i class="fas fa-phone"></i> +1 234 567 890</p>
                    <p><i class="fas fa-map-marker-alt"></i> Calle Ficticia 123, Ciudad</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Read A Book. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
        // Función para abrir el modal con detalles del libro
        function openBookDetail(bookId) {
            // En una aplicación real, aquí se haría una petición AJAX para obtener los datos del libro
            // Por simplicidad, simularemos datos estáticos
            
            let bookDetails = {
                1: {
                    title: "Cien años de soledad",
                    author: "Gabriel García Márquez",
                    year: 1967,
                    genre: "Realismo mágico",
                    description: "La obra cumbre del realismo mágico que narra la historia de la familia Buendía a lo largo de siete generaciones en el pueblo ficticio de Macondo. Una exploración profunda de la soledad, el amor y la guerra a través de múltiples generaciones.",
                    isbn: "978-0307474728",
                    pages: 417
                },
                2: {
                    title: "1984",
                    author: "George Orwell",
                    year: 1949,
                    genre: "Distopía",
                    description: "Una novela distópica que presenta una sociedad totalitaria vigilada por el Gran Hermano, donde se controla hasta el pensamiento. Winston Smith lucha por mantener su identidad en un mundo donde la libertad individual ha sido erradicada.",
                    isbn: "978-0451524935",
                    pages: 328
                },
                3: {
                    title: "El nombre del viento",
                    author: "Patrick Rothfuss",
                    year: 2007,
                    genre: "Fantasía épica",
                    description: "Primera parte de la trilogía Crónica del asesino de reyes, narra la historia del músico, ladrón y mago Kvothe. Una historia épica relatada en primera persona por el protagonista, quien revela su verdadera historia a un cronista.",
                    isbn: "978-8401352836",
                    pages: 662
                },
                4: {
                    title: "Orgullo y prejuicio",
                    author: "Jane Austen",
                    year: 1813,
                    genre: "Novela romántica",
                    description: "Una novela romántica que explora temas como el orgullo, los prejuicios, el matrimonio y las clases sociales en la Inglaterra rural del siglo XIX. La historia sigue a Elizabeth Bennet mientras lidia con problemas de modales, educación, moral y matrimonio en la sociedad aristocrática de la Inglaterra georgiana.",
                    isbn: "978-0141439518",
                    pages: 432
                }
            };
            
            let book = bookDetails[bookId];
            
            if (book) {
                let content = `
                    <h2 style="margin-bottom: 10px; color: var(--primary-color);">${book.title}</h2>
                    <p style="color: var(--secondary-color); font-size: 1.1rem; margin-bottom: 15px;"><strong>Autor:</strong> ${book.author}</p>
                    <div style="margin-bottom: 15px;">
                        <p><strong>Año:</strong> ${book.year}</p>
                        <p><strong>Género:</strong> ${book.genre}</p>
                        <p><strong>ISBN:</strong> ${book.isbn}</p>
                        <p><strong>Páginas:</strong> ${book.pages}</p>
                    </div>
                    <h3 style="margin-bottom: 10px; color: var(--primary-color);">Descripción</h3>
                    <p style="line-height: 1.6;">${book.description}</p>
                `;
                
                document.getElementById('bookDetailContent').innerHTML = content;
                document.getElementById('bookDetailModal').style.display = 'flex';
            }
        }
        
        // Función para cerrar el modal
        function closeBookDetail() {
            document.getElementById('bookDetailModal').style.display = 'none';
        }
        
        // Manejo del formulario de suscripción
        document.getElementById('subscriptionForm').addEventListener('submit', function(event) {
            event.preventDefault();
            
            let email = document.getElementById('email').value;
            let interests = document.getElementById('interests').value;
            
            // En una aplicación real, aquí enviaríamos los datos al servidor
            // Por ahora, mostraremos un mensaje de confirmación
            
            alert(`¡Gracias por suscribirte! Recibirás actualizaciones sobre ${interests === 'all' ? 'todos los géneros' : interests} en ${email}`);
            
            // Reiniciar el formulario
            this.reset();
        });
        
        // Cerrar el modal si se hace clic fuera de él
        window.onclick = function(event) {
            let modal = document.getElementById('bookDetailModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        };
    </script>
</body>
</html>