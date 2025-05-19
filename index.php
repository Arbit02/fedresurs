<?php

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $filePath = 'fedresurs_cookies.json';
    if (file_exists($filePath)) {
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="fedresurs_cookies.json"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
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
