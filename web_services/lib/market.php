<?php
/**
 * Elgg Webservices plugin 
 * Blogs
 * 
 * @package Webservice
 * @author Liang Cheng
 *
 */
 
 /**
 * Web service to get market posts list by all users
 *
 * @param string $context eg. all, friends, mine, groups
 * @param int $limit  (optional) default 10
 * @param int $offset (optional) default 0
 * @param int $group_guid (optional)  the guid of a group, $context must be set to 'group'
 * @param string $username (optional) the username of the user default loggedin user
 *
 * @return array $file Array of files uploaded
 */

function market_get_posts($context,  $limit = 10, $offset = 0, $group_guid, $username) {	

	if(!$username) {
		$user = get_loggedin_user();
	} else {
		$user = get_user_by_username($username);
		if (!$user) {
			throw new InvalidParameterException('registration:usernamenotvalid');
		}
	}
		
		if($context == "all"){
		$params = array(
			'types' => 'object',
			'subtypes' => 'market',
			'limit' => $limit,
			'full_view' => FALSE,
		);
		}
		if($context == "mine" || $context ==  "user"){
		$params = array(
			'types' => 'object',
			'subtypes' => 'market',
			'owner_guid' => $user->guid,
			'limit' => $limit,
			'full_view' => FALSE,
		);
		}
		if($context == "group"){
		$params = array(
			'types' => 'object',
			'subtypes' => 'market',
			'container_guid'=> $group_guid,
			'limit' => $limit,
			'full_view' => FALSE,
		);
		}
		$latest_blogs = elgg_get_entities($params);
		
		if($context == "friends"){
		$latest_blogs = get_user_friends_objects($user->guid, 'market', $limit, $offset);
		}
	
	
	if($latest_blogs) {
		foreach($latest_blogs as $single ) {
			$blog['guid'] = $single->guid;
			$blog['title'] = $single->title;
			$blog['description'] = $single->description;
			$blog['price'] = $single->price;
			$blog['mareketcategory'] = $single->marketcategory;
			$blog['market_type'] = $single->market_type;
			$blog['custom'] = $single->custom;
			$blog['tags'] = $single->tags;
			$blog['post_image'] = elgg_normalize_url("market/image/".$single->guid."/1/"."large/");
                        $blog['likes'] = likes_count(get_entity($single->guid));
                

			$owner = get_entity($single->owner_guid);
			$blog['owner']['guid'] = $owner->guid;
			$blog['owner']['name'] = $owner->name;
			$blog['owner']['username'] = $owner->username;
			$blog['owner']['avatar_url'] = get_entity_icon_url($owner,'small');
			
			$blog['container_guid'] = $single->container_guid;
			$blog['access_id'] = $single->access_id;
//			$blog['time_created'] = (int)$single->time_created;
//			$blog['time_updated'] = (int)$single->time_updated;
//			$blog['last_action'] = (int)$single->last_action;
			$return[] = $blog;
		}
	}
	else {
		$msg = elgg_echo('market_post:none');
		throw new InvalidParameterException($msg);
	}

	return $return;

}


expose_function('market.get_posts',
				"market_get_posts",
				array(

					  'context' => array ('type' => 'string', 'required' => false, 'default' => 'all'),
					  'limit' => array ('type' => 'int', 'required' => false, 'default' => 10),
					  'offset' => array ('type' => 'int', 'required' => false, 'default' => 0),
					  'group_guid' => array ('type'=> 'int', 'required'=>false, 'default' =>0),
					   'username' => array ('type' => 'string', 'required' => false),
					),
				"Get list of market posts",
				'GET',
				false,
				false);

