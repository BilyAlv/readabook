Aquí tienes el README completo para copiar y pegar:

```markdown
# Read A Book

## 📌 Descripción
Read A Book es una plataforma web CRUD para la gestión de libros y usuarios. Permite a los administradores gestionar la información de los libros y usuarios, y a los clientes explorar y suscribirse a novedades.

## 🚀 Características
- Autenticación de usuarios  
- Gestión de libros (crear, editar, eliminar)  
- Gestión de usuarios (crear, editar, eliminar)  
- Sistema de suscripción  
- Panel de administración  
- Reportes de actividad  
- **Pruebas automatizadas:** Implementación de pruebas automatizadas para la gestión de libros, usuarios, y autenticación.

## 🛠️ Tecnologías Utilizadas
- **Backend:** PHP con MySQL  
- **Frontend:** HTML, CSS, JavaScript  
- **Pruebas automatizadas:** Selenium con Python  
- **Control de versiones:** Git y GitHub  
- **Entorno de desarrollo:** Laragon  

## 🔑 Credenciales de Acceso

### **Administrador**
- **Email:** admin@readabook.com  
- **Contraseña:** `1234`  
- **Rol:** Admin  

### **Usuarios**
- **Email:** usuario@readabook.com  
- **Contraseña:** `1234`  
- **Rol:** Usuario  

## 📝 Instalación y Configuración
1. Clonar el repositorio:
   ```bash
   git clone https://github.com/BilyAlv/Tarea-03-Proyecto-Readabook.git
   ```
2. Importar la base de datos desde el archivo `db/db.sql` o `db/db 2.sql` a tu servidor MySQL.
3. Configurar el archivo `includes/db.php` con tus credenciales de base de datos.
4. Abrir el proyecto en tu servidor local (por ejemplo, Laragon) y accede a `index.php` para empezar.
5. Para ejecutar las pruebas automatizadas, sigue las instrucciones en la carpeta `readabook-tests`.

## 🧑‍💻 Estructura del Proyecto

```plaintext
readabook
├── admin
├── cliente
├── css
├── db
├── img
├── includes
├── js
└── php

readabook-tests
├── test_gestion_libros.py
├── test_gestion_usuarios.py
├── test_login.py
├── test_reportes.py
└── reportes
login.php
logout.php
README.md
```

## 📊 **Pruebas Automatizadas**

Se han implementado pruebas automatizadas para asegurar la correcta funcionalidad de las siguientes características de la aplicación:

- **Gestión de Libros**: Agregar, editar, eliminar libros, y vista previa de imágenes.
- **Gestión de Usuarios**: Agregar, editar, eliminar usuarios.
- **Autenticación**: Inicios de sesión exitosos y fallidos.
- **Reportes**: Acceso y verificación de secciones en reportes.

### **Para ejecutar las pruebas automatizadas:**
1. Asegúrate de tener Python y Selenium instalados.
2. Ejecuta las pruebas desde la carpeta `readabook-tests`.
   ```bash
   python -m unittest discover readabook-tests
   ```

## 🔧 Recursos Adicionales
- Para más detalles sobre el proyecto, consulta el código en GitHub: [BilyAlv/Tarea-03-Proyecto-Readabook](https://github.com/BilyAlv/Tarea-03-Proyecto-Readabook).
```

