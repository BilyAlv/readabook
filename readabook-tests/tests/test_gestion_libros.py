import os
import pytest
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.common.alert import Alert
from selenium.webdriver.common.action_chains import ActionChains
import time
import random
import string

# Ruta donde se guardarán las capturas de pantalla
REPORTS_PATH = r"C:\laragon\www\readabook\readabook-tests\reports\test_gestion-libros"

# Asegúrate de que la ruta exista
if not os.path.exists(REPORTS_PATH):
    os.makedirs(REPORTS_PATH)

@pytest.fixture
def setup():
    driver = webdriver.Chrome()
    driver.get("http://localhost/readabook/login.php")
    driver.maximize_window()  # Abrir el navegador en pantalla completa
    yield driver
    driver.quit()

# Función para desplazarse a la tabla de libros
def scroll_to_books_table(driver):
    """
    Desplaza la vista hasta la tabla de libros
    """
    try:
        # Esperar a que la tabla esté presente
        table = WebDriverWait(driver, 10).until(
            EC.visibility_of_element_located((By.CLASS_NAME, "books-table"))
        )
        # Desplazar hasta la tabla
        driver.execute_script("arguments[0].scrollIntoView(true);", table)
        # Pequeña pausa para permitir que el desplazamiento termine
        driver.execute_script("window.scrollBy(0, -100);")  # Ajustar para mejorar la visibilidad
    except Exception as e:
        print(f"Error al desplazarse a la tabla: {e}")

# Función para tomar capturas de pantalla antes y después de cada test
def take_screenshot(driver, test_name, stage):

    try:
        # Si necesitamos capturar la tabla, desplazar hasta ella
        if "table" in stage:
            scroll_to_books_table(driver)
        
        filename = f"{stage}_{test_name}.png"
        filepath = os.path.join(REPORTS_PATH, filename)
        driver.save_screenshot(filepath)
        print(f"Captura guardada: {filepath}")
    except Exception as e:
        print(f"Error al tomar captura {stage} {test_name}: {e}")

def login_as_admin(driver):
    """
    Inicia sesión como administrador y navega a la página de gestión de libros
    """
    email = "pruebas@example.com"
    password = "19284637839"
    WebDriverWait(driver, 10).until(EC.visibility_of_element_located((By.ID, "email")))

    # Iniciar sesión como administrador
    driver.find_element(By.ID, "email").send_keys(email)
    driver.find_element(By.ID, "password").send_keys(password)
    driver.find_element(By.XPATH, "//button[@type='submit']").click()
    
    WebDriverWait(driver, 10).until(EC.url_contains("admin/index.php"))
    WebDriverWait(driver, 10).until(EC.visibility_of_element_located((By.XPATH, "//a[contains(@href, 'gestionar_libros.php')]")))

    driver.find_element(By.XPATH, "//a[contains(@href, 'gestionar_libros.php')]").click()
    WebDriverWait(driver, 10).until(EC.url_contains("gestionar_libros.php"))

def generate_random_string(length=10):
    """
    Genera una cadena aleatoria de caracteres
    """
    return ''.join(random.choice(string.ascii_letters) for _ in range(length))

def test_add_book(setup):
    driver = setup
    test_name = "test_add_book"
    try:
        login_as_admin(driver)
        WebDriverWait(driver, 10).until(EC.visibility_of_element_located((By.CLASS_NAME, "book-form")))

        # Captura de pantalla antes de la prueba (formulario)
        take_screenshot(driver, test_name, "before_form")
        
        # Captura de la tabla antes de agregar el libro
        take_screenshot(driver, test_name, "before_table")

        # Generar datos aleatorios para el nuevo libro
        titulo = f"Libro Test {generate_random_string(5)}"
        autor = f"Autor Test {generate_random_string(5)}"
        categoria = "Ficción"
        precio = "19.99"
        stock = "10"
        descripcion = f"Esta es una descripción de prueba para {titulo}."

        # Completar formulario para agregar un libro
        driver.find_element(By.ID, "titulo").send_keys(titulo)
        driver.find_element(By.ID, "autor").send_keys(autor)
        driver.find_element(By.ID, "categoria").send_keys(categoria)
        driver.find_element(By.ID, "precio").send_keys(precio)
        driver.find_element(By.ID, "stock").send_keys(stock)
        driver.find_element(By.ID, "descripcion").send_keys(descripcion)

        # Hacer clic en el botón de agregar libro
        driver.find_element(By.NAME, "add_book").click()

        # Esperar mensaje de éxito
        WebDriverWait(driver, 10).until(EC.visibility_of_element_located((By.CLASS_NAME, "message")))

        # Verificar que el libro fue agregado
        assert "Libro agregado exitosamente" in driver.page_source

        # Verificar que el libro agregado aparece en la tabla de libros
        WebDriverWait(driver, 10).until(EC.visibility_of_element_located((By.CLASS_NAME, "books-table")))
        assert titulo in driver.page_source

        # Captura del formulario después de agregar el libro
        take_screenshot(driver, test_name, "after_form")
        
        # Captura de la tabla después de agregar el libro
        take_screenshot(driver, test_name, "after_table")
        
    except Exception as e:
        print(f"Error al agregar el libro: {e}")
        take_screenshot(driver, test_name, "error")
        assert False

