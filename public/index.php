<?php
    /**
     * Main index file for content browsing functionality
     * Displays files from content directory with pagination support
     */

    // Load required dependencies
    require __DIR__ . '/../vendor/autoload.php';
    require "../src/commons.php";

    // Get folder parameter from URL or default to empty string
    $subfolder = isset($_GET["folder"]) ? $_GET["folder"] : '';
    $path = realpath($content_abs_dir.'/'.$subfolder);

    // Security check: prevent directory traversal attacks
    if (!str_starts_with($path, $content_abs_dir)) {
        http_response_code(403);
        die;
    }

    // Ensure the path is a directory
    if (!is_dir($path)) {
        # to eliminate the possibility that $path is a file.
        http_response_code(404);
        die;
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
    $display_dir = substr($folder, strlen($content_dir)+1);
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
    $paths_to_mtime = $pagerfanta->getCurrentPageResults();
    $paths_to_metadata = array();
    foreach ($paths_to_mtime as $path => $mtime) {
        // Extract relative path information
        $rel_path=substr($path, strlen($content_dir)+1); //'essay/title.txt'
        $rel_dir=dirname($rel_path);

        // Get file content preview
        $file_content = get_content($path, constant('PREVIEW_SIZE_IN_KB'));

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
