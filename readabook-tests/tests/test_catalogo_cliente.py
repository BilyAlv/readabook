import os
import pytest
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC

# Ruta donde se guardarán las capturas de pantalla
REPORTS_PATH = r"C:\laragon\www\readabook\readabook-tests\reports\test_perfil_usuario"

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

def take_screenshot(driver, test_name, stage):

    try:
        filename = f"{stage}_{test_name}.png"
        filepath = os.path.join(REPORTS_PATH, filename)
        driver.save_screenshot(filepath)
        print(f"Captura guardada: {filepath}")
    except Exception as e:
        print(f"Error al tomar captura {stage} {test_name}: {e}")

def login_as_client(driver):
    email = "user@example.com"  # Usar un correo de cliente
    password = "250105e"  # Usar la contraseña de cliente
    WebDriverWait(driver, 10).until(EC.visibility_of_element_located((By.ID, "email")))

    # Iniciar sesión como cliente
    driver.find_element(By.ID, "email").send_keys(email)
    driver.find_element(By.ID, "password").send_keys(password)
    driver.find_element(By.XPATH, "//button[@type='submit']").click()
    
    # Tomar captura de pantalla después de iniciar sesión y verificar el catálogo
    take_screenshot(driver, "test_view_catalog", "after_login")

def test_view_catalog(setup):
    driver = setup
    test_name = "test_view_catalog"
    login_as_client(driver)  # Iniciar sesión como cliente
    
    # Captura de pantalla después de la verificación
    take_screenshot(driver, test_name, "after")
    
    # Tomar un último screenshot después de la prueba y cerrar la página
    take_screenshot(driver, test_name, "final")
    driver.quit()  # Cerrar el navegador
