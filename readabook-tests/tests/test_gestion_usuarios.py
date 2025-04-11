import os
import pytest
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.common.alert import Alert
from selenium.webdriver.common.action_chains import ActionChains

# Ruta donde se guardarán las capturas de pantalla
REPORTS_PATH = r"C:\laragon\www\readabook\readabook-tests\reports\test_gestion-usuarios"

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

# Función para desplazarse a la tabla de usuarios
def scroll_to_users_table(driver):
    """
    Desplaza la vista hasta la tabla de usuarios
    """
    try:
        # Esperar a que la tabla esté presente
        table = WebDriverWait(driver, 10).until(
            EC.visibility_of_element_located((By.CLASS_NAME, "users-table"))
        )
        # Desplazar hasta la tabla
        driver.execute_script("arguments[0].scrollIntoView(true);", table)
        # Pequeña pausa para permitir que el desplazamiento termine
        driver.execute_script("window.scrollBy(0, -100);")  # Ajustar para mejorar la visibilidad
    except Exception as e:
        print(f"Error al desplazarse a la tabla: {e}")

# Función para tomar capturas de pantalla antes y después de cada test
def take_screenshot(driver, test_name, stage):
    """
    Toma una captura de pantalla y la guarda con un nombre apropiado.
    Si el stage contiene 'table', primero se desplaza hacia la tabla.
    
    Args:
        driver: Instancia del WebDriver
        test_name: Nombre del test
        stage: Etapa (before/after/error) con posible sufijo _table
    """
    try:
        # Si necesitamos capturar la tabla, desplazar hasta ella
        if "table" in stage:
            scroll_to_users_table(driver)
        
        filename = f"{stage}_{test_name}.png"
        filepath = os.path.join(REPORTS_PATH, filename)
        driver.save_screenshot(filepath)
        print(f"Captura guardada: {filepath}")
    except Exception as e:
        print(f"Error al tomar captura {stage} {test_name}: {e}")

def login_as_admin(driver):
    email = "admin@readabook.com"
    password = "1234"
    WebDriverWait(driver, 10).until(EC.visibility_of_element_located((By.ID, "email")))

    # Iniciar sesión como administrador
    driver.find_element(By.ID, "email").send_keys(email)
    driver.find_element(By.ID, "password").send_keys(password)
    driver.find_element(By.XPATH, "//button[@type='submit']").click()
    
    WebDriverWait(driver, 10).until(EC.url_contains("admin/index.php"))
    WebDriverWait(driver, 10).until(EC.visibility_of_element_located((By.XPATH, "//a[contains(@href, 'gestionar_usuarios.php')]")))

    driver.find_element(By.XPATH, "//a[contains(@href, 'gestionar_usuarios.php')]").click()
    WebDriverWait(driver, 10).until(EC.url_contains("gestionar_usuarios.php"))

def test_add_user(setup):
    driver = setup
    test_name = "test_add_user"
    try:
        login_as_admin(driver)
        WebDriverWait(driver, 10).until(EC.visibility_of_element_located((By.CLASS_NAME, "user-form")))

        # Captura de pantalla antes de la prueba (formulario)
        take_screenshot(driver, test_name, "before_form")
        
        # Captura de la tabla antes de agregar el usuario
        take_screenshot(driver, test_name, "before_table")

        # Completar formulario para agregar un usuario
        driver.find_element(By.ID, "nombre").send_keys("Nuevo Usuario")
        driver.find_element(By.ID, "email").send_keys("nuevo@usuario.com")
        driver.find_element(By.ID, "password").send_keys("password123")

        # Hacer clic en el <select> para desplegar las opciones
        driver.find_element(By.ID, "rol").click()

        # Hacer clic en la opción "usuario" que se despliega
        driver.find_element(By.XPATH, "//option[@value='usuario']").click()

        # Hacer clic en el botón de agregar
        driver.find_element(By.CSS_SELECTOR, "button[type='submit']").click()

        # Esperar mensaje de éxito
        WebDriverWait(driver, 10).until(EC.visibility_of_element_located((By.CLASS_NAME, "message")))

        # Verificar que el usuario fue agregado
        assert "Usuario agregado exitosamente" in driver.page_source

        # Verificar que el usuario agregado aparece en la tabla de usuarios
        WebDriverWait(driver, 10).until(EC.visibility_of_element_located((By.CLASS_NAME, "users-table")))
        assert "Nuevo Usuario" in driver.page_source

        # Captura del formulario después de agregar el usuario
        take_screenshot(driver, test_name, "after_form")
        
        # Captura de la tabla después de agregar el usuario
        take_screenshot(driver, test_name, "after_table")
        
    except Exception as e:
        print(f"Error al agregar el usuario: {e}")
        take_screenshot(driver, test_name, "error")
        assert False

