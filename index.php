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
		<link href="/stuff/favicon.ico" rel="bookmark" type="image/x-icon" />
        <meta name="description" content="Tslmy's personal blog, powered by t.t.t, the simplest plain-text-based, database-free blog engine."
        />
        <meta name="keywords" content="t.t.t powered,blog,tslmy,personal,chinese,english,geek"
        />
		<link href='http://fonts.googleapis.com/css?family=Muli' rel='stylesheet' type='text/css'>
        <link href="stuff/style_list.css" rel="stylesheet" type="text/css" />
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
		<div id="web_background"></div>
        <div id="main">
			<?php
			if ($handler = opendir("content/")){  //try to open the directory.
				while (false !== ($filename = readdir($handler))) {//for each file in this directory
					$len=strlen($filename);//get the length of the file name for the next step
					if (substr($filename,0,1)!="_" && strtolower(substr($filename,$len-4,$len))==".txt") { //if this file is not intended to be omitted and it's a .txt file 
						$files[filemtime("content/".$filename)] = substr($filename,0,$len-4); //then put it into the file array with its Last Modified Time as its number
					}
				}
				krsort($files,SORT_NUMERIC);//sort the array out

				if (isset($_GET["page"])){ //try to get the target page number
					$this_page=$_GET["page"];
				}else{
					$this_page=1;
				}

				$prev_items_to_omit=($this_page-1)*constant('ITEMS_DISPLAYED_PEER_PAGE');
				$count=0;//set counter to zero
				$items_limit=$prev_items_to_omit+constant('ITEMS_DISPLAYED_PEER_PAGE');
				foreach ($files as $each_one){
					if ($count<$items_limit){
						$count++;
						if ($count>$prev_items_to_omit){
							echo 
							"<div class='item'>
								<a href='view.php?name=".$each_one."'>
									<div class='name'>
										".$each_one."
									</div>
									<div class='mtime'>
										".date("Y-m-d H:i:s",filemtime("content/".$filename))."
									</div>
								</a>
								<div class='hr'></div>
							</div>
							\n";
						}
					}
					else
					{
						break;
					}
				}
			}else { //if failed to load the directory.
				echo "Error occured. Contact tslmy!";
			}
			?>
		</div>
		<div id="left">
			<div id="left_texts">
				<div id="logo">
					<a href="http://tslmy.users.sf.net">
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
							echo "Just another t.t.t-powered minimal blog.";
						}
					?>
				</div>
			</div>
       </div>
    </body>

</html>