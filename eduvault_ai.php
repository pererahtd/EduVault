<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/bootstrap_2.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$user = current_user();
if (!$user) {
    echo json_encode(['reply' => 'Please log in to use EduVault AI.']);
    exit;
}

$data    = json_decode(file_get_contents('php://input'), true);
$message = trim((string) ($data['message'] ?? ''));

if ($message === '') {
    echo json_encode(['reply' => 'Please type a message.']);
    exit;
}
if (mb_strlen($message) > 500) {
    echo json_encode(['reply' => 'Please keep your message under 500 characters.']);
    exit;
}

$studentName = first_name($user['full_name']);

$systemInstruction = "You are EduVault AI, a friendly assistant for the EduVault student platform used by NSBM Green University students.
You are talking to a student named {$studentName}. Be polite and warm.

EduVault pages the student can use:
- Dashboard: the main page with greeting, GPA calculator, schedule and announcements.
- Your Uploads: upload PDF notes. Admin approves them before others see them.
- Study Material: browse and download approved PDFs, filter by batch and module.
- Groups: create or join study groups, group chat, schedule study sessions.
- Notice Board: official announcements from the administration.
- Discussion / Q&A: ask questions and answer classmates.
- My Profile: change password and profile picture.
- Feedback: send feedback to the team.

Keep answers short and practical. Reply in Sinhala if the student writes in Sinhala, otherwise reply in English.
Do not invent features that are not listed above.";

$payload = [
    'systemInstruction' => ['parts' => [['text' => $systemInstruction]]],
    'contents'          => [['role' => 'user', 'parts' => [['text' => $message]]]],
];
$body = json_encode($payload, JSON_UNESCAPED_UNICODE);

// Each model has its own free quota. If one is full, the next model is tried.
$models = [
    GEMINI_MODEL,           
    'gemini-3.5-flash',
    'gemini-3.5-flash-lite',
    'gemini-flash-latest',
];
$models = array_values(array_unique(array_filter($models)));

$reply = null;
foreach ($models as $model) {
    $ch = curl_init('https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $body,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'x-goog-api-key: ' . GEMINI_API_KEY],
        CURLOPT_TIMEOUT        => 25,
    ]);
    $response = curl_exec($ch);
    $curlErr  = curl_error($ch);
    $code     = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false) {
        error_log("EduVault AI curl error [$model]: $curlErr");
        continue;
    }

    $result = json_decode($response, true);
    $text   = '';
    foreach (($result['candidates'][0]['content']['parts'] ?? []) as $part) {
        $text .= $part['text'] ?? '';
    }

    if (trim($text) !== '') {
        $reply = trim($text);
        break;
    }

    error_log("EduVault AI failed [$model] HTTP $code: " . substr((string) $response, 0, 500));
}

if ($reply === null) {
    echo json_encode(['reply' => 'Sorry, the AI is busy right now. Please try again in a minute.']);
    exit;
}

echo json_encode(['reply' => $reply], JSON_UNESCAPED_UNICODE);