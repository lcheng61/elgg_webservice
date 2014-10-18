<?php
/**
 * Elgg Ideas Plugin
 * @package ideas
 */

		
// Get input
$offset = get_input('offset', 0);
$owner_name = get_input('username');
$owner = get_user_by_username($owner_name);

elgg_push_breadcrumb(elgg_echo('ideas:title'), "ideas/category");
elgg_push_breadcrumb($owner->name);

elgg_register_title_button();

//set ideas title
if ($page_owner->guid == elgg_get_logged_in_user_guid()) {
	$title = elgg_echo('ideas:mine:title');
} else {
	$title = sprintf(elgg_echo('ideas:user:title'),$owner->name);
}
		
// Get a list of ideas posts
$content = elgg_list_entities(array(
				'type' => 'object',
				'subtype' => 'ideas',
				'owner_guid' => $owner->guid,
				'limit' => 5,
				'full_view' => false,
				'pagination' => true,
				'view_type_toggle' => FALSE));

if (empty($content)) {
	$content = elgg_echo('ideas:none:found');
}

// Show ideas sidebar
$sidebar = elgg_view("ideas/sidebar");

$params = array(
		'filter' => false,
		'content' => $content,
		'title' => $title,
		'sidebar' => $sidebar,
		);

$body = elgg_view_layout('content', $params);

echo elgg_view_page($title, $body);
