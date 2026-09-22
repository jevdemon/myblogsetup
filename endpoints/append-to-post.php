<?php
ob_start();
error_reporting(0);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', dirname(__FILE__) . '/append-debug.log');

require_once dirname(__FILE__) . '/Idno/start.php';

if (!\Idno\Core\Idno::site()->session()->isLoggedIn()) {
    ob_end_clean();
    header('Content-Type: application/json');
    http_response_code(403);
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$targetUuid = isset($input['uuid']) ? $input['uuid'] : '';
$newContentHtml = isset($input['content']) ? $input['content'] : '';
$newTitle = isset($input['title']) ? $input['title'] : '';

if (empty($targetUuid) || empty($newContentHtml)) {
    ob_end_clean();
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(['error' => 'Missing uuid or content']);
    exit;
}

$entry = \IdnoPlugins\Text\Entry::getByUUID($targetUuid);

if (empty($entry)) {
    ob_end_clean();
    header('Content-Type: application/json');
    http_response_code(404);
    echo json_encode(['error' => 'Post not found']);
    exit;
}

$currentUser = \Idno\Core\Idno::site()->session()->currentUser();
if ($entry->getOwner()->getUUID() != $currentUser->getUUID()) {
    ob_end_clean();
    header('Content-Type: application/json');
    http_response_code(403);
    echo json_encode(['error' => 'You do not own this post']);
    exit;
}

class MinimalAppendPage extends \Idno\Common\Page {}
\Idno\Core\Idno::site()->setCurrentPage(new MinimalAppendPage());

$titleHeading = '';
if (!empty($newTitle)) {
    $titleHeading = '<h3><b>' . htmlspecialchars($newTitle, ENT_QUOTES, 'UTF-8') . '</b></h3>';
}

$entry->body = $entry->body . $titleHeading . $newContentHtml;

$success = $entry->publish();

ob_end_clean();
header('Content-Type: application/json');

if ($success) {
    echo json_encode(['success' => true, 'url' => $entry->getURL()]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save']);
}
