<?php
/**
 * Elgg Market Plugin
 * @package market
 */

// Get input
$selected_cat = get_input('cat', 'all');
$selected_type = get_input('type', 'all');

if ($selected_cat != 'all') {
	$namevalue_cat = array('name' => 'marketcategory', 'value' => $selected_cat);
}

if ($selected_type != 'all') {
	$namevalue_type = array('name' => 'market_type', 'value' => $selected_type);
}

elgg_set_context('market');
elgg_pop_breadcrumb();
elgg_register_title_button();

$tabs = elgg_view('market/menu', array('type' => $selected_type));

//set market title
$title = elgg_echo('market:category:title', array(elgg_echo("market:category:{$selected_cat}")));

$options = array(
	'types' => 'object',
	'subtypes' => 'market',
	'limit' => 5,
	'full_view' => false,
	'pagination' => true,
	'view_type_toggle' => false,
	'item_class' => 'market-item-list',
);

// Get a list of market posts in a specific category
if (!empty($selected_cat)) {
	elgg_push_breadcrumb(elgg_echo('market:title'), "market/category/all");
	elgg_push_breadcrumb(elgg_echo("market:category:{$selected_cat}"), "market/category/{$selected_cat}");
	elgg_push_breadcrumb(elgg_echo("market:type:{$selected_type}"));
	$options['metadata_name_value_pairs'] = array($namevalue_cat, $namevalue_type);
	$content = elgg_list_entities_from_metadata($options);
} else {
	elgg_push_breadcrumb(elgg_echo('market:title'));
	$content = elgg_list_entities($options);
}

if (!$content) {
	$content = elgg_echo('market:none:found');
}

// Show market sidebar
$sidebar = elgg_view("market/sidebar");

$params = array(
		'filter' => $tabs,
		'content' => $content,
		'title' => $title,
		'sidebar' => $sidebar,
		);

$body = elgg_view_layout('content', $params);

echo elgg_view_page($title, $body);


