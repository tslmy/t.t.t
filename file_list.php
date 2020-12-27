<?php
    //doing the page number math START
    if (isset($_GET["page"])) {
        //try to get the target page number
        $this_page=(int)$_GET["page"];
    } else {
        //if failed, then the user has reached here by typing just the domain.
        $this_page=1;
    }
    //doing the page number math END

    $num_items_to_skip=($this_page-1)*constant('ITEMS_DISPLAYED_PEER_PAGE');
    $count=0;//set counter to zero
    $items_limit=$num_items_to_skip+constant('ITEMS_DISPLAYED_PEER_PAGE');
    $current_date_year='';
    $current_date_month='';
    foreach ($files as $this_file_path) {
        if ($count>=$items_limit) {
            break;
        }
        // else:
        $count++;
        if ($count<=$num_items_to_skip) {
            continue;
        }
        // else:
        $this_title=basename($this_file_path, '.txt'); //'title'
        if (substr($this_title, 0, 1)=='_') {
            //omit txt files whose names start with "_"
            continue;
        };
        $this_mtime=filemtime($this_file_path);
        $this_dirname=dirname($this_file_path); //'content/essay/'
        $this_shorterpath=substr($this_file_path, 8, -4); //'essay/title'
        //labeling year and month END
        echo "<div";
        $assumed_img_path=substr($this_file_path, 0, strlen($this_file_path)-3).'jpg';
        if (file_exists($assumed_img_path)) {
            echo 'style=\'background-image:-webkit-gradient(linear,70% 0%, 100% 0%, from(rgba(255,255,255,1)), to(rgba(255,255,255,0))),url("'.$assumed_img_path.'");\' ';
        }
        echo ">
            <small>".date("Y M d (D) H:i", $this_mtime)."</small>
            <h2>
                <a href='view.php?name=".urlencode($this_shorterpath)."'>
                    ".$this_title."
                </a>
            </h2>
            <article>";//things to start a new block for a post

        if (constant('LIST_MODE')==0) {
            //0(default, takes up more CPU):  Renders everything from Markdown everytime they are needed.
            $file_content = get_content($this_file_path);
            $truncated = substr($file_content, 0, constant('PREVIEW_SIZE_IN_KB'));
            echo closetags($truncated);
        } else {
            //1(recommended, takes up more disk storage and PHP's writing permission): Make a HTML cache for every new/updated post when index.php finds one, and then everyone else reads directly from cache.
            $cache_dir='cache'.substr($this_dirname, 7, strlen($this_dirname));
            if (!is_dir($cache_dir)) {
                mkdir($cache_dir);
            }
            $cache_file_path="cache/".$this_shorterpath.".htm";
            if ((file_exists($cache_file_path)==false) or (filemtime($cache_file_path)<filemtime($this_file_path))) {
                //if the corresponding cache file does not exist or havn't been updated since the last time that this post changed
                if (function_exists('get_content')==false) {
                    include_once "stuff/get_content.php";
                }
                fwrite(fopen($cache_file_path, "w+"), get_content($this_file_path));
                echo "[NEW]";
            }
            echo closetags(fread(fopen($cache_file_path, "r"), constant('PREVIEW_SIZE_IN_KB')));
        }
        
        echo "...
            </article>
            <small>
                Published under: ".str_replace('/', ' &gt; ', substr($this_dirname, strlen($folder)+1, strlen($this_dirname)))."
            </small>
            <hr>
        </div>\n";
    }
?>