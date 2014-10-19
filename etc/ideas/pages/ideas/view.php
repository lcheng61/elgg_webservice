<?php
/**
 * Elgg Ideas Plugin
 * @package ideas
 */


// Get the specified ideas post
$post = (int) get_input('ideaspost');

// If we can get out the ideas post ...
if ($ideaspost = get_entity($post)) {
			
	// Load fancybox
	elgg_load_js('lightbox');
	elgg_load_css('lightbox');

	$category = $ideaspost->ideascategory;

	elgg_push_breadcrumb(elgg_echo('ideas:title'), "ideas/category");
	elgg_push_breadcrumb(elgg_echo("ideas:category:{$category}"), "ideas/category/{$category}");
	elgg_push_breadcrumb($ideaspost->title);

	// Display it
	$content = elgg_view_entity($ideaspost, array('full_view' => true));
	if (elgg_get_plugin_setting('ideas_comments', 'ideas') == 'yes') {
		$content .= elgg_view_comments($ideaspost);
	}

	// Set the title appropriately
	$title = elgg_echo("ideas:category") . ": " . elgg_echo("ideas:category:{$category}");

} else {
			
	// Display the 'post not found' page instead
	$content = elgg_view_title(elgg_echo("ideas:notfound"));
	$title = elgg_echo("ideas:notfound");
			
}
	
// Show ideas sidebar
$sidebar = elgg_view("ideas/sidebar");

$params = array(
		'content' => $content,
		'title' => $title,
		'sidebar' => $sidebar,
		'filter' => '',
		'header' => '',
		);

$body = elgg_view_layout('content', $params);

echo elgg_view_page($title, $body);

