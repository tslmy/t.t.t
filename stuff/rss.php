
		
         <?php
			if ($handler = opendir("../content/")){  //try to open the directory.
				echo "
<?xml version='1.0' encoding='utf-8'?>
<feed xmlns='http://www.w3.org/2005/Atom'>
 
        <title>Lilo^log R.S.S.</title>
        <subtitle>The official (and example) RSS feed for http://the.tslmy.tk (and t.t.t blog engine).</subtitle>
        <link href='http://the.tslmy.tk/stuff/rss.php' rel='self' />
        <link href='http://the.tslmy.tk/' />
        <author>
                <name>tslmy</name>
                <email>tslimingyang@126.com</email>
        </author>
";
				while (false !== ($filename = readdir($handler))) {//for each file in this directory
					$len=strlen($filename);//get the length of the file name for the next step
					if (substr($filename,0,1)!="_" && strtolower(substr($filename,$len-4,$len))==".txt") { //if this file is not intended to be omitted and it's a .txt file 
						$files[filemtime("../content/".$filename)] = substr($filename,0,$len-4); //then put it into the file array with its Last Modified Time as its number
					}
				}
				krsort($files,SORT_NUMERIC);//sort the array out

				foreach ($files as $each_one){
					echo "
						<entry>
							<title>".$each_one."</title>
							<link href='http://the.tslmy.tk/view.php?name=".$each_one."' />
							<updated>".date(DATE_ISO8601,filemtime("../content/".$each_one.".txt"))."</updated>
						</entry>
					";
				};
			}else { //if failed to load the directory.
				echo "Error occured. Contact tslmy!";
			};
		?>

</feed>