<?php

    require __DIR__ . '/../vendor/autoload.php';
    require "../src/commons.php";

    if (!isset($_GET["name"])) {
        http_response_code(400);
        die;
    }
    $path = realpath($content_abs_dir.'/'.$_GET["name"]);
    if (!str_starts_with($path, $content_abs_dir)) {
        http_response_code(403);
        die;
    }
    $extn = pathinfo($path, PATHINFO_EXTENSION);
    if (!in_array($extn, constant('EXT_ALLOWED'))) {
        http_response_code(403);
        die;
    }
    if (!is_file($path)) {
        # to eliminate the possibility that $path is a directory.
        http_response_code(404);
        die;
    }
    $file_name = substr($path, strlen(getcwd())+1); # content/Lorem Ipsum.md
    $get_name = substr($path, strlen($content_abs_dir)+1, strrpos($path, "."));

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
        'ga_id' => constant('GA_ID'),
        'basename' => $basename,
        'site_name' => constant('SITE_NAME'),
        'nav_breadcrumb' => $nav_breadcrumb,
        'date_str' => date("Y M d (D) H:i", filemtime($file_name)),
        'content' => get_content($file_name, -1),
        'disqus' => constant('DISQUS_SHORTNAME'),
        'file_name' => $file_name,
    ]);
