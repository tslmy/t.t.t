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
        <title><?php echo constant('SITE_NAME');?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel="stylesheet" href="https://unpkg.com/mvp.css">
        <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
        <link rel="manifest" href="/site.webmanifest">
        <!-- below to </head>: Google Analytics Code. -->
        <script type="text/javascript">
            var _gaq = _gaq || [];
            _gaq.push(['_setAccount', '<?php echo constant('GA_ID'); ?>']);
            _gaq.push(['_trackPageview']); (function() {
                var ga = document.createElement('script');
                ga.type = 'text/javascript';
                ga.async = true;
                ga.src = ('https:' == document.location.protocol ? 'https://ssl': 'http://www') + '.google-analytics.com/ga.js';
                var s = document.getElementsByTagName('script')[0];
                s.parentNode.insertBefore(ga, s);
            })();
        </script>
    </head>
    <body>
        <header>
            <nav>
                <a href="index.php"><img src="favicon-32x32.png" /></a>
                <ul>
                    You are at:
                    <li><a href="index.php">Home</a></li>
                    <?php
                    $paths=explode('/', substr($folder, strlen($content_dir)+1, strlen($folder)));
                    $absolute_path='';
                    foreach ($paths as $each_folder) {
                        if ($each_folder!='') {
                            $absolute_path = $absolute_path.'/'.$each_folder;
                            echo '> <li><a href="index.php?folder='.urlencode($absolute_path).'">'.$each_folder.'</a></li>';
                        }
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
                                Published under: ".str_replace('/', ' &gt; ', $rel_dir)."
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
