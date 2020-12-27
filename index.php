<?php
    date_default_timezone_set('UTC');
    require __DIR__ . '/vendor/autoload.php';
    require("stuff/config.php");

    function get_filetree($path)
    {
        $tree = array();
        foreach (glob($path.'/*') as $single) { //edit this line for different file formats!
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
        include_once "stuff/get_content.php";
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
        <meta name="description" content="t.t.t, the simplest plain-text-based, database-free blog engine."
        />
        <meta name="keywords" content="t.t.t, blog"
        />
        <link href="stuff/favicon.ico" rel="bookmark" type="image/x-icon" />
        <link href="stuff/css/index.css" rel="stylesheet" type="text/css" />
    </head>
    <body>
        <div id="main">
            <div class="item" id="search">
                <select data-placeholder="Quick Enterance" class="chzn-select">
                <option></option> 
                <?php
                foreach ($files as $this_file_path) {
                    $file_name=basename($this_file_path, '.txt');
                    if (substr($file_name, 0, 1)=='_') {
                        continue;
                    }
                    echo "<option value='".urlencode(substr($this_file_path, 8, -4))."'>".$file_name."</option>";
                }
                ?>
                </select>
                <div id="paths">
                    <?php
                    $paths=explode('/', substr($folder, 8, strlen($folder)));
                    $absolute_path='';
                    foreach ($paths as $each_folder) {
                        if ($each_folder!='') {
                            $absolute_path = $absolute_path.'/'.$each_folder;
                            echo '<a class="path" href="index.php?folder='.urlencode($absolute_path).'">'.$each_folder.'</a>';
                        }
                    }
                    ?>
                </div>
            </div>
            <?php include_once "file_list.php"; ?>
        </div>
        <div id="left">
            <div id="left_texts">
                <div id="logo">
                    <a href="index.php">
                        <?php echo constant('SITE_NAME');?>
                    </a>
                </div>
                <div id="nav_holder">
                    <?php
                        $url=$_SERVER["REQUEST_URI"]; //get the current URL
                        $max_page_number=ceil(count($files)/constant('ITEMS_DISPLAYED_PEER_PAGE'));
                        if ($max_page_number>1) {
                            for ($page_number=1; $page_number<=$max_page_number; $page_number++) {
                                if ($page_number==$this_page) {
                                    echo "<a class='nav_button nav_button_current'>".$page_number."</a>";
                                } else {
                                    echo "<a class='nav_button' href='index.php?page=".$page_number."'>".$page_number."</a>";
                                }
                            }
                        }
                    ?>
                </div>
                <div id="intro">
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
                </div>
            </div>
       </div>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js" integrity="sha512-bLT0Qm9VnAYZDflyKcBaQ2gg0hSYNQrJ8RilYldYQ1FxQYoCLtUjuuRuZo+fjqhx/qtq/1itJ0C2ejDxltZVFg==" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js" integrity="sha512-rMGGF4wg1R73ehtnxXBt5mbUfN9JUJwbk21KMlnLZDJh7BkPmeovBuddZCENJddHYYMkCh9hPFnPmS9sspki8g==" crossorigin="anonymous"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css" integrity="sha512-yVvxUQV0QESBt1SyZbNJMAwyKvFTLMyXSyBHDO4BG5t7k/Lw34tyqlSDlKIrIENIzCl+RVUNjmCPG+V/GMesRw==" crossorigin="anonymous" />
        <script type="text/javascript"> 
            $(".chzn-select").chosen().change(function(){window.location.href = 'view.php?name='+chose_get_value('.chzn-select');});
            function chose_get_value(select){
                return $(select).val();
            }
            function chose_get_text(select){
                return $(select+" option:selected").text();
            }
        </script>
    </body>
</html>
