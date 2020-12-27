<?php
use League\CommonMark\GithubFlavoredMarkdownConverter;
use Michelf\SmartyPants;

function get_content($file_name){
	$converter = new GithubFlavoredMarkdownConverter([
	    'html_input' => 'strip',
	    'allow_unsafe_links' => false,
	]);
	// Create the HTML to cache
	$file_content = file_get_contents($file_name);
	$content = $converter->convertToHtml(trim($file_content));
	$content = SmartyPants::defaultTransform($content);
	// Now add the internal links (replace ~shortname~ with the link)
	$content = preg_replace('/~([^<:>~]+?):([^<>~]+?)~/', '<a href="view.php?name=$1">$2</a>', $content);
	$content = preg_replace('/~([^<:>~]+?)~/', '<a href="view.php?name=$1">$1</a>', $content);
	return $content;
}
?>