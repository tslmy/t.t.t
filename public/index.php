<?php

// Front controller: every URL that isn't a real file ends up here.
//
// Apache/Heroku: see public/.htaccess.
// PHP's built-in server needs this file as its router:
//     php -S localhost:9000 -t public public/index.php

require __DIR__ . '/../vendor/autoload.php';

if (PHP_SAPI === 'cli-server') {
    $requested = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (is_string($requested) && !str_ends_with($requested, '.php') && is_file(__DIR__ . $requested)) {
        return false;  // let the built-in server serve static files itself
    }
}

Ttt\App::create($_SERVER)->handle($_SERVER['REQUEST_URI'] ?? '/', $_GET)->send();
