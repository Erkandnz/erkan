<?php
// telegram-relay.php
// Receives a message from the site's contact box and forwards it to Telegram.
// Keep this file's contents private -- it holds your bot token.

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: https://erkanuyan.com.tr');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

$botToken = '8699079217:AAF_u-jGcxdyfJPO1xcAJSOvCIHHhYnR6EA';
$chatId   = '588842971';

$input = json_decode(file_get_contents('php://input'), true);
$message = isset($input['message']) ? trim($input['message']) : '';
$sessionId = isset($input['sessionId']) ? trim(preg_replace('/[^\p{L}\p{N} _-]/u', '', $input['sessionId'])) : '';

if ($message === '' || $sessionId === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Missing message or session']);
    exit;
}

if (mb_strlen($message) > 500) {
    $message = mb_substr($message, 0, 500);
}

// The [Oturum: ...] tag lets us match your Telegram reply back to this visitor.
// Reply (swipe / long-press > Reply) to THIS message in Telegram when answering.
$text = "Sitenizden yeni bir mesaj var:\n\n" . $message . "\n\n[Oturum: " . $sessionId . "]";

$telegramUrl = "https://api.telegram.org/bot{$botToken}/sendMessage";
$postFields = json_encode([
    'chat_id' => $chatId,
    'text'    => $text,
]);

$ch = curl_init($telegramUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($httpCode === 200) {
    echo json_encode(['ok' => true]);
} else {
    http_response_code(502);
    echo json_encode(['ok' => false, 'error' => $curlError ?: 'Telegram API error', 'http_code' => $httpCode]);
}
