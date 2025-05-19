import undetected_chromedriver as uc
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
import time
import json
import random
import os
import warnings
from contextlib import redirect_stderr, redirect_stdout
warnings.filterwarnings("ignore")

def get_fedresurs_cookies():
    driver = None
    try:
        options = uc.ChromeOptions()
        options.add_argument("--start-maximized")
        options.add_argument("--disable-blink-features=AutomationControlled")
        options.add_argument("--disable-blink-features")
        options.add_argument("--no-sandbox")
        options.add_argument("--disable-dev-shm-usage")
        options.add_argument("--disable-gpu")
        options.add_argument("--lang=ru-RU")
        options.add_argument("user-agent=Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36")

        with open(os.devnull, 'w') as f:
            with redirect_stderr(f), redirect_stdout(f):
                driver = uc.Chrome(
                    options=options,
                    version_main=136
                )

        time.sleep(random.uniform(1, 3))
        driver.get("https://fedresurs.ru/")
        WebDriverWait(driver, 15).until(
            EC.presence_of_element_located((By.TAG_NAME, "body")))

        for i in range(1, 4):
            driver.execute_script(f"window.scrollTo(0, document.body.scrollHeight/{i});")
            time.sleep(random.uniform(0.5, 1.5))

        cookies = driver.get_cookies()
        important_cookies = {
            '_ym_uid', '_ym_d', '_ym_isad', '_ym_visorc',
            'qrator_jsr', 'qrator_jsid2'
        }
        return [c for c in cookies if c['name'] in important_cookies]

    except Exception:
        return None
    finally:
        if driver:
            try:
                with open(os.devnull, 'w') as f:
                    with redirect_stderr(f), redirect_stdout(f):
                        driver.quit()
            except:
                pass

if __name__ == "__main__":
    with open(os.devnull, 'w') as f:
        with redirect_stderr(f), redirect_stdout(f):
            cookies = get_fedresurs_cookies()

    if cookies:
        with open('fedresurs_cookies.json', 'w', encoding='utf-8') as f:
            json.dump(cookies, f, ensure_ascii=False, indent=2)
    else:
        with open('fedresurs_error.log', 'w') as f:
            f.write("Не удалось получить куки\n")
