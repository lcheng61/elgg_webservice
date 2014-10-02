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
 * @param string $category(optional) eg. fashion, gadget, etc
 * @param string $username (optional) the username of the user default loggedin user
 *
 * @return array $file Array of files uploaded
 */

function market_get_posts($context,  $limit = 10, $offset = 0, $group_guid, $category, $username) {

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
                        'offset' => $offset,
		);
		}
		if($context == "mine" || $context ==  "user"){
		$params = array(
			'types' => 'object',
			'subtypes' => 'market',
			'owner_guid' => $user->guid,
			'limit' => $limit,
			'full_view' => FALSE,
                        'offset' => $offset,
		);
		}
		if($context == "group"){
		$params = array(
			'types' => 'object',
			'subtypes' => 'market',
			'container_guid'=> $group_guid,
			'limit' => $limit,
			'full_view' => FALSE,
                        'offset' => $offset,
		);
		}
//		$latest_blogs = elgg_list_entities_from_metadata($params);
		$latest_blogs = elgg_get_entities($params);
		
		if($context == "friends"){
		$latest_blogs = get_user_friends_objects($user->guid, 'market', $limit, $offset);
		}
	
	if($latest_blogs) {
		foreach($latest_blogs as $single ) {
                   if (($single->marketcategory == $category) || 
                           ($category == "all")) {
			$blog['product_id'] = $single->guid;

                        $options = array(
	                    'annotations_name' => 'generic_comment',
	                    'guid' => $single->$guid,
	                    'limit' => $limit,
	                    'pagination' => false,
	                    'reverse_order_by' => true,
                        );

       	                $comments = elgg_get_annotations($options);
                        $num_comments = count($comments);

			$blog['product_name'] = $single->title;
//			$blog['product_description'] = $single->description;
			$blog['product_price'] = $single->price;
			$blog['tips_number'] = $single->tips_number;
			$blog['sold_number'] = $single->sold_count;
			$blog['product_category'] = $single->marketcategory;
//			$blog['market_type'] = $single->market_type;
//			$blog['custom'] = $single->custom;
//			$blog['product_tags'] = $single->tags;
			$blog['product_image'] = elgg_normalize_url("market/image/".$single->guid."/1/"."large/");
                        $blog['likes_number'] = likes_count(get_entity($single->guid));
                        $blog['reviews_number'] = $num_comments;

			$owner = get_entity($single->owner_guid);
			$blog['product_seller']['user_id'] = $owner->guid;
//			$blog['product_seller']['name'] = $owner->name;
			$blog['product_seller']['user_name'] = $owner->username;
			$blog['product_seller']['user_avatar_url'] = get_entity_icon_url($owner,'small');
			$blog['product_seller']['is_seller'] = $owner->is_seller;
			
//			$blog['container_guid'] = $single->container_guid;
//			$blog['access_id'] = $single->access_id;
//			$blog['time_created'] = (int)$single->time_created;
//			$blog['time_updated'] = (int)$single->time_updated;
//			$blog['last_action'] = (int)$single->last_action;
			$return[] = $blog;
                    }
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
					  'category' => array ('type' => 'string', 'required' => false, 'default' => 'all'),
					  'username' => array ('type' => 'string', 'required' => false),
					),
				"Get list of market posts",
				'GET',
				false,
				false);

 /**
 * Web service to get market product detail list by all users
 *
 * @param string $context eg. all, friends, mine, groups
 * @param int $limit  (optional) default 10
 * @param int $offset (optional) default 0
 *
 * @return array $file Array of files uploaded
 */
/**
 * Web service for read a blog post
 *
 * @param string $guid     GUID of a blog entity
 * @param string $username Username of reader (Send NULL if no user logged in)
 * @param string $password Password for authentication of username (Send NULL if no user logged in)
 *
 * @return string $title       Title of blog post
 * @return string $content     Text of blog post
 * @return string $excerpt     Excerpt
 * @return string $tags        Tags of blog post
 * @return string $owner_guid  GUID of owner
 * @return string $access_id   Access level of blog post (0,-2,1,2)
 * @return string $status      (Published/Draft)
 * @return string $comments_on On/Off
 */

function get_product_detail($guid, $username) {
	$return = array();
	$blog = get_entity($guid);

	if (!elgg_instanceof($blog, 'object', 'market')) {
		$return['content'] = elgg_echo('blog:error:post_not_found');
		return $return;
	}

	$return['title'] = htmlspecialchars($blog->title);
	$return['product_image'] = elgg_normalize_url("market/image/".$guid."/1/"."large/");
	$return['content'] = strip_tags($blog->description);
	$return['product_price'] = $blog->price;
	$return['excerpt'] = $blog->excerpt;
	$return['product_description'] = $blog->description;

	$owner = get_entity($blog->owner_guid);
// XXX: if owner is null, then handle it. unlikely.
	$return['product_seller']['user_id'] = $owner->guid;
	$return['product_seller']['user_name'] = $owner->username;
	$return['product_seller']['user_avatar_url'] = get_entity_icon_url($owner,'small');
	$return['product_seller']['is_seller'] = $owner->is_seller;
        $return['likes_number'] = likes_count(get_entity($guid));
        $return['tips_number'] = $blog->tips_number;

	return $return;
}
	
