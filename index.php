<!DOCTYPE html>
<html>
    <head>
	
        <title>
            <?php 
				require("stuff/config.php");
				echo constant('SITE_NAME');
				?>
        </title>
		<meta http-equiv="Content-Type" content="text/html; charset=GB2312" />
		<link href="/stuff/favicon.ico" rel="bookmark" type="image/x-icon" />
        <meta name="description" content="Tslmy's personal blog, powered by t.t.t, the simplest plain-text-based, database-free blog engine."
        />
        <meta name="keywords" content="t.t.t powered,blog,tslmy,personal,chinese,english,geek"
        />
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
        <div id="left">
		<div id="left_texts">
		<div id="logo">
                <a href="http://tslmy.tk">
                    <?php echo constant('SITE_NAME');?>
                </a>
		</div>
            <div id="intro">
           <?php
		   $intro_file_name="content/_intro.txt";
		   if(file_exists($intro_file_name)){
				$file=fopen( $intro_file_name, "r");
				while(!feof($file))
				{
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
        <div id="main">
<?php
if ($handler = opendir("content/")){ 
	while (false !== ($filename = readdir($handler))) { 
		$len=strlen($filename);
		if (substr($filename,0,1)!="_" && strtolower(substr($filename,$len-4,$len))==".txt") {
			$files[filemtime("content/".$filename)] = substr($filename,0,$len-4);
		}
	}
	krsort($files,SORT_NUMERIC);
	foreach ($files as $each_one){
		echo 
		"<a class='item' href='view.php?name=".$each_one."'>
		".$each_one."</a>
			<!--div class='brick'></div-->
				
			<div class='hr'></div>
		
		\n"
		;
	}
	closedir($handler);
}else {
	echo "Error occured. Contact tslmy!";
}
?>
        </div>
    </body>

</html>