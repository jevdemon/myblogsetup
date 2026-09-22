<?php
$cacheFile = $_SERVER['DOCUMENT_ROOT'] . '/blog/gfx/linkblog-cache.xml';
$cacheMaxAge = 900;
$feedUrl = 'http://data.feedland.org/feeds/jevdemon1.xml';

$xmlContent = null;
if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheMaxAge)) {
    $xmlContent = file_get_contents($cacheFile);
} else {
    $context = stream_context_create(['http' => ['timeout' => 5]]);
    $fetched = @file_get_contents($feedUrl, false, $context);
    if ($fetched !== false) {
        $xmlContent = $fetched;
        @file_put_contents($cacheFile, $fetched);
    } elseif (file_exists($cacheFile)) {
        $xmlContent = file_get_contents($cacheFile);
    }
}

if ($xmlContent) {
    $xml = @simplexml_load_string($xmlContent);
    if ($xml && isset($xml->channel->item)) {
        echo '<ul>';
        $count = 0;
        foreach ($xml->channel->item as $item) {
            if ($count >= 12) break;
            $title = htmlspecialchars((string)$item->title);
            $itemPermalink = htmlspecialchars((string)$item->guid);
            echo '<li><a href="' . $itemPermalink . '" target="_blank">' . $title . '</a></li>';
            $count++;
        }
        echo '</ul>';
    }
}
?>
