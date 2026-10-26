<?php
// telegram-webhook.php
// Telegram calls this URL automatically whenever you send a message in the
// bot chat. If your message is a REPLY to one of the site's forwarded
// messages, we read the [Oturum: ...] tag from the quoted text and store
// your reply so the visitor's browser can pick it up.

header('Content-Type: application/json');

$botToken = '8699079217:AAF_u-jGcxdyfJPO1xcAJSOvCIHHhYnR6EA';

$update = json_decode(file_get_contents('php://input'), true);
$message = $update['message'] ?? null;

if (!$message || empty($message['text'])) {
    http_response_code(200); // always 200 so Telegram doesn't retry forever
    echo json_encode(['ok' => true]);
    exit;
}

$replyText = trim($message['text']);
$quoted = $message['reply_to_message']['text'] ?? '';

// Extract session id from the quoted (original) message text
$sessionId = '';
if ($quoted && preg_match('/\[Oturum:\s*([\p{L}\p{N} _-]+)\]/u', $quoted, $m)) {
    $sessionId = trim($m[1]);
}

if ($sessionId !== '') {
    $dir = __DIR__ . '/replies';
    if (!is_dir($dir)) {
        mkdir($dir, 0700, true);
    }
    $file = $dir . '/' . $sessionId . '.json';
    file_put_contents($file, json_encode([
        'reply' => $replyText,
        'time'  => time(),
    ]));
}

http_response_code(200);
echo json_encode(['ok' => true]);
