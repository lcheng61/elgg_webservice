<?php
/**
 * Elgg Ideas Plugin
 * @package ideas
 */

// Get the post, if it exists
$guid = (int) get_input('guid');
$post = get_entity($guid);
		
if ($post && $post->canEdit()) {
	$title = elgg_echo('ideas:edit');
	$form_vars = array(
			'name' => 'ideasForm',
			'onsubmit' => "acceptTerms();return false;",
			'enctype' => 'multipart/form-data'
			);
	$body_vars = ideas_prepare_form_vars($post);
	$content = elgg_view_form("ideas/save", $form_vars, $body_vars);
} else {
	$title = elgg_echo('ideas:none:found');
	$content = elgg_view("ideas/error");
}

elgg_push_breadcrumb(elgg_echo('ideas:title'), "ideas/category");
elgg_push_breadcrumb($post->title, $post->getURL());
elgg_push_breadcrumb(elgg_echo('ideas:edit'));

// Show ideas sidebar
$sidebar = elgg_view("ideas/sidebar");
		
$params = array(
		'content' => $content,
		'title' => $title,
		'sidebar' => $sidebar,
		);

$body = elgg_view_layout('one_sidebar', $params);

echo elgg_view_page($title, $body);

