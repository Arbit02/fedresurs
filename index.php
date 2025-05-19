<?php

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $filePath = 'fedresurs_cookies.json';

    // Проверка существования файла
    if (file_exists($filePath)) {
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="fedresurs_cookies.json"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    } else {
        // Файл не найден — отправим JSON с ошибкой
        http_response_code(404);
        echo json_encode(['error' => 'Файл не найден']);
        exit;
    }

} else {
    // Если не GET-запрос, вернем 405 Method Not Allowed
    http_response_code(405);
    header('Allow: GET');
    echo json_encode(['error' => 'Метод не поддерживается']);
    exit;
}
