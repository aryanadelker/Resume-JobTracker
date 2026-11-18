<?php
declare(strict_types=1);
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
header('Content-Type: application/json; charset=utf-8');

function respond(int $status, array $payload): void {
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

if (empty($_SESSION['user_id'])) {
    respond(401, ['success' => false, 'message' => 'Please log in to save settings to your account.']);
}

require_once __DIR__ . '/config/db.php';
if (!isset($conn) || !($conn instanceof mysqli)) {
    respond(500, ['success' => false, 'message' => 'Database connection variable $conn was not found. Check config/db.php.']);
}

$userId = (int)$_SESSION['user_id'];
$allowedScales = ['small', 'normal', 'large', 'extra_large'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $conn->prepare(
        'SELECT font_scale, high_contrast, dyslexia_font, reduce_motion, large_cursor, increased_spacing
         FROM AccessibilitySettings WHERE user_id = ?'
    );
    if (!$stmt) respond(500, ['success' => false, 'message' => 'Could not prepare settings query.']);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $settings = $result->fetch_assoc();
    $stmt->close();
    if (!$settings) {
        $settings = [
            'font_scale' => 'normal', 'high_contrast' => 0, 'dyslexia_font' => 0,
            'reduce_motion' => 0, 'large_cursor' => 0, 'increased_spacing' => 0
        ];
    }
    respond(200, ['success' => true, 'settings' => $settings]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: GET, POST');
    respond(405, ['success' => false, 'message' => 'Method not allowed.']);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) respond(400, ['success' => false, 'message' => 'Invalid JSON request.']);

$fontScale = $input['font_scale'] ?? 'normal';
if (!in_array($fontScale, $allowedScales, true)) $fontScale = 'normal';
$highContrast = !empty($input['high_contrast']) ? 1 : 0;
$dyslexiaFont = !empty($input['dyslexia_font']) ? 1 : 0;
$reduceMotion = !empty($input['reduce_motion']) ? 1 : 0;
$largeCursor = !empty($input['large_cursor']) ? 1 : 0;
$increasedSpacing = !empty($input['increased_spacing']) ? 1 : 0;

$sql = 'INSERT INTO AccessibilitySettings
        (user_id, font_scale, high_contrast, dyslexia_font, reduce_motion, large_cursor, increased_spacing)
        VALUES (?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE font_scale=VALUES(font_scale),
        high_contrast=VALUES(high_contrast), dyslexia_font=VALUES(dyslexia_font),
        reduce_motion=VALUES(reduce_motion), large_cursor=VALUES(large_cursor),
        increased_spacing=VALUES(increased_spacing)';
$stmt = $conn->prepare($sql);
if (!$stmt) respond(500, ['success' => false, 'message' => 'Could not prepare settings save.']);
$stmt->bind_param('isiiiii', $userId, $fontScale, $highContrast, $dyslexiaFont, $reduceMotion, $largeCursor, $increasedSpacing);
$ok = $stmt->execute();
$stmt->close();

if (!$ok) respond(500, ['success' => false, 'message' => 'Database could not save the settings.']);
respond(200, ['success' => true]);
