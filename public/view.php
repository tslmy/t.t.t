<?php

// Old post URLs (view.php?name=essay/title.md) are redirected to /post/essay/title.

require __DIR__ . '/../vendor/autoload.php';

Ttt\App::create($_SERVER)->handle($_SERVER['REQUEST_URI'] ?? '/view.php', $_GET)->send();
