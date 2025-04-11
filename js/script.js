document.addEventListener('DOMContentLoaded', function() {
    // Funcionalidad de toggle para la barra lateral
    const toggleMenuButton = document.querySelector('.toggle-menu');
    const adminWrapper = document.querySelector('.admin-wrapper');
    const adminSidebar = document.getElementById('admin_sidebar');
    
    if (toggleMenuButton && adminWrapper) {
        toggleMenuButton.addEventListener('click', function() {
            console.log('Toggle button clicked');
            adminWrapper.classList.toggle('sidebar-collapsed');
            
            if (adminSidebar) {
                adminSidebar.style.display = 'none';
                setTimeout(() => {
                    adminSidebar.style.display = '';
                }, 10);
            }
        });
    }
    
    // Manejo del menú desplegable de administrador
    const dropdownToggle = document.querySelector('.admin-dropdown-toggle');
    if (dropdownToggle) {
        dropdownToggle.addEventListener('click', function(e) {
            e.preventDefault();
            toggleDropdown();
        });
    }
    
    // Función para alternar la visibilidad del menú desplegable
    function toggleDropdown() {
        const menu = document.getElementById('admin-dropdown-menu');
        const dropdown = document.querySelector('.admin-dropdown');
        if (menu && dropdown) {
            menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
            dropdown.classList.toggle('open');
        }
    }
    
    // Cerrar el menú si el usuario hace clic fuera de él
    window.addEventListener('click', function(event) {
        const menu = document.getElementById('admin-dropdown-menu');
        const dropdown = document.querySelector('.admin-dropdown');
        if (menu && dropdown && !dropdown.contains(event.target)) {
            menu.style.display = 'none';
            dropdown.classList.remove('open');
        }
    });
    
    // Gestión de mensajes flash (éxito/error)
    function manejarMensajes() {
        const message = document.getElementById('message');
        const error = document.getElementById('error');
        
        // Mostrar mensaje de éxito si existe
        if (message && message.innerHTML.trim() !== '') {
            message.style.display = 'block';
        }
        
        // Mostrar mensaje de error si existe
        if (error && error.innerHTML.trim() !== '') {
            error.style.display = 'block';
        }
        
        // Ocultar los mensajes después de 5 segundos
        setTimeout(function() {
            if (message) {
                message.style.display = 'none';
            }
            
            if (error) {
                error.style.display = 'none';
            }
        }, 5000);
    }
    
    // Llamar al gestor de mensajes al cargar la página
    manejarMensajes();
    
    // Funcionalidad del modal
    const modal = document.getElementById("modalEditar");
    const botonesEditar = document.querySelectorAll('.btn-edit');
    const btnCerrar = document.querySelector(".cerrar");
    const formEditar = document.getElementById("formEditarLibro");
    
    // Crear un endpoint para obtener los detalles del libro
    async function obtenerDetallesLibro(id) {
        try {
            const response = await fetch(`../php/get_book_details.php?id=${id}`);
            if (!response.ok) {
                throw new Error('Error al obtener los detalles del libro');
            }
            return await response.json();
        } catch (error) {
            console.error('Error:', error);
            return null;
        }
    }
    
    // Abrir modal con datos del libro cuando se hace clic en el botón de editar
    botonesEditar.forEach(function(boton) {
        boton.onclick = async function(event) {
            event.preventDefault();
            const libroId = this.getAttribute('data-id');
            
            // Obtener datos completos del libro mediante AJAX
            const libro = await obtenerDetallesLibro(libroId);
            
            if (libro) {
                // Establecer los valores en el formulario
                document.getElementById("edit_book_id").value = libro.id;
                formEditar.querySelector('[name="titulo"]').value = libro.titulo;
                formEditar.querySelector('[name="autor"]').value = libro.autor;
                formEditar.querySelector('[name="descripcion"]').value = libro.descripcion;
                formEditar.querySelector('[name="categoria"]').value = libro.categoria;
                formEditar.querySelector('[name="precio"]').value = libro.precio;
                
                // Mostrar el modal
                modal.style.display = "block";
            } else {
                alert('No se pudieron cargar los detalles del libro. Por favor, inténtalo de nuevo.');
            }
        };
    });
    
    // Cerrar modal al hacer clic en el botón X
    if (btnCerrar) {
        btnCerrar.onclick = function() {
            modal.style.display = "none";
        };
    }
    
    // Cerrar modal al hacer clic fuera de él
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    };
    
    // Confirmación de eliminación
    const botonesEliminar = document.querySelectorAll('.btn-delete');
    botonesEliminar.forEach(function(boton) {
        boton.onclick = function(event) {
            if (!confirm("¿Está seguro de eliminar este libro?")) {
                event.preventDefault();
            }
        };
    });
});