def test_edit_book_page(setup):
    driver = setup
    test_name = "test_edit_book_page"
    try:
        login_as_admin(driver)
        WebDriverWait(driver, 10).until(EC.visibility_of_element_located((By.CLASS_NAME, "books-table")))
        
        # Captura de la tabla antes de editar
        take_screenshot(driver, test_name, "before_table")

        # Localizar un libro para editar (el primer libro en la lista)
        # Guardar el título original para verificaciones posteriores
        original_title = driver.find_element(By.XPATH, "(//td[2]//span)[1]").text
        print(f"Título original: {original_title}")
        
        # Hacer clic en el enlace de editar para el primer libro
        edit_link = driver.find_element(By.XPATH, "(//a[contains(@href, 'editar_libro.php?id=')])[1]")
        edit_link.click()

        # Esperar a que se cargue la página de edición
        WebDriverWait(driver, 10).until(
            EC.presence_of_element_located((By.ID, "editBookForm"))
        )
        
        # Verificar que el título del libro aparece en la página
        page_title = driver.find_element(By.CLASS_NAME, "book-management-title").text
        assert "Editar Libro:" in page_title
        print(f"Título de la página: {page_title}")

        # Captura del formulario de edición
        take_screenshot(driver, test_name, "edit_form")

        # Editar los datos del libro
        new_title = f"Libro Editado {generate_random_string(5)}"
        new_price = "29.99"
        new_stock = "15"

        # Limpiar y actualizar campos
        title_field = driver.find_element(By.ID, "titulo")
        title_field.clear()
        title_field.send_keys(new_title)
        
        price_field = driver.find_element(By.ID, "precio")
        price_field.clear()
        price_field.send_keys(new_price)
        
        stock_field = driver.find_element(By.ID, "stock")
        stock_field.clear()
        stock_field.send_keys(new_stock)

        # Captura después de modificar campos
        take_screenshot(driver, test_name, "after_edit_fields")

        # Hacer clic en el botón de actualización
        driver.find_element(By.NAME, "update_book").click()

        # Esperar la redirección a la página de gestión de libros
        WebDriverWait(driver, 10).until(EC.url_contains("gestionar_libros.php"))
      
        # Verificar que el libro actualizado aparece en la tabla
        WebDriverWait(driver, 10).until(EC.visibility_of_element_located((By.CLASS_NAME, "books-table")))
        
        # Asegurarse de que la tabla está completamente cargada
        time.sleep(1)  # Pequeña pausa para asegurar que la página se ha actualizado
        
        # Verificar que el nuevo título aparece en la tabla
        assert new_title in driver.page_source, f"El nuevo título '{new_title}' no aparece en la tabla después de editar"
        print(f"Libro actualizado exitosamente a: {new_title}")
        
        # Captura de la tabla después de la edición
        take_screenshot(driver, test_name, "after_table")

    except Exception as e:
        print(f"Error al editar el libro: {e}")
        take_screenshot(driver, test_name, "error")
        raise

def test_delete_book(setup):
    driver = setup
    test_name = "test_delete_book"
    try:
        login_as_admin(driver)
        WebDriverWait(driver, 10).until(EC.visibility_of_element_located((By.CLASS_NAME, "books-table")))

        # Captura de la tabla antes de la eliminación
        take_screenshot(driver, test_name, "before_table")
        
        # Guardar el título del libro que vamos a eliminar para comprobar después
        book_title = driver.find_element(By.XPATH, "(//td[2]//span)[1]").text

        # Localizar y hacer clic en el botón de eliminar para el primer libro
        delete_button = driver.find_element(By.XPATH, "(//button[@type='submit' and @name='delete_book'])[1]")
        delete_button.click()

        # Esperar y manejar la alerta de confirmación
        WebDriverWait(driver, 10).until(EC.alert_is_present())
        alert = Alert(driver)
        alert.accept()

        # Verificar que el mensaje de eliminación exitosa esté presente
        WebDriverWait(driver, 10).until(EC.visibility_of_element_located((By.CLASS_NAME, "message")))
        assert "Libro eliminado exitosamente" in driver.page_source

        # Captura de la tabla después de la eliminación
        take_screenshot(driver, test_name, "after_table")
        
        # Verificar que el libro eliminado ya no aparece en la tabla
        time.sleep(1)  # Pequeña pausa para asegurar que la página se ha actualizado
        page_source = driver.page_source
        
        # Si el libro sigue apareciendo, intentamos buscar explícitamente
        if book_title in page_source:
            search_input = driver.find_element(By.ID, "searchBook")
            search_input.clear()
            search_input.send_keys(book_title)
            time.sleep(1)  # Esperar a que se aplique el filtro
            
            # Comprobar si hay resultados exactos del libro eliminado
            elements = driver.find_elements(By.XPATH, f"//td[2]//span[text()='{book_title}']")
            assert len(elements) == 0, f"El libro '{book_title}' sigue apareciendo en la tabla después de eliminarlo"

    except Exception as e:
        print(f"Error al eliminar el libro: {e}")
        take_screenshot(driver, test_name, "error")
        assert False

