<?php
require_once dirname(__FILE__) . '/Idno/start.php';

header('Content-Type: application/json');

if (!\Idno\Core\Idno::site()->session()->isLoggedIn()) {
    http_response_code(403);
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$filename = isset($input['filename']) ? basename($input['filename']) : '';
$angle = isset($input['angle']) ? (float)$input['angle'] : 0;

if (empty($filename) || !preg_match('/^post-[a-zA-Z0-9\-_]+\.(jpg|jpeg|png|webp|gif)$/i', $filename)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid filename']);
    exit;
}

$filePath = $_SERVER['DOCUMENT_ROOT'] . '/blog/gfx/post-photos/' . $filename;
if (!file_exists($filePath)) {
    http_response_code(404);
    echo json_encode(['error' => 'File not found']);
    exit;
}

$ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

switch ($ext) {
    case 'jpg':
    case 'jpeg':
        $image = imagecreatefromjpeg($filePath);
        break;
    case 'png':
        $image = imagecreatefrompng($filePath);
        break;
    case 'webp':
        $image = imagecreatefromwebp($filePath);
        break;
    case 'gif':
        $image = imagecreatefromgif($filePath);
        break;
    default:
        $image = false;
}

if (!$image) {
    http_response_code(500);
    echo json_encode(['error' => 'Could not read image']);
    exit;
}

$rotated = imagerotate($image, $angle, 0);

if ($ext === 'png' || $ext === 'webp' || $ext === 'gif') {
    imagealphablending($rotated, false);
    imagesavealpha($rotated, true);
}

switch ($ext) {
    case 'jpg':
    case 'jpeg':
        imagejpeg($rotated, $filePath, 90);
        break;
    case 'png':
        imagepng($rotated, $filePath);
        break;
    case 'webp':
        imagewebp($rotated, $filePath);
        break;
    case 'gif':
        imagegif($rotated, $filePath);
        break;
}

imagedestroy($image);
imagedestroy($rotated);

echo json_encode(['success' => true]);
