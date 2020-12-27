<?php
define('SITE_NAME', 't.t.t');				//This defines the site's name.
define('ITEMS_DISPLAYED_PEER_PAGE', 10);		//How many posts are to be shown on each page of the index.
define('PREVIEW_SIZE_IN_KB', 1000);			//How much post previews to provide.
define('DISQUS_SHORTNAME', 'tslmy');			//disqus_shortname
define('GA_ID', 'UA-21290300-1');			//Google Analytics tracking ID
define('LIST_MODE', 1);						//0(default, takes up more CPU):  Renders everything from Markdown everytime they are needed.
                                            //1(recommended, takes up more disk storage and PHP's writing permission): Make a HTML cache for every new/updated post when index.php finds one, and then everyone else reads directly from cache.
