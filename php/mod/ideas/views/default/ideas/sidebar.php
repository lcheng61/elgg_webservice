<?php
/**
 * Elgg Ideas Plugin
 * @package ideas
 */

echo elgg_view_module('aside', elgg_echo('ideas:categories'), elgg_view('ideas/categories'));

echo elgg_view('page/elements/comments_block', array(
	'subtypes' => 'ideas',
	'owner_guid' => elgg_get_page_owner_guid(),
));

echo elgg_view('page/elements/tagcloud_block', array(
	'subtypes' => 'ideas',
	'owner_guid' => elgg_get_page_owner_guid(),
));

