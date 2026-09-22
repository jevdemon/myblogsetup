<?php
require_once dirname(__FILE__) . '/Idno/start.php';

header('Content-Type: application/json');

if (!\Idno\Core\Idno::site()->session()->isLoggedIn()) {
    http_response_code(403);
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

if (empty($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'No valid file uploaded']);
    exit;
}

$allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
$fileType = mime_content_type($_FILES['photo']['tmp_name']);
if (!in_array($fileType, $allowedTypes)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid file type']);
    exit;
}

$maxSize = 15 * 1024 * 1024;
if ($_FILES['photo']['size'] > $maxSize) {
    http_response_code(400);
    echo json_encode(['error' => 'File too large']);
    exit;
}

$extMap = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
$ext = $extMap[$fileType];
$filename = 'post-' . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.' . $ext;

$uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/blog/gfx/post-photos/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$destPath = $uploadDir . $filename;
if (!move_uploaded_file($_FILES['photo']['tmp_name'], $destPath)) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save file']);
    exit;
}

$url = 'https://evdemon.org/blog/gfx/post-photos/' . $filename;
echo json_encode(['url' => $url]);
