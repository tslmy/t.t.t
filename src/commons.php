<?php
define('SITE_NAME', "tslmy's blog");				//This defines the site's name.
define('SITE_DESC', "A dummy blog for demonstrating the database-free blog engine, t.t.t.");				//This defines the site's description.
define('ITEMS_DISPLAYED_PER_PAGE', 2);		//How many posts are to be shown on each page of the index.
define('PREVIEW_SIZE_IN_KB', 1000);         //How much post previews to provide.
define('CONTENT_DIR', 'content');			//Where the text files should be stored.
define('DISQUS_SHORTNAME', 'tslmy');			//disqus_shortname
define('GA_ID', 'UA-21290300-1');           //Google Analytics tracking ID
define('EXT_ALLOWED', ['txt', 'md']);

$content_dir = trim(constant('CONTENT_DIR'), '/');
$content_abs_dir = getcwd().'/'.$content_dir;

use League\CommonMark\GithubFlavoredMarkdownConverter;
use Michelf\SmartyPants;

function get_content($file_name, $max_size)
{
    $converter = new GithubFlavoredMarkdownConverter([
        'html_input' => 'strip',
        'allow_unsafe_links' => false,
    ]);
    // Create the HTML to cache
    $content = file_get_contents($file_name);
    if ($max_size>0) {
        $content = substr($content, 0, $max_size);
    }
    $content = $converter->convertToHtml(trim($content));
    $content = SmartyPants::defaultTransform($content);
    // Now add the internal links (replace ~shortname~ with the link)
    $content = preg_replace('/~([^<:>~]+?):([^<>~]+?)~/', '<a href="view.php?name=$1">$2</a>', $content);
    $content = preg_replace('/~([^<:>~]+?)~/', '<a href="view.php?name=$1">$1</a>', $content);
    return $content;
}

function print_breadcrumb($crumbs, $cur_path)
{
    $output = "<a href=\"index.php\">Home</a>";
    $absolute_path='';
    foreach ($crumbs as $i => $folder_name) {
        if ($folder_name=='') {
            continue;
        }
        $absolute_path = $absolute_path.'/'.$folder_name;
        if ($absolute_path==$cur_path) {
            $output .= ' &gt; '.$folder_name.'';
        } else {
            $output .= ' &gt; <a href="index.php?folder='.urlencode($absolute_path).'">'.$folder_name.'</a>';
        }
    }
    return $output;
}
