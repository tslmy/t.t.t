<!DOCTYPE html>
<html>
    <head>
        <title>
            <?php 
				require("stuff/config.php");
				echo constant('SITE_NAME');
				?>
        </title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta name="description" content="Tslmy's personal blog, powered by t.t.t, the simplest plain-text-based, database-free blog engine."
        />
		<meta name="keywords" content="t.t.t powered,blog,tslmy,personal,chinese,english,geek"
        />
		<link href="stuff/favicon.ico" rel="bookmark" type="image/x-icon" />
		<link href="stuff/rss.php" type="application/atom+xml" rel="alternate" title="<?php echo constant('SITE_NAME'); ?> R.S.S." />
		<link rel="stylesheet" href="stuff/chosen/chosen.css" />
        <link href="stuff/css/list.css" rel="stylesheet" type="text/css" />
    </head>
    <body>
		<aside>
			<button>&#9762;</button>
		</aside>
			<?php
				class nodeFile {
					var $path;
					var $modifyTime;
				}
				class nodeFolder {
					var $path;
					var $content = array();
				}
				
				function get_filetree($path){ 
					$tree = array();
					foreach(glob($path.'/*') as $node){ //edit this line for different file formats!
						if(is_dir($node)){ //this node is a folder
							//initialize the sub-tree instance
							$this_instance = new nodeFolder;
							$this_instance->path = $node;
							$this_instance->content = get_filetree($node);
							//and push it into the main tree as a branch
							array_push($tree,$this_instance);
						} elseif (strtolower(substr($node,-4)) == '.txt') {
							//initialize the file instance
							$this_instance = new nodeFile;
							$this_instance->path = $node;
							$this_instance->modifyTime = filemtime($node);
							//and push it into the main tree as a node
							array_push($tree,$this_instance);
						} 
					} 
					return $tree; 
				}
				$tree = new nodeFolder;
				$tree->path = 'content';
				$tree->content = get_filetree('content');
				//var_dump($tree);
			?>
			<nav>
				<div id="logo">
					<a href="http://www.tslimi.tk/wiki/">
						<?php echo constant('SITE_NAME'); ?>
					</a>
				</div>
			<?php
				$count = 0;
				$items_limit = 100;
				$depth = 1;
				function getPathsLastPart($path) {
					$a = explode('/',$path);
					return $a[count($a)-1];
				}
				function makePaddingStr(){
					global $depth;
					return ' style="padding-left:'.($depth*20).'px" ';
				}
				function expandTree($branch){
					global $count, $items_limit, $depth;
					$this_path=$branch->path;
					$folderName = getPathsLastPart($this_path);
					echo '<section id="'.$folderName.'">';
					echo '<a'.makePaddingStr().' class="folder_link" href="index.php?folder='.urlencode($this_path).'">'.$folderName.'</a>';
					foreach ($branch->content as $node){
						if ($count<$items_limit){
							$count++;
							$this_path=$node->path;
							if (get_class($node)=='nodeFile') {
								$fileName=getPathsLastPart($this_path);
								echo '<a'.makePaddingStr().'href="view.php?name='.urlencode($this_path).'">'.substr($fileName,0,strlen($fileName)-4).'</a>';
							} else { //class($node)=='nodeFolder'
								$depth++;
								expandTree($node);
								$depth--;
							}
						}
						else
						{
							echo 'More...';
						}
					}
					echo '</section>';
				}
				expandTree($tree);
			?>
			</nav>
    </body>
</html>
