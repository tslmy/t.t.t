<?php

// Atom feed of the newest posts.

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . "/../src/commons.php";

const FEED_ITEMS = 20;

$entries = [];
foreach (array_slice(published_posts(CONTENT_ABS_DIR, constant('EXT_ALLOWED'), constant('SHOW_DRAFTS')), 0, FEED_ITEMS) as $post) {
    // Posts link to each other with relative URLs; a feed reader needs absolute ones.
    $absolute_links = fn (string $html): string => str_replace('href="view.php?', 'href="'.absolute_url($_SERVER, 'view.php?'), $html);
    $entries[] = [
        'title' => $post['title'],
        'url' => absolute_url($_SERVER, 'view.php?name='.rawurlencode($post['rel'])),
        'updated' => $post['date'],
        'summary' => $post['summary'] !== '' ? $absolute_links(render_markdown($post['summary'])) : '',
        'content' => $absolute_links(get_content($post['path'], -1)),
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
