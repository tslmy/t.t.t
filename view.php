<?php 
$get_name=$_GET["name"];
$file_path = pathinfo($_GET["name"]);
if (!file_exists('content/'.$get_name.'.txt')) {
	header("location:stuff/pages/404/");
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>
            <?php echo str_replace('/','&gt;',$get_name); ?>
                - <?php 
				require("stuff/config.php");
				echo constant('SITE_NAME');
				?>
        </title>
        <link href="stuff/css/view.css" rel="stylesheet" type="text/css" media="screen"/>
        <link href="stuff/css/view_print.css" rel="stylesheet" type="text/css" media="print"/>
		<link href="/stuff/favicon.ico" rel="bookmark" type="image/x-icon" />
		<link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>
		<!-- code syntax highlighter START-->
		<script src="stuff/highlight/highlight.pack.js"></script>
		<link rel="stylesheet" href="stuff/highlight/solarized_light.css">
		<script>
		    hljs.tabReplace = '    ';
		    hljs.initHighlightingOnLoad();
		</script>
		<!-- code syntax highlighter END-->
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta name="description" content="An article about <?php echo $get_name; ?> on <?php echo constant('SITE_NAME'); ?>."
        />
        <meta name="keywords" content="<?php echo constant('SITE_NAME'); ?>,t.t.t-powered,blog,tslmy,minimal-design"
        />
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
    
    <body onload="prettyPrint()">
        <div id="title">
            <a href="index.php">
                <?php echo $file_path['basename']; ?>
            </a>
        </div>
		<div id="paper">
				<nav>
			<div class="button" onclick="location.href='index.php'">Back</div>
			<div class="button" onclick="location.href='<?php
			$file_name='content/'.$get_name.'.txt'; 
			echo $file_name;?>'">.TXT</div>
			<div class="button" onclick="javascript:window.print()">Print</div>
		</nav>
        <div id="context">
			
				<?php 
				$path=$file_path['dirname'];
				if ($path!='.'){//if not the root folder "content"
					echo '<div id="tags">';
					$path_tags=explode('/',$path);
					$absolute_path='';
					foreach ($path_tags as $each_tag) {
						if ($each_tag!=''){
							$absolute_path = $absolute_path.'/'.$each_tag;
							echo '<a class="tag" href="index.php?folder='.urlencode($absolute_path).'">'.$each_tag.'</a>';
						}
					}
					echo '</div>';
				}
				?>
			
			<?php
			$content="404 Error Occured. Bazinga!";
			if (constant('LIST_MODE')==0){
				if( is_file( $file_name ) )
				{ 
					include_once "stuff/markdown.php";
					include_once "stuff/smartypants.php";
					include_once "stuff/get_content.php";
					echo get_content($file_name);
				}
			} else {
				echo file_get_contents( 'cache/'.$get_name.'.htm' );
			}
			?>
		</div>
        </div>
<div id="attach_paper">
		<!-- JiaThis Button BEGIN -->
<div id="ckepop">
<a href="http://www.jiathis.com/share" class="jiathis jiathis_txt jiathis_separator jtico jtico_jiathis" target="_blank"></a>
</div>
<script type="text/javascript" >
var jiathis_config={
	hideMore:true
}
</script>
<script type="text/javascript" src="http://v2.jiathis.com/code/jia.js" charset="utf-8"></script>
<!-- JiaThis Button END -->
<!-- disqus start -->
<div id="disqus_thread">
<script type="text/javascript">
    var disqus_shortname = "<?php echo constant('DISQUS_SHORTNAME'); ?>";
    (function() {
        var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
        dsq.src = 'http://' + disqus_shortname + '.disqus.com/embed.js';
        (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
    })();
</script>
<noscript>JavaScript is required to load comments.</noscript>
</div>
<!-- disqus end -->
<hr>
	<!-- linkwithin start -->
	<script>
		var linkwithin_site_id = 932511;
	</script>
	<script src="http://www.linkwithin.com/widget.js"></script>
	<a href="http://www.linkwithin.com/">
		<img src="http://www.linkwithin.com/pixel.png" alt="Related Posts Plugin for WordPress, Blogger..." style="border: 0" />
	</a>
	<!--linkwithin end-->
</div>
</body>
</html>