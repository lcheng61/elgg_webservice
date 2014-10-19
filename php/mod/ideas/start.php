<?php
/**
 * Elgg Ideas Plugin
 * @package ideas
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author slyhne
 * @copyright slyhne 2010-2014
 * @link http://tiger-inc.eu
 * @version 1.8
 */

elgg_register_event_handler('init','system','ideas_init');

function ideas_init() {

	elgg_register_library('ideas', elgg_get_plugins_path() . 'ideas/lib/ideas.php');

	// Add a site navigation item
	$item = new ElggMenuItem('ideas', elgg_echo('ideas:title'), 'ideas/category');
	elgg_register_menu_item('site', $item);

	// Extend owner block menu
	elgg_register_plugin_hook_handler('register', 'menu:owner_block', 'ideas_owner_block_menu');
	elgg_register_plugin_hook_handler('register', 'menu:page', 'ideas_page_menu');

	// Extend system CSS with our own styles
	elgg_extend_view('css/elgg','ideas/css');
	elgg_extend_view('css/admin','ideas/admincss');

	// Add a new widget
	elgg_register_widget_type(
			'ideas',
			elgg_echo('ideas:widget'),
			elgg_echo('ideas:widget:description')
			);

	// Register a page handler, so we can have nice URLs
	elgg_register_page_handler('ideas','ideas_page_handler');

	// Initialize a pagesetup for menus
	elgg_register_event_handler('pagesetup','system','ideas_pagesetup');

	// Override the default url to view a ideas post
	elgg_register_entity_url_handler('object', 'ideas', 'ideas_url_handler');

	// Register entity type
	elgg_register_entity_type('object', 'ideas');

	// Register granular notification for this type
	register_notification_object('object', 'ideas', elgg_echo('ideas:new:post'));

	// Listen to notification events and supply a more useful message
	elgg_register_plugin_hook_handler('notify:entity:message', 'object', 'ideas_notify_message');

	// Setup cron job to delete old ideas posts
	elgg_register_plugin_hook_handler('cron', 'daily', 'ideas_expire_cron_hook');

	// Register actions
	$action_url = elgg_get_plugins_path() . "ideas/actions/";
	elgg_register_action("ideas/save", "{$action_url}save.php");
	elgg_register_action("ideas/delete", "{$action_url}delete.php");
	elgg_register_action("ideas/delete_img", "{$action_url}delete_img.php");

}

// ideas page handler; allows the use of fancy URLs
function ideas_page_handler($page) {

	$pages = dirname(__FILE__) . '/pages/ideas';

	if (!isset($page[1])) {
		$page[1] = 'all';
	}
	if (!isset($page[2])) {
		$page[2] = 'all';
	}

	$page_type = $page[0];
	switch ($page_type) {
		case 'owned':
			set_input('username', $page[1]);
			include "$pages/owned.php";
			break;
		/*
		case 'friends':
			set_input('username' , $page[1]);
			include "$pages/friends.php";
			break;
		*/
		case 'view':
			set_input('ideaspost', $page[1]);
			include "$pages/view.php";
			break;
		case 'image':
			set_input('guid', $page[1]);
			set_input('imagenum', $page[2]);
			set_input('size', $page[3]);
			set_input('tu', $page[4]);
			include "$pages/image.php";
			break;
		case 'imagepopup':
			set_input('guid', $page[1]);
			set_input('imagenum', $page[2]);
			include "$pages/imagepopup.php";
			break;
		case 'add':
			elgg_load_library('ideas');
			include "$pages/add.php";
			break;
		case 'edit':
			elgg_load_library('ideas');
			set_input('guid', $page[1]);
			include "$pages/edit.php";
			break;
		case 'category':
			set_input('cat', $page[1]);
			set_input('type', $page[2]);
			include "$pages/category.php";
			break;
		case 'terms':
			include "$pages/terms.php";
			break;
		default:
			set_input('cat', $page[1]);
			set_input('type', $page[2]);
			include "$pages/category.php";
			break;
	}
	return true;
}

