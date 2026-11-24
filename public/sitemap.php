<?php

// XML sitemap: the home page and every post.

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . "/../src/commons.php";

$posts = list_post_files(CONTENT_ABS_DIR, constant('EXT_ALLOWED'));
$pages = [['url' => absolute_url($_SERVER), 'updated' => $posts[0]['mtime'] ?? 0]];
foreach ($posts as $post) {
    $pages[] = ['url' => absolute_url($_SERVER, 'view.php?name='.rawurlencode($post['rel'])), 'updated' => $post['mtime']];
}

header('Content-Type: application/xml; charset=UTF-8');
echo sitemap_xml($pages);
