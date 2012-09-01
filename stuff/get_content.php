<?php
function get_content($file_name){
	// Create the HTML to cache
	$r = SmartyPants(Markdown(trim(file_get_contents( $file_name ))));
	// Now add the internal links (replace ~shortname~ with the link)
	$r = preg_replace('/~([^<:>~]+?):([^<>~]+?)~/', '<a href="view.php?name=$1">$2</a>', $r);
	$r = preg_replace('/~([^<:>~]+?)~/', '<a href="view.php?name=$1">$1</a>', $r);
	return $r;
}
?>