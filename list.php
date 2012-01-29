<!doctype html> 
<html lang="en"> 
<head>
        <title>
            <?php 
				require("stuff/config.php");
				echo constant('SITE_NAME');
				?>
        </title>
  <style>
    /* RESET */
    html, body, div, span, h1, h2, h3, h4, h5, h6, p, blockquote, a,
    font, img, dl, dt, dd, ol, ul, li, legend, table, tbody, tr, th, td 
    {margin:0px;padding:0px;border:0;outline:0;font-weight:inherit;font-style:inherit;font-size:100%;font-family:inherit;list-style:none;}
    a img {border: none;}
    ol li {list-style: decimal outside;}
    fieldset {border:0;padding:0;}
    
    body { font-family: sans-serif; font-size: 1em; }
    
    div#container { width: 780px; margin: 0 auto; padding: 1em 0;  }
    p { margin: 1em 0; max-width: 700px; }
    h1 + p { margin-top: 0; }
    
    h1, h2 { font-family: Georgia, Times, serif; }
    h1 { font-size: 2em; margin-bottom: .75em; }
    h2 { font-size: 1.5em; margin: 2.5em 0 .5em; border-bottom: 1px solid #999; padding-bottom: 5px; }
    h3 { font-weight: bold; }
    
    ul li { list-style: disc; margin-left: 1em; }
    ol li { margin-left: 1.25em; }
    
    div.side-by-side { width: 100%; margin-bottom: 1em; }
    div.side-by-side > div { float: left; width: 50%; }
    div.side-by-side > div > em { margin-bottom: 10px; display: block; }
    
    a { color: orange; text-decoration: underline; }
    
    .faqs em { display: block; }
    
    .clearfix:after {
      content: "\0020";
      display: block;
      height: 0;
      clear: both;
      overflow: hidden;
      visibility: hidden;
    }
    
    footer {
      margin-top: 2em;
      border-top: 1px solid #666;
      padding-top: 5px;
    }
#a {
	position:absolute;
	display:table;

}
#b {
	display:table-cell;
	vertical-align:middle;
	text-align:center;

}
.full_fill {
	height:100%;
	width:100%;
}
#dark {
	position:fixed;
	top:0px;
	left:0px;
	background:-webkit-gradient(radial,50% 10%,70,50% 50%,1000,color-stop(0.3,rgba(220,220,220,0.2)),from(rgba(255,255,255,0)),to(rgba(0,0,0,0.65)));
	z-index:2;
	pointer-events:none;
}

  </style>
  <link rel="stylesheet" href="stuff/chosen/chosen.css" />
</head>
<body>
	<div id="dark" class="full_fill"></div>
    <div id="a" class="full_fill">
       <div id="b">
        <select data-placeholder="the.tslmy.tk" class="chzn-select" style="width:400px;" tabindex="2">
          <option value=""></option> 
          			<?php
			if ($handler = opendir("content/")){  //try to open the directory.
				while (false !== ($filename = readdir($handler))) {//for each file in this directory
					$len=strlen($filename);//get the length of the file name for the next step
					if (substr($filename,0,1)!="_" && strtolower(substr($filename,$len-4,$len))==".txt") { //if this file is not intended to be omitted and it's a .txt file 
						$files[filemtime("content/".$filename)] = substr($filename,0,$len-4); //then put it into the file array with its Last Modified Time as its number
					}
				}
				krsort($files,SORT_NUMERIC);//sort the array out

				foreach ($files as $each_one){
					echo "<option value='".$each_one."'>".$each_one."</option>";
				}
			}else { //if failed to load the directory.
				echo "Error occured. Contact tslmy!";
			}
			?>
        </select>
       </div>
    </div>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js" type="text/javascript"></script>
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