expose_function('market.get_product_detail',
		"get_product_detail",
		array('guid' => array ('type' => 'string'),
	              'username' => array ('type' => 'string', 'required' => false),
		     ),
		"Read a product post",
		'GET',
		false,
		false);



/*



function market_get_product_detail($product_id) {
		$params = array(
			'types' => 'object',
			'subtypes' => 'market',
//			'limit' => $limit,
			'full_view' => FALSE,
//                        'offset' => $offset,
		);
		$latest_blogs = elgg_get_entities($params);
		
		if($context == "friends"){
		$latest_blogs = get_user_friends_objects($user->guid, 'market', $limit, $offset);
		}
	
	if($latest_blogs) {
		foreach($latest_blogs as $single ) {
                   if (($single->marketcategory == $category) || 
                           ($category == "all")) {
			$blog['product_id'] = $product_id;

                        $options = array(
	                    'annotations_name' => 'generic_comment',
	                    'guid' => $product_id,
	                    'limit' => $limit,
	                    'pagination' => false,
	                    'reverse_order_by' => true,
                        );

       	                $comments = elgg_get_annotations($options);
//                        $num_comments = count($comments);

			$blog['product_name'] = $single->title;
//			$blog['product_description'] = $single->description;
			$blog['product_price'] = $single->price;

			$blog['tips_number'] = $single->tips_number;
			$blog['sold_number'] = $single->sold_count;
			$blog['product_category'] = $single->marketcategory;
//			$blog['market_type'] = $single->market_type;
//			$blog['custom'] = $single->custom;
//			$blog['product_tags'] = $single->tags;
			$blog['product_image'] = elgg_normalize_url("market/image/".$single->guid."/1/"."large/");
                        $blog['likes_number'] = likes_count(get_entity($single->guid));
                        $blog['reviews_number'] = $num_comments;

			$owner = get_entity($single->owner_guid);
			$blog['product_seller']['user_id'] = $owner->guid;
//			$blog['product_seller']['name'] = $owner->name;
			$blog['product_seller']['user_name'] = $owner->username;
			$blog['product_seller']['user_avatar_url'] = get_entity_icon_url($owner,'small');
			$blog['product_seller']['is_seller'] = $owner->is_seller;
			
//			$blog['container_guid'] = $single->container_guid;
//			$blog['access_id'] = $single->access_id;
//			$blog['time_created'] = (int)$single->time_created;
//			$blog['time_updated'] = (int)$single->time_updated;
//			$blog['last_action'] = (int)$single->last_action;
			$return[] = $blog;
                    }
		}
	}
	else {
		$msg = elgg_echo('market_post:none');
		throw new InvalidParameterException($msg);
	}

	return $return;
}

expose_function('market.get_product_detail',
				"market_get_posts",
				array(
					  'context' => array ('type' => 'string', 'required' => false, 'default' => 'all'),
					  'limit' => array ('type' => 'int', 'required' => false, 'default' => 10),
					  'offset' => array ('type' => 'int', 'required' => false, 'default' => 0),
					  'group_guid' => array ('type'=> 'int', 'required'=>false, 'default' =>0),
					  'category' => array ('type' => 'string', 'required' => false, 'default' => 'all'),
					  'username' => array ('type' => 'string', 'required' => false),
					),
				"Get list of market posts",
				'GET',
				false,
				false);
*/

/**
 * Web service to retrieve comments on a product post
 *
 * @param string $guid market product guid
 * @param string $limit    Number of users to return
 * @param string $offset   Indexing offset, if any
 *
 * @return array
 */    				
function market_get_comments($guid, $limit = 10, $offset = 0){
	$market = get_entity($guid);
	
$options = array(
	'annotations_name' => 'generic_comment',
	'guid' => $guid,
	'limit' => $limit,
	'pagination' => false,
	'reverse_order_by' => true,
);

	$comments = elgg_get_annotations($options);

	if($comments){
	foreach($comments as $single){
		$comment['guid'] = $single->id;
		$comment['description'] = strip_tags($single->value);
		
		$owner = get_entity($single->owner_guid);
		$comment['owner']['guid'] = $owner->guid;
		$comment['owner']['name'] = $owner->name;
		$comment['owner']['username'] = $owner->username;
		$comment['owner']['avatar_url'] = get_entity_icon_url($owner,'small');
		
		$comment['time_created'] = (int)$single->time_created;
		$return[] = $comment;
	}
} else {
		$msg = elgg_echo('generic_comment:none');
		throw new InvalidParameterException($msg);
	}
	return $return;
}
expose_function('market.get_comments',
				"market_get_comments",
				array(	'guid' => array ('type' => 'string'),
						'limit' => array ('type' => 'int', 'required' => false, 'default' => 10),
						'offset' => array ('type' => 'int', 'required' => false, 'default' => 0),
						
					),
				"Get comments for a market post",
				'GET',
				false,
				false);	
