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

REPORTS_PATH = r"C:\laragon\www\readabook\readabook-tests\reports\test_gestion-libros"

if not os.path.exists(REPORTS_PATH):
    os.makedirs(REPORTS_PATH)

@pytest.fixture
def setup():
    driver = webdriver.Chrome()
    driver.get("http://localhost/readabook/login.php")
    driver.maximize_window()  
    yield driver
    driver.quit()

def scroll_to_books_table(driver):

    try:
        table = WebDriverWait(driver, 10).until(
            EC.visibility_of_element_located((By.CLASS_NAME, "books-table"))
        )
        driver.execute_script("arguments[0].scrollIntoView(true);", table)
        driver.execute_script("window.scrollBy(0, -100);") 
    except Exception as e:
        print(f"Error al desplazarse a la tabla: {e}")

def take_screenshot(driver, test_name, stage):

    try:
        if "table" in stage:
            scroll_to_books_table(driver)
        
        filename = f"{stage}_{test_name}.png"
        filepath = os.path.join(REPORTS_PATH, filename)
        driver.save_screenshot(filepath)
        print(f"Captura guardada: {filepath}")
    except Exception as e:
        print(f"Error al tomar captura {stage} {test_name}: {e}")

def login_as_admin(driver):

    email = "pruebas@example.com"
    password = "19284637839"
    WebDriverWait(driver, 10).until(EC.visibility_of_element_located((By.ID, "email")))

    driver.find_element(By.ID, "email").send_keys(email)
    driver.find_element(By.ID, "password").send_keys(password)
    driver.find_element(By.XPATH, "//button[@type='submit']").click()
    
    WebDriverWait(driver, 10).until(EC.url_contains("admin/index.php"))
    WebDriverWait(driver, 10).until(EC.visibility_of_element_located((By.XPATH, "//a[contains(@href, 'gestionar_libros.php')]")))

    driver.find_element(By.XPATH, "//a[contains(@href, 'gestionar_libros.php')]").click()
    WebDriverWait(driver, 10).until(EC.url_contains("gestionar_libros.php"))

def generate_random_string(length=10):

    return ''.join(random.choice(string.ascii_letters) for _ in range(length))

