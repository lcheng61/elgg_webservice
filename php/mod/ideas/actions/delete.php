<?php
/**
 * Elgg Ideas Plugin
 * @package ideas
 */

// Make sure we're logged in (send us to the front page if not)
if (!elgg_is_logged_in()) forward();

// Get input data
$guid = (int) get_input('guid');
		
// Make sure we actually have permission to edit
$post = get_entity($guid);
if ($post->getSubtype() == "ideas" && $post->canEdit()) {
	
	elgg_load_library('ideas');

	// Delete the ideas post
	$return = ideas_delete_post($post);
	if ($return) {
		// Success message
		system_message(elgg_echo("ideas:deleted"));
	} else {
		// Error message
		register_error(elgg_echo("ideas:notdeleted"));
	}
				
	// Forward to the main ideas page
	forward(elgg_get_site_url() . "ideas");
}
		