def test_edit_user(setup):
    driver = setup
    test_name = "test_edit_user"
    try:
        login_as_admin(driver)
        WebDriverWait(driver, 10).until(EC.visibility_of_element_located((By.CLASS_NAME, "users-table")))
        
        # Captura de la tabla antes de editar
        take_screenshot(driver, test_name, "before_table")

        # Localizar un usuario para editar (por ejemplo, el primer usuario en la lista)
        driver.find_element(By.XPATH, "(//a[contains(@href, 'editar_usuario.php?id=')])[1]").click()

        WebDriverWait(driver, 10).until(EC.visibility_of_element_located((By.CLASS_NAME, "form-container")))

        # Captura del formulario de edición
        take_screenshot(driver, test_name, "edit_form")

        # Editar los datos del usuario
        new_name = "Usuario Editado"
        new_email = "usuarioeditado@readabook.com"
        new_role = "editor"

        driver.find_element(By.ID, "nombre").clear()
        driver.find_element(By.ID, "nombre").send_keys(new_name)
        
        driver.find_element(By.ID, "email").clear()
        driver.find_element(By.ID, "email").send_keys(new_email)

        # Cambiar el rol
        driver.find_element(By.ID, "rol").click()
        driver.find_element(By.XPATH, f"//option[@value='{new_role}']").click()

        # Hacer clic en el botón de actualización
        driver.find_element(By.CSS_SELECTOR, "button[type='submit']").click()

        # Esperar la redirección a la página de gestión de usuarios
        WebDriverWait(driver, 10).until(EC.url_contains("gestionar_usuarios.php"))

        # Captura de la tabla después de la edición
        take_screenshot(driver, test_name, "after_table")

    except Exception as e:
        print(f"Error al editar el usuario: {e}")
        take_screenshot(driver, test_name, "error")
        assert False

def test_delete_user_success(setup):
    driver = setup
    test_name = "test_delete_user_success"
    try:
        login_as_admin(driver)
        WebDriverWait(driver, 10).until(EC.visibility_of_element_located((By.CLASS_NAME, "users-table")))

        # Captura de la tabla antes de la eliminación
        take_screenshot(driver, test_name, "before_table")

        # Localizar y hacer clic en el botón de eliminar
        delete_button = driver.find_element(By.XPATH, "(//button[@type='submit' and @name='delete_user'])[1]")
        delete_button.click()

        # Esperar y manejar la alerta de confirmación
        WebDriverWait(driver, 10).until(EC.alert_is_present())  # Espera hasta que la alerta esté presente
        alert = Alert(driver)  # Crear un objeto Alert
        alert.accept()  # Aceptar la alerta de confirmación

        # Verificar que el mensaje de eliminación esté presente y contiene el texto correcto
        success_message = WebDriverWait(driver, 10).until(EC.visibility_of_element_located((By.CLASS_NAME, "message")))

        assert "Usuario eliminado exitosamente" in success_message.text

        # Captura de la tabla después de la eliminación
        take_screenshot(driver, test_name, "after_table")

    except Exception as e:
        # Si ocurre un error, tomar captura de pantalla y mostrar el error
        take_screenshot(driver, test_name, "error")
        print(f"Error al eliminar el usuario: {e}")
        assert False

def test_delete_user_fail_self_removal(setup):
    driver = setup
    test_name = "test_delete_user_fail_self_removal"
    try:
        login_as_admin(driver)
        WebDriverWait(driver, 10).until(EC.visibility_of_element_located((By.CLASS_NAME, "users-table")))

        # Captura de la tabla antes de la eliminación fallida
        take_screenshot(driver, test_name, "before_table")

        # Intentar eliminar el propio usuario (o un usuario que no debería eliminarse)
        delete_button = driver.find_element(By.XPATH, "(//button[@type='submit' and @name='delete_user'])[1]")
        delete_button.click()

        # Esperar y manejar la alerta de confirmación
        WebDriverWait(driver, 10).until(EC.alert_is_present())  # Espera hasta que la alerta esté presente
        alert = Alert(driver)  # Crear un objeto Alert
        alert.accept()  # Aceptar la alerta de confirmación

        # Esperar mensaje de error
        WebDriverWait(driver, 10).until(EC.visibility_of_element_located((By.CLASS_NAME, "error")))

        assert "No puedes eliminar tu propia cuenta de administrador" in driver.page_source

        # Captura de la tabla después de la eliminación fallida
        take_screenshot(driver, test_name, "after_table")

    except Exception as e:
        print(f"Error al intentar eliminar el usuario: {e}")
        take_screenshot(driver, test_name, "error")
        assert False