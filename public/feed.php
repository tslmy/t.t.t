<?php

// Atom feed of the newest posts.

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . "/../src/commons.php";

const FEED_ITEMS = 20;

$entries = [];
foreach (array_slice(list_post_files(CONTENT_ABS_DIR, constant('EXT_ALLOWED')), 0, FEED_ITEMS) as $post) {
    // Posts link to each other with relative URLs; a feed reader needs absolute ones.
    $html = str_replace('href="view.php?', 'href="'.absolute_url($_SERVER, 'view.php?'), get_content($post['path'], -1));
    $entries[] = [
        'title' => pathinfo($post['rel'], PATHINFO_FILENAME),
        'url' => absolute_url($_SERVER, 'view.php?name='.rawurlencode($post['rel'])),
        'updated' => $post['mtime'],
        'content' => $html,
    ];
}

header('Content-Type: application/atom+xml; charset=UTF-8');
echo atom_feed(
    constant('SITE_NAME'),
    constant('SITE_DESC'),
    absolute_url($_SERVER),
    absolute_url($_SERVER, 'feed.php'),
    $entries
);
