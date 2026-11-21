<?php

    require __DIR__ . '/../vendor/autoload.php';
    require "../src/commons.php";

    if (!isset($_GET["name"]) || !is_string($_GET["name"])) {
        abort_with(400, 'Missing post name.');
    }
    $path = resolve_inside(CONTENT_ABS_DIR, $_GET["name"]);
    if ($path === null || !is_file($path)) {
        abort_with(404, 'Not found.');
    }
    $extn = pathinfo($path, PATHINFO_EXTENSION);
    if (!in_array($extn, constant('EXT_ALLOWED'))) {
        abort_with(403, 'Forbidden.');
    }
    $file_name = substr($path, strlen(getcwd())+1); # content/Lorem Ipsum.md
    $get_name = substr($path, strlen(CONTENT_ABS_DIR)+1, strrpos($path, "."));

    $file_path = pathinfo($get_name);
    $display_dir = $file_path['dirname'];
    $basename = substr($file_path['basename'], 0, -strlen($extn)-1);

    $nav_breadcrumb = '';
    if (!in_array($display_dir, ['', '.'])) {
        $nav_breadcrumb .= "You are at: ";
        $paths = explode('/', $display_dir);
        $nav_breadcrumb .= print_breadcrumb($paths, '/'.$display_dir);
    }

    use Twig\Environment;
    use Twig\Loader\FilesystemLoader;

    $loader = new FilesystemLoader(__DIR__ . '/../templates');
    $twig = new Environment($loader);

    echo $twig->render('view.html.twig', [
        'analytics_domain' => constant('ANALYTICS_DOMAIN'),
        'analytics_script_url' => constant('ANALYTICS_SCRIPT_URL'),
        'basename' => $basename,
        'site_name' => constant('SITE_NAME'),
        'nav_breadcrumb' => $nav_breadcrumb,
        'date_str' => date("Y M d (D) H:i", filemtime($file_name)),
        'content' => get_content($file_name, -1),
        'disqus' => constant('DISQUS_SHORTNAME'),
        'file_name' => $file_name,
    ]);
