import pytest
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.chrome.service import Service
from webdriver_manager.chrome import ChromeDriverManager
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
import os

# Ruta de las capturas y el reporte
report_path = "C:/laragon/www/readabook/readabook-tests/reports/test_login"
if not os.path.exists(report_path):
    os.makedirs(report_path)

# Inicializador de navegador
def init_driver():
    options = webdriver.ChromeOptions()
    options.add_argument("--start-maximized")
    driver = webdriver.Chrome(service=Service(ChromeDriverManager().install()), options=options)
    return driver

# Captura de pantalla mejorada
def save_screenshot(driver, test_name, stage):

    try:
        screenshot_path = os.path.join(report_path, f"{stage}_{test_name}.png")
        driver.save_screenshot(screenshot_path)
        print(f"Captura guardada: {screenshot_path}")
    except Exception as e:
        print(f"Error al guardar captura {stage}_{test_name}: {e}")

# Fixture para abrir/cerrar navegador
@pytest.fixture
def setup():
    driver = init_driver()
    driver.get("http://localhost/readabook/login.php")
    yield driver
    driver.quit()

# Test 1: Login exitoso
def test_login_success_admin(setup):
    driver = setup
    test_name = "login_success_admin"
    
    # Esperar a que cargue la página y tomar captura antes de cualquier acción
    WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.ID, "email")))
    save_screenshot(driver, test_name, "before")

    try:
        # Datos correctos de admin
        driver.find_element(By.ID, "email").send_keys("pruebas@example.com")
        driver.find_element(By.ID, "password").send_keys("19284637839")
        
        # Captura antes de enviar el formulario
        save_screenshot(driver, test_name, "filled_form")
        
        driver.find_element(By.ID, "password").send_keys(Keys.RETURN)

        # Esperar redirección
        WebDriverWait(driver, 10).until(EC.url_contains("admin"))

        assert "admin" in driver.current_url, "No se redirigió al panel de administración"
        
        # Captura después del login exitoso
        save_screenshot(driver, test_name, "after")
    except Exception as e:
        save_screenshot(driver, test_name, "error")
        print(f"Error en test login admin: {e}")
        assert False

# Test 2: Login fallido por correo incorrecto
def test_login_failure_invalid_email(setup):
    driver = setup
    test_name = "login_failure_invalid_email"
    
    # Captura antes de comenzar el test
    WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.ID, "email")))
    save_screenshot(driver, test_name, "before")
    
    try:
        # Datos incorrectos (correo incorrecto)
        driver.find_element(By.ID, "email").send_keys("wrongemail@readabook.com")
        driver.find_element(By.ID, "password").send_keys("1234")
        
        # Captura con el formulario lleno antes de enviar
        save_screenshot(driver, test_name, "filled_form")
        
        driver.find_element(By.ID, "password").send_keys(Keys.RETURN)

        # Esperar mensaje de error
        WebDriverWait(driver, 10).until(
            EC.presence_of_element_located((By.CLASS_NAME, "error-message"))
        )

        error_message = driver.find_element(By.CLASS_NAME, "error-message").text
        assert "incorrecta" in error_message.lower() or "no encontrado" in error_message.lower(), "No se mostró el mensaje de error esperado"
        
        # Captura después de mostrar el error
        save_screenshot(driver, test_name, "after")
    except Exception as e:
        save_screenshot(driver, test_name, "error")
        print(f"Error en test login email inválido: {e}")
        assert False

# Test 3: Login fallido por contraseña incorrecta
def test_login_failure_invalid_password(setup):
    driver = setup
    test_name = "login_failure_invalid_password"
    
    # Captura antes de comenzar el test
    WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.ID, "email")))
    save_screenshot(driver, test_name, "before")
    
    try:
        # Datos incorrectos (contraseña incorrecta)
        driver.find_element(By.ID, "email").send_keys("admin@readabook.com")
        driver.find_element(By.ID, "password").send_keys("wrongpassword")
        
        # Captura con el formulario lleno antes de enviar
        save_screenshot(driver, test_name, "filled_form")
        
        driver.find_element(By.ID, "password").send_keys(Keys.RETURN)

        # Esperar mensaje de error
        WebDriverWait(driver, 10).until(
            EC.presence_of_element_located((By.CLASS_NAME, "error-message"))
        )

        error_message = driver.find_element(By.CLASS_NAME, "error-message").text
        assert "incorrecta" in error_message.lower() or "no encontrado" in error_message.lower(), "No se mostró el mensaje de error esperado"
        
        # Captura después de mostrar el error
        save_screenshot(driver, test_name, "after")
    except Exception as e:
        save_screenshot(driver, test_name, "error")
        print(f"Error en test login contraseña inválida: {e}")
        assert False

# Test 4: Login fallido por ambos incorrectos (correo y contraseña)
def test_login_failure_invalid_credentials(setup):
    driver = setup
    test_name = "login_failure_invalid_credentials"
    
    # Captura antes de comenzar el test
    WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.ID, "email")))
    save_screenshot(driver, test_name, "before")
    
    try:
        # Datos incorrectos (correo y contraseña incorrectos)
        driver.find_element(By.ID, "email").send_keys("wrongemail@readabook.com")
        driver.find_element(By.ID, "password").send_keys("wrongpassword")
        
        # Captura con el formulario lleno antes de enviar
        save_screenshot(driver, test_name, "filled_form")
        
        driver.find_element(By.ID, "password").send_keys(Keys.RETURN)

        # Esperar mensaje de error
        WebDriverWait(driver, 10).until(
            EC.presence_of_element_located((By.CLASS_NAME, "error-message"))
        )

        error_message = driver.find_element(By.CLASS_NAME, "error-message").text
        assert "incorrecta" in error_message.lower() or "no encontrado" in error_message.lower(), "No se mostró el mensaje de error esperado"
        
        # Captura después de mostrar el error
        save_screenshot(driver, test_name, "after")
    except Exception as e:
        save_screenshot(driver, test_name, "error")
        print(f"Error en test login credenciales inválidas: {e}")
        assert False