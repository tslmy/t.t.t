<?php

// XML sitemap: the home page and every post.

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . "/../src/commons.php";

$posts = published_posts(CONTENT_ABS_DIR, constant('EXT_ALLOWED'), constant('SHOW_DRAFTS'));
$pages = [['url' => absolute_url($_SERVER), 'updated' => $posts[0]['date'] ?? 0]];
foreach ($posts as $post) {
    $pages[] = ['url' => absolute_url($_SERVER, 'view.php?name='.rawurlencode($post['rel'])), 'updated' => $post['date']];
}

header('Content-Type: application/xml; charset=UTF-8');
echo sitemap_xml($pages);
