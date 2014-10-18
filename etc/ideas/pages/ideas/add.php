<?php
/**
 * Elgg Ideas Plugin
 * @package ideas
 */

if (elgg_get_plugin_setting('ideas_adminonly', 'ideas') == 'yes') {
	admin_gatekeeper();
}
		
// How many classifieds can a user have
$ideasmax = elgg_get_plugin_setting('ideas_max', 'ideas');
if(!$ideasmax) {
	$ideasmax = 0;
}

// How many classifieds can a freeloader have
if(elgg_is_active_plugin('vipmember')) {
	$user = elgg_get_logged_in_user_entity();
	if (!vipmember_isPayingMember($user)) {
		$ideasmax = 1;
	}
}


// How many classifieds can a commercial user have
if (elgg_is_active_plugin('adserve')) {
	$user = elgg_get_logged_in_user_entity();
	if ($user->adserve_type == 'com') {
		$ideasmax = elgg_get_plugin_setting('ideas_max', 'adserve');
		if(!$ideasmax) {
			$ideasmax = 0;
		}
	}
}

$ideasactive = elgg_get_entities(array(
			'type' => 'object',
			'subtype' => 'ideas',
			'owner_guid' => elgg_get_logged_in_user_guid(),
			'count' => true
			));

$title = elgg_echo('ideas:add:title');

// Show form, or error if users has used his quota
if ($ideasmax == 0 || elgg_is_admin_logged_in()) { 
	$form_vars = array(
			'name' => 'ideasForm',
			'onsubmit' => "acceptTerms();return false;",
			'enctype' => 'multipart/form-data'
			);
	$body_vars = ideas_prepare_form_vars(NULL);
	$content = elgg_view_form("ideas/save", $form_vars, $body_vars);
} elseif ($ideasmax > $ideasactive) { 
	$form_vars = array(
			'name' => 'ideasForm',
			'onsubmit' => "acceptTerms();return false;",
			'enctype' => 'multipart/form-data'
			);
	$body_vars = ideas_prepare_form_vars(NULL);
	$content = elgg_view_form("ideas/save", $form_vars, $body_vars);
} else {
	$content = elgg_view("ideas/error");
}

elgg_push_breadcrumb(elgg_echo('ideas:title'), "ideas/category");
elgg_push_breadcrumb(elgg_echo('ideas:add'));

// Show ideas sidebar
$sidebar = elgg_view("ideas/sidebar");

$params = array(
		'content' => $content,
		'title' => $title . " " . $ideasmax,
		'sidebar' => $sidebar,
		);

$body = elgg_view_layout('one_sidebar', $params);

echo elgg_view_page($title, $body);