def test_add_book(setup):
    driver = setup
    test_name = "test_add_book"
    try:
        login_as_admin(driver)
        WebDriverWait(driver, 10).until(EC.visibility_of_element_located((By.CLASS_NAME, "book-form")))

        take_screenshot(driver, test_name, "before_form")
        
        take_screenshot(driver, test_name, "before_table")

        titulo = f"Libro Test {generate_random_string(5)}"
        autor = f"Autor Test {generate_random_string(5)}"
        categoria = "Ficción"
        precio = "19.99"
        stock = "10"
        descripcion = f"Esta es una descripción de prueba para {titulo}."

        driver.find_element(By.ID, "titulo").send_keys(titulo)
        driver.find_element(By.ID, "autor").send_keys(autor)
        driver.find_element(By.ID, "categoria").send_keys(categoria)
        driver.find_element(By.ID, "precio").send_keys(precio)
        driver.find_element(By.ID, "stock").send_keys(stock)
        driver.find_element(By.ID, "descripcion").send_keys(descripcion)

        driver.find_element(By.NAME, "add_book").click()

        WebDriverWait(driver, 10).until(EC.visibility_of_element_located((By.CLASS_NAME, "message")))

        assert "Libro agregado exitosamente" in driver.page_source

        WebDriverWait(driver, 10).until(EC.visibility_of_element_located((By.CLASS_NAME, "books-table")))
        assert titulo in driver.page_source

        take_screenshot(driver, test_name, "after_form")
        
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
        
        take_screenshot(driver, test_name, "before_table")

        original_title = driver.find_element(By.XPATH, "(//td[2]//span)[1]").text
        print(f"Título original: {original_title}")
        
        edit_link = driver.find_element(By.XPATH, "(//a[contains(@href, 'editar_libro.php?id=')])[1]")
        edit_link.click()

        WebDriverWait(driver, 10).until(
            EC.presence_of_element_located((By.ID, "editBookForm"))
        )
        
        page_title = driver.find_element(By.CLASS_NAME, "book-management-title").text
        assert "Editar Libro:" in page_title
        print(f"Título de la página: {page_title}")

        take_screenshot(driver, test_name, "edit_form")

        new_title = f"Libro Editado {generate_random_string(5)}"
        new_price = "29.99"
        new_stock = "15"

        title_field = driver.find_element(By.ID, "titulo")
        title_field.clear()
        title_field.send_keys(new_title)
        
        price_field = driver.find_element(By.ID, "precio")
        price_field.clear()
        price_field.send_keys(new_price)
        
        stock_field = driver.find_element(By.ID, "stock")
        stock_field.clear()
        stock_field.send_keys(new_stock)

        take_screenshot(driver, test_name, "after_edit_fields")

        driver.find_element(By.NAME, "update_book").click()

        WebDriverWait(driver, 10).until(EC.url_contains("gestionar_libros.php"))
        
        WebDriverWait(driver, 10).until(EC.visibility_of_element_located((By.CLASS_NAME, "books-table")))
        
        time.sleep(1) 
        assert new_title in driver.page_source, f"El nuevo título '{new_title}' no aparece en la tabla después de editar"
        print(f"Libro actualizado exitosamente a: {new_title}")
        
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

        take_screenshot(driver, test_name, "before_table")
        
        book_title = driver.find_element(By.XPATH, "(//td[2]//span)[1]").text

        delete_button = driver.find_element(By.XPATH, "(//button[@type='submit' and @name='delete_book'])[1]")
        delete_button.click()

        WebDriverWait(driver, 10).until(EC.alert_is_present())
        alert = Alert(driver)
        alert.accept()

        WebDriverWait(driver, 10).until(EC.visibility_of_element_located((By.CLASS_NAME, "message")))
        assert "Libro eliminado exitosamente" in driver.page_source

        take_screenshot(driver, test_name, "after_table")
        
        time.sleep(1)  
        page_source = driver.page_source
        
        if book_title in page_source:
            search_input = driver.find_element(By.ID, "searchBook")
            search_input.clear()
            search_input.send_keys(book_title)
            time.sleep(1) 
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
        take_screenshot(driver, test_name, "before_upload")
        emos que existe un archivo de prueba en una ubicación conocida
        image_path = os.path.join(os.path.dirname(__file__), "test_image.jpg")
        
        if not os.path.exists(image_path):
            print(f"ADVERTENCIA: Archivo de imagen de prueba no encontrado en {image_path}")
            print("El test continuará pero no podrá evaluar la funcionalidad de previsualización de imagen.")
        else:
            file_input = driver.find_element(By.ID, "imagen")
            file_input.send_keys(image_path)
            perar a que aparezca la previsualización
            WebDriverWait(driver, 10).until(EC.visibility_of_element_located((By.CSS_SELECTOR, "#imagen-preview img")))
            tura después de subir imagen
            take_screenshot(driver, test_name, "after_upload")
            ar el botón de eliminar previsualización
            driver.find_element(By.CSS_SELECTOR, ".btn-remove-preview").click()
            ficar que la previsualización desapareció
            time.sleep(1)
            assert len(driver.find_elements(By.CSS_SELECTOR, "#imagen-preview img")) == 0
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
        edit_link = driver.find_element(By.XPATH, "(//a[contains(@href, 'editar_libro.php?id=')])[1]")
        edit_link.click()
        WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.ID, "editBookForm")))
        take_screenshot(driver, test_name, "before_image_change")
        image_path = os.path.join(os.path.dirname(__file__), "test_image.jpg")
        
        if not os.path.exists(image_path):
            print(f"ADVERTENCIA: Archivo de imagen de prueba no encontrado en {image_path}")
            print("El test continuará sin evaluar la funcionalidad de cambio de imagen.")
        else:
            change_image_inputs = driver.find_elements(By.CSS_SELECTOR, ".btn-change-image input")
            regular_image_inputs = driver.find_elements(By.ID, "imagen")
            
            if len(change_image_inputs) > 0:
                file_input = change_image_inputs[0]
                print("Usando el botón de cambiar imagen")
            elif len(regular_image_inputs) > 0:
                file_input = regular_image_inputs[0]
                print("Usando el input de imagen normal")
            else:
                raise Exception("No se encontró ningún input para subir imagen")
            
            file_input.send_keys(image_path)
            WebDriverWait(driver, 10).until(EC.visibility_of_element_located((By.CSS_SELECTOR, "#imagen-preview img")))
            take_screenshot(driver, test_name, "after_image_upload")
            driver.find_element(By.NAME, "update_book").click()
            WebDriverWait(driver, 10).until(EC.url_contains("gestionar_libros.php"))
            time.sleep(1) 
            take_screenshot(driver, test_name, "after_update_with_image")
            
            print("Libro actualizado exitosamente con nueva imagen")
    
    except Exception as e:
        print(f"Error al probar el cambio de imagen en la edición: {e}")
        take_screenshot(driver, test_name, "error")
        raise