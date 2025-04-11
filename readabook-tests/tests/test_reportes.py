import os
import pytest
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.chrome.service import Service
from webdriver_manager.chrome import ChromeDriverManager

# Ruta donde se guardarán las capturas de pantalla
REPORTS_PATH = r"C:\laragon\www\readabook\readabook-tests\reports\test_reportes"

# Asegúrate de que la ruta exista
if not os.path.exists(REPORTS_PATH):
    os.makedirs(REPORTS_PATH)

# Inicializador de navegador
def init_driver():
    options = webdriver.ChromeOptions()
    options.add_argument("--start-maximized")
    driver = webdriver.Chrome(service=Service(ChromeDriverManager().install()), options=options)
    return driver

# Función para tomar capturas de pantalla
def save_screenshot(driver, test_name, stage):
    """Toma una captura de pantalla y la guarda con un nombre apropiado."""
    try:
        filename = f"{stage}_{test_name}.png"
        filepath = os.path.join(REPORTS_PATH, filename)
        driver.save_screenshot(filepath)
        print(f"Captura guardada: {filepath}")
    except Exception as e:
        print(f"Error al tomar captura {stage} {test_name}: {e}")

# Función para desplazarse a un elemento específico
def scroll_to_element(driver, element):
    """Desplaza la vista hasta el elemento especificado"""
    try:
        driver.execute_script("arguments[0].scrollIntoView(true);", element)
        driver.execute_script("window.scrollBy(0, -100);")
    except Exception as e:
        print(f"Error al desplazarse al elemento: {e}")

@pytest.fixture
def setup():
    driver = init_driver()
    yield driver
    driver.quit()

def login_as_admin(driver):
    driver.get("http://localhost/readabook/login.php")
    WebDriverWait(driver, 10).until(EC.visibility_of_element_located((By.ID, "email")))

    driver.find_element(By.ID, "email").send_keys("admin@readabook.com")
    driver.find_element(By.ID, "password").send_keys("1234")
    driver.find_element(By.XPATH, "//button[@type='submit']").click()

    WebDriverWait(driver, 10).until(EC.url_contains("admin/index.php"))

def test_access_reportes(setup):
    driver = setup
    test_name = "access_reportes"
    try:
        login_as_admin(driver)
        save_screenshot(driver, test_name, "after_login")

        WebDriverWait(driver, 10).until(EC.visibility_of_element_located(
            (By.XPATH, "//a[contains(@href, 'reportes.php')]")
        ))
        driver.find_element(By.XPATH, "//a[contains(@href, 'reportes.php')]").click()

        WebDriverWait(driver, 10).until(EC.url_contains("reportes.php"))
        save_screenshot(driver, test_name, "reportes_page_loaded")

        assert "Ver Reportes - Read a Book" in driver.title
        assert "Total de Libros" in driver.page_source

    except Exception as e:
        save_screenshot(driver, test_name, "error")
        print(f"Error al acceder a reportes: {e}")
        assert False

def test_verify_total_books_section(setup):
    driver = setup
    test_name = "total_books_section"
    try:
        login_as_admin(driver)
        WebDriverWait(driver, 10).until(EC.visibility_of_element_located(
            (By.XPATH, "//a[contains(@href, 'reportes.php')]")
        ))
        driver.find_element(By.XPATH, "//a[contains(@href, 'reportes.php')]").click()

        WebDriverWait(driver, 10).until(EC.visibility_of_element_located(
            (By.XPATH, "//div[@class='report-section']/h2[text()='Total de Libros']")
        ))

        total_books_section = driver.find_element(By.XPATH, "//div[@class='report-section']/h2[text()='Total de Libros']/..")
        scroll_to_element(driver, total_books_section)
        save_screenshot(driver, test_name, "total_books_display")

        assert "Total de libros en la base de datos:" in total_books_section.text

    except Exception as e:
        save_screenshot(driver, test_name, "error")
        print(f"Error al verificar sección total de libros: {e}")
        assert False

