<?php
    require __DIR__ . '/vendor/autoload.php';
    require("config.php");

    function get_filetree($path)
    {
        $tree = array();
        foreach (glob($path.'/*') as $single) {
            if (is_dir($single)) {
                $tree = array_merge($tree, get_filetree($single));
            } elseif (strtolower(substr($single, -4)) == '.txt') {
                $tree[$single] =  filemtime($single);
            }
        }
        return $tree;
    }
    if (isset($_GET["folder"])) { //try to get the target page number
        //securing START
        $exploded_path=explode('/', $_GET["folder"]);
        $folder='';
        foreach ($exploded_path as $each_exploded_path) {
            if ($each_exploded_path!='..') {
                $folder=$folder.'/'.$each_exploded_path;
            }
        }
        //securing END
        $folder='content'.$folder;
    } else {//if failed, then the user has reached here by typing just the domain.
        $folder='content';
    }

    $files=get_filetree($folder);
    arsort($files, SORT_NUMERIC);
    $files=array_flip($files);

    if (constant('LIST_MODE')==0) {
        include_once "get_content.php";
    }
    
    function closetags($html)
    {
        /*get all content BEFORE the last "<", ensuring every HTML tag in the content is finished with a ">"*/
        $html = preg_replace("~<[^<>]+?$~i", "", $html);
        /*start to finish all unfinished tags*/
        #put all opened tags into an array
        preg_match_all("#<([a-z]+)( .*[^/])?(?!/)>#iU", $html, $result);
        $openedtags = $result[1];
        #put all closed tags into an array
        preg_match_all("#</([a-z]+)>#iU", $html, $result);
        $closedtags = $result[1];
        $len_opened = count($openedtags);
        # all tags are closed
        if (count($closedtags) == $len_opened) {
            return $html;
        }
        $openedtags = array_reverse($openedtags);
        # close tags
        for ($i = 0; $i < $len_opened; $i++) {
            if (!in_array($openedtags[$i], $closedtags)) {
                $html .= '</' . $openedtags[$i] . '>';
            } else {
                unset($closedtags[array_search($openedtags[$i], $closedtags)]);
            }
        }
        return $html;
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
                <a href="/"><img src="favicon-32x32.png" /></a>
                <ul>
                    You are at:
                    <li><a href="/">Home</a></li>
                    <?php
                    $paths=explode('/', substr($folder, 8, strlen($folder)));
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
            <h1>
                <?php echo constant('SITE_NAME');?>
            </h1>
            <p>
                <?php
                    $intro_file_name="content/_intro.txt";
                    if (file_exists($intro_file_name)) {
                        $file=fopen($intro_file_name, "r");
                        while (!feof($file)) {
                            echo "<p>".fgets($file)."</p>";
                        }
                        fclose($file);
                    } else {
                        echo "Just another t.t.t-powered minimalist blog.";
                    }
                ?>
            </p>
        </header>
        <main>
            <?php include_once "file_list.php"; ?>
        </main>
        <footer>
            <nav>
                Page
                <ul>
                    <?php
                        $url=$_SERVER["REQUEST_URI"]; //get the current URL
                        $max_page_number=ceil(count($files)/constant('ITEMS_DISPLAYED_PEER_PAGE'));
                        if ($max_page_number>1) {
                            for ($page_number=1; $page_number<=$max_page_number; $page_number++) {
                                if ($page_number==$this_page) {
                                    echo "<li>".$page_number."</li>";
                                } else {
                                    echo "<li><a href='index.php?page=".$page_number."'>".$page_number."</a></li>";
                                }
                            }
                        }
                    ?>
                </ul>
            </nav>
            <small>
                Copyright <?php echo constant('SITE_NAME');?>
            </small>
        </footer>
    </body>
</html>
