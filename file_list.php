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
        $file_content = get_content($this_file_path, constant('PREVIEW_SIZE_IN_KB'));
        echo "
            <div>
                <small>".date("Y M d (D) H:i", $this_mtime)."</small>
                <h2>
                    <a href='view.php?name=".urlencode($this_shorterpath)."'>
                        ".$this_title."
                    </a>
                </h2>
                <article>
                    ".$file_content."...
                </article>
                <small>
                    Published under: ".str_replace('/', ' &gt; ', substr($this_dirname, strlen($folder)+1, strlen($this_dirname)))."
                </small>
                <hr>
            </div>\n";
    }
?>