def test_verify_books_by_author_section(setup):
    driver = setup
    test_name = "books_by_author_section"
    try:
        login_as_admin(driver)
        WebDriverWait(driver, 10).until(EC.visibility_of_element_located(
            (By.XPATH, "//a[contains(@href, 'reportes.php')]")
        ))
        driver.find_element(By.XPATH, "//a[contains(@href, 'reportes.php')]").click()

        WebDriverWait(driver, 10).until(EC.visibility_of_element_located(
            (By.XPATH, "//div[@class='report-section']/h2[text()='Libros por Autor']")
        ))

        books_by_author_section = driver.find_element(By.XPATH, "//div[@class='report-section']/h2[text()='Libros por Autor']/..")
        scroll_to_element(driver, books_by_author_section)
        save_screenshot(driver, test_name, "books_by_author_display")

        table = books_by_author_section.find_element(By.TAG_NAME, "table")
        headers = table.find_elements(By.TAG_NAME, "th")
        assert len(headers) == 2
        assert headers[0].text == "Autor"
        assert headers[1].text == "Total de Libros"

        rows = table.find_elements(By.TAG_NAME, "tr")
        assert len(rows) > 1, "No se encontraron filas de datos en la tabla de autores"

    except Exception as e:
        save_screenshot(driver, test_name, "error")
        print(f"Error al verificar sección libros por autor: {e}")
        assert False

def test_verify_books_by_category_section(setup):
    driver = setup
    test_name = "books_by_category_section"
    try:
        login_as_admin(driver)
        WebDriverWait(driver, 10).until(EC.visibility_of_element_located(
            (By.XPATH, "//a[contains(@href, 'reportes.php')]")
        ))
        driver.find_element(By.XPATH, "//a[contains(@href, 'reportes.php')]").click()

        WebDriverWait(driver, 10).until(EC.visibility_of_element_located(
            (By.XPATH, "//div[@class='report-section']/h2[text()='Libros por Categoría']")
        ))

        books_by_category_section = driver.find_element(By.XPATH, "//div[@class='report-section']/h2[text()='Libros por Categoría']/..")
        scroll_to_element(driver, books_by_category_section)
        save_screenshot(driver, test_name, "books_by_category_display")

        table = books_by_category_section.find_element(By.TAG_NAME, "table")
        headers = table.find_elements(By.TAG_NAME, "th")
        assert len(headers) == 2
        assert headers[0].text == "Categoría"
        assert headers[1].text == "Total de Libros"

        rows = table.find_elements(By.TAG_NAME, "tr")
        assert len(rows) > 1, "No se encontraron filas de datos en la tabla de categorías"

    except Exception as e:
        save_screenshot(driver, test_name, "error")
        print(f"Error al verificar sección libros por categoría: {e}")
        assert False

def test_verify_all_sections_scroll(setup):
    driver = setup
    test_name = "full_page_scroll"
    try:
        login_as_admin(driver)
        WebDriverWait(driver, 10).until(EC.visibility_of_element_located(
            (By.XPATH, "//a[contains(@href, 'reportes.php')]")
        ))
        driver.find_element(By.XPATH, "//a[contains(@href, 'reportes.php')]").click()

        WebDriverWait(driver, 10).until(EC.url_contains("reportes.php"))
        save_screenshot(driver, test_name, "top_of_page")

        total_books_section = driver.find_element(By.XPATH, "//div[@class='report-section']/h2[text()='Total de Libros']/..")
        scroll_to_element(driver, total_books_section)
        save_screenshot(driver, test_name, "section1_total")

        books_by_author_section = driver.find_element(By.XPATH, "//div[@class='report-section']/h2[text()='Libros por Autor']/..")
        scroll_to_element(driver, books_by_author_section)
        save_screenshot(driver, test_name, "section2_by_author")

        books_by_category_section = driver.find_element(By.XPATH, "//div[@class='report-section']/h2[text()='Libros por Categoría']/..")
        scroll_to_element(driver, books_by_category_section)
        save_screenshot(driver, test_name, "section3_by_category")

        driver.execute_script("window.scrollTo(0, document.body.scrollHeight);")
        save_screenshot(driver, test_name, "bottom_of_page")

    except Exception as e:
        save_screenshot(driver, test_name, "error")
        print(f"Error al realizar scroll por todas las secciones: {e}")
        assert False

def test_reload_page(setup):
    driver = setup
    test_name = "reload_page"
    try:
        login_as_admin(driver)
        WebDriverWait(driver, 10).until(EC.visibility_of_element_located(
            (By.XPATH, "//a[contains(@href, 'reportes.php')]")
        ))
        driver.find_element(By.XPATH, "//a[contains(@href, 'reportes.php')]").click()

        WebDriverWait(driver, 10).until(EC.url_contains("reportes.php"))
        save_screenshot(driver, test_name, "before_reload")

        driver.refresh()

        WebDriverWait(driver, 10).until(EC.visibility_of_element_located(
            (By.XPATH, "//div[@class='report-section']/h2[text()='Total de Libros']")
        ))

    except Exception as e:
        save_screenshot(driver, test_name, "error")
        print(f"Error al recargar la página de reportes: {e}")
        assert False
