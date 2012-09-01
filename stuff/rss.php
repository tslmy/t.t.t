<feed xmlns='http://www.w3.org/2005/Atom'>
	<title><?php 
		require("config.php");
		echo constant('SITE_NAME');
	?></title>
	<subtitle>The official (and example) RSS feed for http://the.tslmy.tk (and t.t.t blog engine).</subtitle>
	<link href="http://tslmy.tk/myblog/stuff/rss.php" rel="self" />
	<link href="http://the.tslmy.tk/" />
	<id>http://the.tslmy.tk/</id>
	<author>
	    <name>tslmy</name>
	    <email>tslimingyang@126.com</email>
	</author>
	<?php
		if ($handler = opendir('../content/')){  //try to open the directory.
			while (false !== ($filename = readdir($handler))) {//for each file in this directory
				$len=strlen($filename);//get the length of the file name for the next step
				if (substr($filename,0,1)!="_" && strtolower(substr($filename,$len-4,$len))==".txt") { //if this file is not intended to be omitted and it's a .txt file 
					$files[filemtime("../content/".$filename)] = substr($filename,0,$len-4); //then put it into the file array with its Last Modified Time as its number
				}
			}
			krsort($files,SORT_NUMERIC);//sort the array out
			$a=array_keys($files);//no big use...
			echo '<updated>'.date('c',$a[0]).'</updated>';
			foreach ($files as $each_one){
				$this_date=date('c',filemtime("../content/".$each_one.".txt"));
				echo "
					<entry>
						<title>".$each_one."</title>
						<link href='http://tslmy.tk/myblog/view.php?name=".urlencode($each_one)."' />
						<id>ttt-".strtolower($this_date)."</id>
						<updated>".$this_date."</updated>
						<summary> Article \"".$each_one."\" from ".$_SERVER["HTTP_HOST"].".</summary>
					</entry>
				";
			};
		}else { //if failed to load the directory.
			echo "Error occured. Contact tslmy!";
		};
	?>
</feed>