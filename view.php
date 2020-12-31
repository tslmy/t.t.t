<?php
    require __DIR__ . '/vendor/autoload.php';
    require "commons.php";

    if (!isset($_GET["name"])) {
        http_response_code(400);
        die;
    }
    $path = realpath($content_abs_dir.'/'.$_GET["name"].'.txt');
    if (!str_starts_with($path, $content_abs_dir)) {
        http_response_code(403);
        die;
    }
    if (!is_file($path)) {
        # to eliminate the possibility that $path is a directory.
        http_response_code(404);
        die;
    }
    $file_name = substr($path, strlen(getcwd())+1);
    $get_name = substr($file_name, 0, -4);

    $file_path = pathinfo(substr($get_name, strlen($content_dir)));
?>
<!DOCTYPE html>
<html>
    <head>
        <title>
            <?php echo str_replace('/', '&gt;', $get_name); ?> - <?php echo constant('SITE_NAME'); ?>
        </title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel="stylesheet" href="https://unpkg.com/mvp.css">
        <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
        <link rel="manifest" href="/site.webmanifest">
        <!-- code syntax highlighter START-->
        <link rel="stylesheet" href="http://yandex.st/highlightjs/7.3/styles/solarized_light.min.css">
        <script src="http://yandex.st/highlightjs/7.3/highlight.min.js"></script>
        <script>
            hljs.tabReplace = '    ';
            hljs.initHighlightingOnLoad();
        </script>
        <!-- code syntax highlighter END-->
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
                        $path=$file_path['dirname'];
                        if ($path!='.') {//if not the root folder "content"
                            $path_tags=explode('/', $path);
                            $absolute_path='';
                            foreach ($path_tags as $each_tag) {
                                if ($each_tag=='') {
                                    continue;
                                }
                                $absolute_path = $absolute_path.'/'.$each_tag;
                                echo '&gt; <li><a href="index.php?folder='.urlencode($absolute_path).'">'.$each_tag.'</a></li>';
                            }
                        }
                    ?>
                </ul>
            </nav>
            <h1>
                <?php echo $file_path['basename']; ?>
            </h1>
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