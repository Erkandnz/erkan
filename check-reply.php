<?php
// check-reply.php
// The visitor's browser polls this to see if you've replied yet on Telegram.

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: https://erkanuyan.com.tr');

$sessionId = isset($_GET['sessionId']) ? trim(preg_replace('/[^\p{L}\p{N} _-]/u', '', $_GET['sessionId'])) : '';

if ($sessionId === '') {
    http_response_code(400);
    echo json_encode(['ok' => false]);
    exit;
}

$file = __DIR__ . '/replies/' . $sessionId . '.json';

if (file_exists($file)) {
    $data = json_decode(file_get_contents($file), true);
    unlink($file); // deliver once, then clear it
    echo json_encode(['ok' => true, 'reply' => $data['reply']]);
} else {
    echo json_encode(['ok' => true, 'reply' => null]);
}