// Populates the ->getURL() method for ideas objects
function ideas_url_handler($entity) {

	if (!$entity->getOwnerEntity()) {
		// default to a standard view if no owner.
		return FALSE;
	}

	$friendly_title = elgg_get_friendly_title($entity->title);

	return "ideas/view/{$entity->guid}/{$friendly_title}";

}

// Add to the user block menu
function ideas_owner_block_menu($hook, $type, $return, $params) {

	if (elgg_instanceof($params['entity'], 'user')) {
		$url = "ideas/owned/{$params['entity']->username}";
		$item = new ElggMenuItem('ideas', elgg_echo('ideas'), $url);
		$return[] = $item;
	}

	return $return;

}
/**
 * Add a page menu menu.
 *
 * @param string $hook
 * @param string $type
 * @param array  $return
 * @param array  $params
 */
function ideas_page_menu($hook, $type, $return, $params) {
	if (elgg_is_logged_in()) {
		// only show buttons in ideas pages
		if (elgg_in_context('ideas')) {
			$user = elgg_get_logged_in_user_entity();
			$page_owner = elgg_get_page_owner_entity();
			if (!$page_owner) {
				$page_owner = elgg_get_logged_in_user_entity();
			}
			
			if ($page_owner != $user) {
				$usertitle = elgg_echo('ideas:user', array($page_owner->name));
				$return[] = new ElggMenuItem('ideas_owner', $usertitle, 'ideas/owned/' . $page_owner->username);
				//$friendstitle = elgg_echo('ideas:user:friends', array($page_owner->name));
				//$return[] = new ElggMenuItem('2userfriends', $friendstitle, 'ideas/friends/' . $page_owner->username);
			} else {
				$return[] = new ElggMenuItem('ideas_owner', elgg_echo('ideas:mine'), 'ideas/owned/' . $user->username);
			}
		}
	}

	return $return;
}

// Cron function to delete old ideas posts
function ideas_expire_cron_hook($hook, $entity_type, $returnvalue, $params) {

	elgg_load_library('ideas');

	$ideas_ttl = elgg_get_plugin_setting('ideas_expire','ideas');
	if ($ideas_ttl == 0) {
		return true;
	}
	$time_limit = strtotime("-$ideas_ttl months");

	$ret = elgg_set_ignore_access(TRUE);
	
	$entities = elgg_get_entities(array(
					'type' => 'object',
					'subtype' => 'ideas',
					'created_time_upper' => $time_limit,
					));

	foreach ($entities as $entity) {
		$date = date('j/n-Y', $entity->time_created);
		$title = $entity->title;
		$owner = $entity->getOwnerEntity();
		notify_user($owner->guid,
				elgg_get_site_entity()->guid,
				elgg_echo('ideas:expire:subject'),
				elgg_echo('ideas:expire:body', array($owner->name, $title, $date, $ideas_ttl)),
				NULL,
				'site');
		// Delete ideas post incl. pictures
		ideas_delete_post($entity);
	}
	
	$ret = elgg_set_ignore_access(FALSE);
	
}

/**
 * Returns the body of a notification message
 *
 * @param string $hook
 * @param string $entity_type
 * @param string $returnvalue
 * @param array  $params
 */
function ideas_notify_message($hook, $entity_type, $returnvalue, $params) {
	$entity = $params['entity'];
	$to_entity = $params['to_entity'];
	$method = $params['method'];
	if (($entity instanceof ElggEntity) && ($entity->getSubtype() == 'ideas')) {
		$descr = elgg_get_excerpt($entity->description);
		$title = $entity->title;
		$owner = $entity->getOwnerEntity();
		$ideas_type = elgg_echo("ideas:type:{$entity->ideas_type}");

		return elgg_echo('ideas:notification', array(
			$owner->name,
			$ideas_type,
			$title,
			$descr,
			$entity->getURL()
		));
	}
	return null;
}
