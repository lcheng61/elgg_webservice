<?php
/**
 * Elgg Ideas Plugin
 * @package ideas
 */

// Get input
$selected_cat = get_input('cat', 'all');
$selected_type = get_input('type', 'all');

if ($selected_cat != 'all') {
	$namevalue_cat = array('name' => 'ideascategory', 'value' => $selected_cat);
}

if ($selected_type != 'all') {
	$namevalue_type = array('name' => 'ideas_type', 'value' => $selected_type);
}

elgg_set_context('ideas');
elgg_pop_breadcrumb();
elgg_register_title_button();

$tabs = elgg_view('ideas/menu', array('type' => $selected_type));

//set ideas title
$title = elgg_echo('ideas:category:title', array(elgg_echo("ideas:category:{$selected_cat}")));

$options = array(
	'types' => 'object',
	'subtypes' => 'ideas',
	'limit' => 5,
	'full_view' => false,
	'pagination' => true,
	'view_type_toggle' => false,
	'item_class' => 'ideas-item-list',
);

// Get a list of ideas posts in a specific category
if (!empty($selected_cat)) {
	elgg_push_breadcrumb(elgg_echo('ideas:title'), "ideas/category/all");
	elgg_push_breadcrumb(elgg_echo("ideas:category:{$selected_cat}"), "ideas/category/{$selected_cat}");
	elgg_push_breadcrumb(elgg_echo("ideas:type:{$selected_type}"));
	$options['metadata_name_value_pairs'] = array($namevalue_cat, $namevalue_type);
	$content = elgg_list_entities_from_metadata($options);
} else {
	elgg_push_breadcrumb(elgg_echo('ideas:title'));
	$content = elgg_list_entities($options);
}

if (!$content) {
	$content = elgg_echo('ideas:none:found');
}

// Show ideas sidebar
$sidebar = elgg_view("ideas/sidebar");

$params = array(
		'filter' => $tabs,
		'content' => $content,
		'title' => $title,
		'sidebar' => $sidebar,
		);

$body = elgg_view_layout('content', $params);

echo elgg_view_page($title, $body);


