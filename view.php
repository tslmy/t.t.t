<!DOCTYPE html>
<html>
    
    <head>
        <title>
            <?php $name=$_GET[ "name"]; echo $name; ?>
                - Lilo^Log 0.1^100
        </title>
        <link href="stuff/style_view.css" rel="stylesheet" type="text/css" media="screen"/>
        <link href="stuff/style_view_print.css" rel="stylesheet" type="text/css" media="print"/>
		<link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>
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
        <div id="title">
            <a href="index.php">
                <?php echo $name; ?>
            </a>
        </div>
		<div id="paper">
				<nav>
			<div class="button" id="button_back"><a href="index.php">Back</a></div>
			<div class="button" id="button_txt"><a href="<?php echo $name.".txt";?>">.TXT</a></div>
			<div class="button" id="button_print"><a href="javascript:window.print() ">Print</a></div>
		</nav>
        <div id="context">
            <?php 
			$file_name=$name.'.txt'; 
			if( is_file( $file_name ) )
			{ 
			include_once "stuff/markdown.php"; 
			$context=file_get_contents( $file_name ); 
			echo Markdown($context);
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
    var disqus_shortname = 'tslmy';
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
		<!-- wumii start -->
<script type="text/javascript">
    var wumiiPermaLink = "http://the.tslmy.tk/view.php?name=<?php echo $name; ?>";
var wumiiTitle = <?php echo json_encode($title); ?>;
    var wumiiTags = ""; 
    var wumiiSitePrefix = "http://the.tslmy.tk/";
    var wumiiParams = "&num=5&mode=2&pf=JAVASCRIPT";
</script>
<script type="text/javascript" src="http://widget.wumii.com/ext/relatedItemsWidget.htm"></script>
<a href="http://www.wumii.com/widget/relatedItems.htm" style="border:0;">
</a>
<script type="text/javascript" id="wumiiRelatedItems"></script>
<!--wumii end-->
</div>

    </body>
</html>