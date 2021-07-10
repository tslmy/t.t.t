<?php

    require __DIR__ . '/../vendor/autoload.php';
    require "../src/commons.php";

    $subfolder = isset($_GET["folder"]) ? $_GET["folder"] : '';
    $path = realpath($content_abs_dir.'/'.$subfolder);
    if (!str_starts_with($path, $content_abs_dir)) {
        http_response_code(403);
        die;
    }
    if (!is_dir($path)) {
        # to eliminate the possibility that $path is a file.
        http_response_code(404);
        die;
    }
    $folder = substr($path, strlen(getcwd())+1);

    function get_filetree($path)
    {
        $tree = array();
        foreach (glob($path.'/*') as $single) {
            if (is_dir($single)) {
                $tree = array_merge($tree, get_filetree($single));
            } else {
                $extn = pathinfo($single, PATHINFO_EXTENSION);
                if (in_array($extn, constant('EXT_ALLOWED'))) {
                    $tree[$single] = filemtime($single);
                }
            }
        }
        return $tree;
    }
    $files=get_filetree($folder);
    arsort($files, SORT_NUMERIC);

    use Pagerfanta\Adapter\ArrayAdapter;
    use Pagerfanta\Pagerfanta;

    $adapter = new ArrayAdapter($files);
    $pagerfanta = new Pagerfanta($adapter);
    $pagerfanta->setMaxPerPage(constant('ITEMS_DISPLAYED_PER_PAGE'));
    $page = 1;
    if (isset($_GET["page"])) {
        $page=(int)$_GET["page"];
        $pagerfanta->setCurrentPage((int)$_GET["page"]);
    }

    $head_title = constant('SITE_NAME');
    $display_dir = substr($folder, strlen($content_dir)+1);
    if ($display_dir!='') {
        $head_title .= ' - '.str_replace('/', '&gt;', $display_dir);
    }

    $nav_breadcrumb = '';
    if (!in_array($display_dir, ['', '.'])) {
        $nav_breadcrumb .= "You are at: ";
        $paths = explode('/', $display_dir);
        $nav_breadcrumb .= print_breadcrumb($paths, '/'.$display_dir);
    }

    $paths_to_mtime = $pagerfanta->getCurrentPageResults();
    $paths_to_metadata = array();
    foreach ($paths_to_mtime as $path => $mtime) {
        $rel_path=substr($path, strlen($content_dir)+1); //'essay/title.txt'
        $rel_dir=dirname($rel_path);
        //labeling year and month END
        $file_content = get_content($path, constant('PREVIEW_SIZE_IN_KB'));

        $path_tags=explode('/', $rel_dir);
        $breadcrumb = print_breadcrumb($path_tags, '');

        $paths_to_metadata[$path] = array(
            'date_str' => date("Y M d (D) H:i", $mtime),
            'rel_path' => $rel_path,
            'rel_dir' => $rel_dir,
            'breadcrumb' => $breadcrumb,
            'filename' => pathinfo($rel_path, PATHINFO_FILENAME),
            'preview' => $file_content,
        );
    }

    use Twig\Environment;
    use Twig\Loader\FilesystemLoader;

    $loader = new FilesystemLoader(__DIR__ . '/../templates');
    $twig = new Environment($loader);

    echo $twig->render('index.html.twig', [
        'ga_id' => constant('GA_ID'),
        'head_title' => $head_title,
        'nav_breadcrumb' => $nav_breadcrumb,
        'site_name' => constant('SITE_NAME'),
        'site_desc' => constant('SITE_DESC'),
        'have_to_paginate' => $pagerfanta->haveToPaginate(),
        'num_pages' => $pagerfanta->getNbPages(),
        'page' => $page,
        'paths_to_metadata' => $paths_to_metadata,
    ]);
