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
?>
<!DOCTYPE html>
<html>
    <head>
        <title>
            <?php echo $basename; ?> - <?php echo constant('SITE_NAME'); ?>
        </title>
        <!-- code syntax highlighter START-->
        <link rel="stylesheet" href="http://yandex.st/highlightjs/7.3/styles/solarized_light.min.css">
        <script src="http://yandex.st/highlightjs/7.3/highlight.min.js"></script>
        <script>
            hljs.tabReplace = '    ';
            hljs.initHighlightingOnLoad();
        </script>
        <!-- code syntax highlighter END-->
        <?php include '../src/head.php'; ?>
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
                            echo print_breadcrumb($paths, '');
                        }
                    ?>
                </ul>
            </nav>
            <h1> <?php echo $basename;?> </h1>
            <p> Published at <?php echo date("Y M d (D) H:i", filemtime($file_name));?> </p>
        </header>
        <main>
            <article>
                <?php
                    echo get_content($file_name, -1);
                ?>
            </article>
            <hr>
            <div id="attach_paper">
                <!-- disqus start -->
                <div id="disqus_thread"></div>
                <script type="text/javascript">
                    /* * * CONFIGURATION VARIABLES: EDIT BEFORE PASTING INTO YOUR WEBPAGE * * */
                    var disqus_shortname = '<?php echo constant('DISQUS_SHORTNAME'); ?>';

                    /* * * DON'T EDIT BELOW THIS LINE * * */
                    (function() {
                        var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
                        dsq.src = '//' + disqus_shortname + '.disqus.com/embed.js';
                        (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
                    })();
                </script>
                <noscript>Please enable JavaScript to view the <a href="http://disqus.com/?ref_noscript">comments powered by Disqus.</a></noscript>
                <!-- disqus end -->
            </div>
        </main>
        <footer>
            <a href='<?php echo $file_name; ?>'>Source</a>
        </footer>
    </body>
</html>