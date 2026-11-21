<?php
    /**
     * Main index file for content browsing functionality
     * Displays files from content directory with pagination support
     */

    // Load required dependencies
    require __DIR__ . '/../vendor/autoload.php';
    require "../src/commons.php";

    // Get folder parameter from URL or default to empty string
    $subfolder = isset($_GET["folder"]) && is_string($_GET["folder"]) ? $_GET["folder"] : '';
    $path = resolve_inside(CONTENT_ABS_DIR, $subfolder);

    // Missing paths and directory traversal attempts both look like "not found".
    // Also ensure the path is a directory, not a file.
    if ($path === null || !is_dir($path)) {
        abort_with(404, 'Not found.');
    }

    // Get relative path from current working directory
    $folder = substr($path, strlen(getcwd())+1);

    // Initialize file collection
    $files = array();
    $allowed = constant('EXT_ALLOWED');

    // Recursively iterate through the directory to collect files
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($folder, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $extn = $file->getExtension();
            // Only include files with allowed extensions
            if (in_array($extn, $allowed)) {
                $files[$file->getPathname()] = $file->getMTime();
            }
        }
    }

    // Sort files by modification time (newest first)
    arsort($files, SORT_NUMERIC);

    // Import Pagerfanta for pagination
    use Pagerfanta\Adapter\ArrayAdapter;
    use Pagerfanta\Pagerfanta;

    // Configure pagination
    $adapter = new ArrayAdapter($files);
    $pagerfanta = new Pagerfanta($adapter);
    $pagerfanta->setMaxPerPage(constant('ITEMS_DISPLAYED_PER_PAGE'));

    // Handle page parameter and validate it
    $page = 1;
    if (isset($_GET["page"])) {
        $page = (int)$_GET["page"];
        // Validate page number
        if ($page < 1) {
            $page = 1;
        } elseif ($page > $pagerfanta->getNbPages()) {
            $page = $pagerfanta->getNbPages();
        }
        $pagerfanta->setCurrentPage($page);
    }

    // Set up page title
    $head_title = constant('SITE_NAME');
    $display_dir = substr($folder, strlen(CONTENT_REL_DIR)+1);
    if ($display_dir!='') {
        // Add current directory to title if not in root
        $head_title .= ' - '.str_replace('/', '&gt;', $display_dir);
    }

    // Generate breadcrumb navigation
    $nav_breadcrumb = '';
    if (!in_array($display_dir, ['', '.'])) {
        $nav_breadcrumb .= "You are at: ";
        $paths = explode('/', $display_dir);
        $nav_breadcrumb .= print_breadcrumb($paths, '/'.$display_dir);
    }

    // Process file information for the current page
    $paths_to_mtime = iterator_to_array($pagerfanta->getCurrentPageResults());
    $paths_to_metadata = array();
    foreach ($paths_to_mtime as $path => $mtime) {
        // Extract relative path information
        $rel_path=substr($path, strlen(CONTENT_REL_DIR)+1); //'essay/title.txt'
        $rel_dir=dirname($rel_path);

        // Get file content preview
        $file_content = get_content($path, constant('PREVIEW_SIZE_IN_BYTES'));

        // Generate breadcrumb for this file
        $path_tags=explode('/', $rel_dir);
        $breadcrumb = print_breadcrumb($path_tags, '');

        // Store file metadata for template rendering
        $paths_to_metadata[$path] = array(
            'date_str' => date("Y M d (D) H:i", $mtime),
            'rel_path' => $rel_path,
            'rel_dir' => $rel_dir,
            'breadcrumb' => $breadcrumb,
            'filename' => pathinfo($rel_path, PATHINFO_FILENAME),
            'preview' => $file_content,
        );
    }

    // Set up Twig templating
    use Twig\Environment;
    use Twig\Loader\FilesystemLoader;

    $loader = new FilesystemLoader(__DIR__ . '/../templates');
    $twig = new Environment($loader);

    // Render the template with all collected data
    echo $twig->render('index.html.twig', [
        'analytics_domain' => constant('ANALYTICS_DOMAIN'),
        'analytics_script_url' => constant('ANALYTICS_SCRIPT_URL'),
        'head_title' => $head_title,
        'nav_breadcrumb' => $nav_breadcrumb,
        'site_name' => constant('SITE_NAME'),
        'site_desc' => constant('SITE_DESC'),
        'have_to_paginate' => $pagerfanta->haveToPaginate(),
        'num_pages' => $pagerfanta->getNbPages(),
        'page' => $page,
        'paths_to_metadata' => $paths_to_metadata,
    ]);
