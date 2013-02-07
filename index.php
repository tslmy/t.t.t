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
		<!--link href='http://fonts.googleapis.com/css?family=Muli' rel='stylesheet' type='text/css'-->
		<link rel="stylesheet" href="stuff/chosen/chosen.css" />
        <link href="stuff/css/list.css" rel="stylesheet" type="text/css" />
        <!-- below to </head>: Google Analytics Code. -->
        <script type="text/javascript">
            var _gaq = _gaq || [];
            _gaq.push(['_setAccount', 'UA-21290300-1']);
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
        <div id="main">
			<?php
				function get_filetree($path){ 
					$tree = array(); 
					foreach(glob($path.'/*') as $single){ //edit this line for different file formats!
						if(is_dir($single)){ 
							$tree = array_merge($tree,get_filetree($single)); 
						} elseif (strtolower(substr($single,-4)) == '.txt') {
							$tree[$single] =  filemtime ($single);
						} 
					} 
					return $tree; 
				} 
				if (isset($_GET["folder"])){ //try to get the target page number
					//securing START
					$exploded_path=explode('/',$_GET["folder"]);
					$folder='';
					foreach ($exploded_path as $each_exploded_path) {
						if ($each_exploded_path!='..'){
							$folder=$folder.'/'.$each_exploded_path;
						}
					}
					//securing END
					$folder='content'.$folder;
				}else{//if failed, then the user has reached here by typing just the domain.
					$folder='content';
				}

				$files=get_filetree($folder);
				arsort ($files,SORT_NUMERIC); 
				$files=array_flip($files);
				
				if (constant('LIST_MODE')==0) {
					include_once "stuff/markdown.php";
					include_once "stuff/smartypants.php";
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
					} ?>

				<div class="item" id="search">
					<select data-placeholder="Quick Enterance" class="chzn-select">
					<option value=""></option> 
					<?php 
					foreach ($files as $this_file_path){
						$file_name=basename($this_file_path,'.txt');
						if (substr($file_name,0,1)=='_') {continue;}
						echo "<option value='".urlencode(substr($this_file_path,8,-4))."'>".$file_name."</option>";
					}
					?>
					</select>
					<div id="paths">
						<?php 
						$paths=explode('/',substr($folder,8,strlen($folder)));
						$absolute_path='';
						foreach ($paths as $each_folder) {
							if ($each_folder!=''){
								$absolute_path = $absolute_path.'/'.$each_folder;
								echo '<a class="path" href="index.php?folder='.urlencode($absolute_path).'">'.$each_folder.'</a>';
							}
						}
						?>
					</div>
				   </div>
				<?php //doing the page number math START
				if (isset($_GET["page"])){ //try to get the target page number
					$this_page=(int)$_GET["page"];
				}else{//if failed, then the user has reached here by typing just the domain.
					$this_page=1;
				}
				//doing the page number math END
				$prev_items_to_omit=($this_page-1)*constant('ITEMS_DISPLAYED_PEER_PAGE');
				$count=0;//set counter to zero
				$items_limit=$prev_items_to_omit+constant('ITEMS_DISPLAYED_PEER_PAGE');
				$current_date_year='';
				$current_date_month='';
				foreach ($files as $this_file_path){
					if ($count<$items_limit){
						$count++;
						if ($count>$prev_items_to_omit){
							$this_title=basename($this_file_path,'.txt');		//'title'
							if (substr($this_title,0,1)=='_') {continue;};//omit the ones start with "_"
							//or strtolower(substr($this_file_path,strlen($this_file_path)-4,strlen($this_file_path)))!='.txt'
							//labeling year and month START
							$this_mtime=filemtime($this_file_path);
							$this_modified_year=date("Y",$this_mtime);
							$this_dirname=dirname($this_file_path);			//'content/essay/'
							$this_shorterpath=substr($this_file_path,8,-4);	//'essay/title'
							if ($current_date_year<>$this_modified_year) {
								echo "<div class='item date year'>".$this_modified_year.'</div>';
								$current_date_year=$this_modified_year;
								$current_date_month='';//reset month
							};
							$this_modified_month=date("F",$this_mtime);
							if ($current_date_month<>$this_modified_month) {
								echo "<div class='item date'>".$this_modified_month.'</div>';
								$current_date_month=$this_modified_month;
							};
							//labeling year and month END
							echo 	"<div class='item'";
							$assumed_img_path=substr($this_file_path,0,strlen($this_file_path)-3).'jpg';
							if (file_exists($assumed_img_path)){
								echo 'style=\'background-image:-webkit-gradient(linear,70% 0%, 100% 0%, from(rgba(255,255,255,1)), to(rgba(255,255,255,0))),url("'.$assumed_img_path.'");\' ';
							}
							echo		">
										<a href='view.php?name=".urlencode($this_shorterpath)."'>
											<span class='effect'>
												<!--span class='prefix'-->
													<span class='day'>".date("d",$this_mtime)."</span> 
												<!--/span-->
												<span class='name'>".$this_title."</span>
												<span class='tags'>".str_replace('/','&gt;',substr($this_dirname,strlen($folder)+1,strlen($this_dirname)))."</span>
											</span>
										</a>
										<article><span class='mtime'>".date("H:i",$this_mtime)."</span>";//things to start a new block for a post

							if (constant('LIST_MODE')==0) {//0(default, takes up more CPU):  Renders everything from Markdown everytime they are needed.
								echo closetags(substr(get_content($this_file_path),0,constant('PREVIEW_SIZE_IN_KB')));
							} else {//1(recommended, takes up more disk storage and PHP's writing permission): Make a HTML cache for every new/updated post when index.php finds one, and then everyone else reads directly from cache.
								$cache_dir='cache'.substr($this_dirname,7);
								if (!is_dir($cache_dir)){
									mkdir($cache_dir);
								}
								$cache_file_path="cache/".$this_shorterpath.".htm";
								if ((file_exists($cache_file_path)==false) or (filemtime($cache_file_path)<filemtime($this_file_path))) {
								//if the corresponding cache file does not exist or havn't been updated since the last time that this post changed
									if (function_exists('Markdown')==false){
										include_once "stuff/markdown.php";
										include_once "stuff/smartypants.php";
										include_once "stuff/get_content.php";
									}
									fwrite(fopen($cache_file_path,"w+"),get_content($this_file_path));//try "fgetss" sometime.
									echo "[NEW]";
								}
								echo closetags(fread(fopen($cache_file_path, "r"),constant('PREVIEW_SIZE_IN_KB')));
							}
							
							echo "...</article>
									 <div class='hr'></div>
								</div>\n";
						}
					}
					else
					{
						break;
					}
				}
			?>
		</div>
		<div id="left">
			<div id="left_texts">
				<div id="logo">
					<a href="http://the.tslimi.tk/">
						<?php echo constant('SITE_NAME');?>
					</a>
				</div>
				<div id="nav_holder">
					
						<?php
							$url=$_SERVER["REQUEST_URI"]; //get the current URL
							$max_page_number=ceil(count($files)/constant('ITEMS_DISPLAYED_PEER_PAGE'));
							//echo $max_page_number." pages for ".count($files)." items.<br/>";
							if ($max_page_number>1) {
								for ($page_number=1; $page_number<=$max_page_number; $page_number++){
									if ($page_number==$this_page) {
										echo "<div style='background:rgba(150,150,150,.5); pointer-events:none;' class='nav_button'><b>".$page_number." </b></div>";
									} else {
										echo "<a href='index.php?page=".$page_number."'><div class='nav_button'>".$page_number."</div></a>";
									}
								}
							}
						?>
				</div>
				<div id="intro">
				   <?php
				   $intro_file_name="content/_intro.txt";
				   if(file_exists($intro_file_name)){
						$file=fopen( $intro_file_name, "r");
						while(!feof($file))	{
							echo "<p>".fgets($file). "</p>";
						}
						fclose($file);
						} else {
							echo "Just another t.t.t-powered minimalist blog.";
						}
					?>
				</div>
			</div>
       </div>
  <script src="stuff/jquery-1.7.2.min.js" type="text/javascript"></script>
  <script src="stuff/chosen/chosen.jquery.min.js" type="text/javascript"></script>
  <script type="text/javascript"> 
	$(".chzn-select").chosen();
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