def test_image_preview(setup):
    driver = setup
    test_name = "test_image_preview"
    try:
        login_as_admin(driver)
        WebDriverWait(driver, 10).until(EC.visibility_of_element_located((By.CLASS_NAME, "book-form")))
        
        # Captura antes de subir imagen
        take_screenshot(driver, test_name, "before_upload")
        
        # Preparar archivo de imagen para subir
        image_path = os.path.join(os.path.dirname(__file__), "test_image.jpg")
        
        # Si el archivo no existe, creamos un mensaje de error pero no detenemos el test
        if not os.path.exists(image_path):
            print(f"ADVERTENCIA: Archivo de imagen de prueba no encontrado en {image_path}")
            print("El test continuará pero no podrá evaluar la funcionalidad de previsualización de imagen.")
        else:
            # Subir la imagen
            file_input = driver.find_element(By.ID, "imagen")
            file_input.send_keys(image_path)
            
            # Esperar a que aparezca la previsualización
            WebDriverWait(driver, 10).until(EC.visibility_of_element_located((By.CSS_SELECTOR, "#imagen-preview img")))
            
            # Captura después de subir imagen
            take_screenshot(driver, test_name, "after_upload")
            
            # Probar el botón de eliminar previsualización
            driver.find_element(By.CSS_SELECTOR, ".btn-remove-preview").click()
            
            # Verificar que la previsualización desapareció
            time.sleep(1)
            assert len(driver.find_elements(By.CSS_SELECTOR, "#imagen-preview img")) == 0
            
            # Captura después de eliminar previsualización
            take_screenshot(driver, test_name, "after_remove_preview")
    
    except Exception as e:
        print(f"Error al probar la previsualización de imagen: {e}")
        take_screenshot(driver, test_name, "error")
        assert False

def test_edit_book_image_upload(setup):
    driver = setup
    test_name = "test_edit_book_image_upload"
    try:
        login_as_admin(driver)
        WebDriverWait(driver, 10).until(EC.visibility_of_element_located((By.CLASS_NAME, "books-table")))
        
        # Hacer clic en el enlace de editar para el primer libro
        edit_link = driver.find_element(By.XPATH, "(//a[contains(@href, 'editar_libro.php?id=')])[1]")
        edit_link.click()

        # Esperar a que se cargue la página de edición
        WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.ID, "editBookForm")))
        
        # Captura antes de modificar la imagen
        take_screenshot(driver, test_name, "before_image_change")
        
        # Preparar archivo de imagen para subir
        image_path = os.path.join(os.path.dirname(__file__), "test_image.jpg")
        
        if not os.path.exists(image_path):
            print(f"ADVERTENCIA: Archivo de imagen de prueba no encontrado en {image_path}")
            print("El test continuará sin evaluar la funcionalidad de cambio de imagen.")
        else:
            # Verificar si hay una imagen actual o tenemos que usar el input normal
            change_image_inputs = driver.find_elements(By.CSS_SELECTOR, ".btn-change-image input")
            regular_image_inputs = driver.find_elements(By.ID, "imagen")
            
            # Usar el input apropiado según esté disponible
            if len(change_image_inputs) > 0:
                file_input = change_image_inputs[0]
                print("Usando el botón de cambiar imagen")
            elif len(regular_image_inputs) > 0:
                file_input = regular_image_inputs[0]
                print("Usando el input de imagen normal")
            else:
                raise Exception("No se encontró ningún input para subir imagen")
            
            # Subir la nueva imagen
            file_input.send_keys(image_path)
            
            # Esperar a que aparezca la previsualización
            WebDriverWait(driver, 10).until(EC.visibility_of_element_located((By.CSS_SELECTOR, "#imagen-preview img")))
            
            # Captura después de subir imagen
            take_screenshot(driver, test_name, "after_image_upload")
            
            # Hacer clic en el botón de guardar cambios
            driver.find_element(By.NAME, "update_book").click()
            
            # Esperar la redirección a la página de gestión de libros
            WebDriverWait(driver, 10).until(EC.url_contains("gestionar_libros.php"))
            
            time.sleep(1)  # Pequeña pausa para asegurar que la página se ha actualizado
            
            # Captura después de actualizar con nueva imagen
            take_screenshot(driver, test_name, "after_update_with_image")
            
            print("Libro actualizado exitosamente con nueva imagen")
    
    except Exception as e:
        print(f"Error al probar el cambio de imagen en la edición: {e}")
        take_screenshot(driver, test_name, "error")
        raise