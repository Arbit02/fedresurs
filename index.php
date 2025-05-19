<?php
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    shell_exec('python main.py');
    $filePath = 'fedresurs_cookies.json';
    if (file_exists($filePath)) {
        $cookiesJson = file_get_contents($filePath);
        $cookies = json_decode($cookiesJson, true);

        $cookieParts = [];
        foreach ($cookies as $cookie) {
            $cookieParts[] = $cookie['name'] . '=' . $cookie['value'];
        }
        $cookiesString = implode('; ', $cookieParts);
        header('Content-Type: text/plain');
        echo $cookiesString;
        exit;
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Файл не найден']);
        exit;
    }

} else {
    http_response_code(405);
    header('Allow: GET');
    echo json_encode(['error' => 'Метод не поддерживается']);
    exit;
}
