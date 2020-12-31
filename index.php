<?php
    require __DIR__ . '/vendor/autoload.php';
    require "commons.php";

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
?>
<!DOCTYPE html>
<html>
    <head>
        <title><?php
        echo constant('SITE_NAME');
        $display_dir = substr($folder, strlen($content_dir)+1);
        if ($display_dir!='') {
            echo ' - '.str_replace('/', '&gt;', $d);
        }
        ?></title>
        <?php include 'head.php'; ?>
    </head>
    <body>
        <header>
            <nav>
                <a href="index.php"><img src="favicon-32x32.png" /></a>
                <ul>
                    <?php
                        if (!in_array($display_dir, ['', '.'])) {
                            echo "You are at: ";
                            $paths = explode('/', $display_dir);
                            print_breadcrumb($paths, '/'.$display_dir);
                        }
                    ?>
                </ul>
            </nav>
            <h1> <?php echo constant('SITE_NAME');?> </h1>
            <p> <?php echo constant('SITE_DESC');?> </p>
        </header>
        <main>
            <?php
                $paths = $pagerfanta->getCurrentPageResults();
                foreach ($paths as $path => $mtime) {
                    $rel_path=substr($path, strlen($content_dir)+1); //'essay/title.txt'
                    $rel_dir=dirname($rel_path);
                    //labeling year and month END
                    $file_content = get_content($path, constant('PREVIEW_SIZE_IN_KB'));
                    echo "
                        <div>
                            <small>".date("Y M d (D) H:i", $mtime)."</small>
                            <h2>
                                <a href='view.php?name=".urlencode($rel_path)."'>
                                    ".pathinfo($rel_path, PATHINFO_FILENAME)."
                                </a>
                            </h2>
                            <article>
                                ".$file_content."...
                            </article>";
                    if ($rel_dir!='.') {
                        echo "
                            <small>
                                Published under: ";
                            $path_tags=explode('/', $rel_dir);
                            print_breadcrumb($path_tags, '');
                        echo "
                            </small>";
                    }
                    echo "<hr>
                        </div>\n";
                }
            ?>
        </main>
        <footer>
            <?php
                if ($pagerfanta->haveToPaginate()) {
                    echo "
                        <nav>
                            Page
                            <ul>";
                    for ($i=1; $i<=$pagerfanta->getNbPages(); $i++) {
                        if ($i==$page) {
                            echo "<li>".$i."</li>";
                        } else {
                            echo "<li><a href='index.php?page=".$i."'>".$i."</a></li>";
                        }
                    }
                    echo "
                            </ul>
                        </nav>";
                }
            ?>
            <small>
                Copyright <?php echo constant('SITE_NAME');?>
            </small>
        </footer>
    </body>
</html>
