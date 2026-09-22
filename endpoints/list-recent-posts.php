<?php
require_once dirname(__FILE__) . '/Idno/start.php';

header('Content-Type: application/json');

if (!\Idno\Core\Idno::site()->session()->isLoggedIn()) {
    http_response_code(403);
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$config = \Idno\Core\Idno::site()->config();

try {
    $pdo = new PDO(
        'mysql:host=' . $config->dbhost . ';dbname=' . $config->dbname . ';charset=utf8mb4',
        $config->dbuser,
        $config->dbpass
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$ownerUuid = \Idno\Core\Idno::site()->session()->currentUser()->getUUID();

$stmt = $pdo->prepare("SELECT uuid, contents FROM entities WHERE entity_subtype = :subtype AND owner = :owner ORDER BY created DESC LIMIT 3");
$stmt->execute([
    'subtype' => 'IdnoPlugins\\Text\\Entry',
    'owner' => $ownerUuid
]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$results = [];
foreach ($rows as $row) {
    $data = json_decode($row['contents'], true);
    $title = 'Untitled';
    if (!empty($data['title'])) {
        $title = $data['title'];
    } elseif (!empty($data['body'])) {
        $title = trim(strip_tags(mb_substr($data['body'], 0, 60))) . '...';
    }

    $results[] = ['title' => $title, 'uuid' => $row['uuid']];
}

echo json_encode($results);
