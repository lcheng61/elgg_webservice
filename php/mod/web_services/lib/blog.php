<?php
/**
 * Elgg Webservices plugin 
 * Blogs
 * 
 * @package Webservice
 * @author Mark Harding
 *
 */
 
 /**
 * Web service to get file list by all users
 *
 * @param string $context eg. all, friends, mine, groups
 * @param int $limit  (optional) default 10
 * @param int $offset (optional) default 0
 * @param int $group_guid (optional)  the guid of a group, $context must be set to 'group'
 * @param string $username (optional) the username of the user default loggedin user
 *
 * @return array $file Array of files uploaded
 */
function blog_get_posts($context,  $limit = 10, $offset = 0,$group_guid, $username) {	
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
			'subtypes' => 'blog',
			'limit' => $limit,
			'full_view' => FALSE,
		);
		}
		if($context == "mine" || $context ==  "user"){
		$params = array(
			'types' => 'object',
			'subtypes' => 'blog',
			'owner_guid' => $user->guid,
			'limit' => $limit,
			'full_view' => FALSE,
		);
		}
		if($context == "group"){
		$params = array(
			'types' => 'object',
			'subtypes' => 'blog',
			'container_guid'=> $group_guid,
			'limit' => $limit,
			'full_view' => FALSE,
		);
		}
		$latest_blogs = elgg_get_entities($params);
		
		if($context == "friends"){
		$latest_blogs = get_user_friends_objects($user->guid, 'blog', $limit, $offset);
		}
	
	
	if($latest_blogs) {
		foreach($latest_blogs as $single ) {
			$blog['guid'] = $single->guid;
			$blog['title'] = $single->title;
			$blog['excerpt'] = $single->excerpt;

			$owner = get_entity($single->owner_guid);
			$blog['owner']['guid'] = $owner->guid;
			$blog['owner']['name'] = $owner->name;
			$blog['owner']['username'] = $owner->username;
			$blog['owner']['avatar_url'] = get_entity_icon_url($owner,'small');
			
			$blog['container_guid'] = $single->container_guid;
			$blog['access_id'] = $single->access_id;
			$blog['time_created'] = (int)$single->time_created;
			$blog['time_updated'] = (int)$single->time_updated;
			$blog['last_action'] = (int)$single->last_action;
			$return[] = $blog;
		}
	}
	else {
		$msg = elgg_echo('blog:none');
		throw new InvalidParameterException($msg);
	}
	return $return;
}

expose_function('blog.get_posts',
				"blog_get_posts",
				array(
						'context' => array ('type' => 'string', 'required' => false, 'default' => 'all'),
					  'limit' => array ('type' => 'int', 'required' => false, 'default' => 10),
					  'offset' => array ('type' => 'int', 'required' => false, 'default' => 0),
					  'group_guid' => array ('type'=> 'int', 'required'=>false, 'default' =>0),
					   'username' => array ('type' => 'string', 'required' => false),
					),
				"Get list of blog posts",
				'GET',
				false,
				false);


/**
 * Web service for making a blog post
 *
 * @param string $username username of author
 * @param string $title    the title of blog
 * @param string $excerpt  the excerpt of blog
 * @param string $text     the content of blog
 * @param string $tags     tags for blog
 * @param string $access   Access level of blog
 *
 * @return bool
 */
function blog_save($title, $text, $excerpt, $tags , $access, $container_guid) {
	$user = get_loggedin_user();
	if (!$user) {
		throw new InvalidParameterException('registration:usernamenotvalid');
	}
	
	$obj = new ElggObject();
	$obj->subtype = "blog";
	$obj->owner_guid = $user->guid;
	$obj->container_guid = $container_guid;
	$obj->access_id = strip_tags($access);
	$obj->method = "api";
	$obj->description = strip_tags($text);
	$obj->title = elgg_substr(strip_tags($title), 0, 140);
	$obj->status = 'published';
	$obj->comments_on = 'On';
	$obj->excerpt = strip_tags($excerpt);
	$obj->tags = strip_tags($tags);
	$guid = $obj->save();
	add_to_river('river/object/blog/create',
	'create',
	$user->guid,
	$obj->guid
	);
	$return['success'] = true;
	$return['message'] = elgg_echo('blog:message:saved');
	return $return;
	} 
	
expose_function('blog.save_post',
				"blog_save",
				array(
						'title' => array ('type' => 'string', 'required' => true),
						'text' => array ('type' => 'string', 'required' => true),
						'excerpt' => array ('type' => 'string', 'required' => false),
						'tags' => array ('type' => 'string', 'required' => false, 'default' => "blog"),
						'access' => array ('type' => 'string', 'required' => false, 'default'=>ACCESS_PUBLIC),
						'container_guid' => array ('type' => 'int', 'required' => false, 'default' => 0),
					),
				"Post a blog post",
				'POST',
				true,
				false);
/**
 * Web service for delete a blog post
 *
 * @param string $guid     GUID of a blog entity
 * @param string $username Username of reader (Send NULL if no user logged in)
 * @param string $password Password for authentication of username (Send NULL if no user logged in)
 *
 * @return bool
 */
function blog_delete_post($guid, $username) {
	$return = array();
	$blog = get_entity($guid);
	$return['success'] = false;
	if (!elgg_instanceof($blog, 'object', 'blog')) {
		throw new InvalidParameterException('blog:error:post_not_found');
	}
	
	$user = get_user_by_username($username);
	if (!$user) {
		throw new InvalidParameterException('registration:usernamenotvalid');
	}
	$blog = get_entity($guid);
	if($user->guid!=$blog->owner_guid) {
		$return['message'] = elgg_echo('blog:message:notauthorized');
	}

	if (elgg_instanceof($blog, 'object', 'blog') && $blog->canEdit()) {
		$container = get_entity($blog->container_guid);
		if ($blog->delete()) {
			$return['success'] = true;
			$return['message'] = elgg_echo('blog:message:deleted_post');
		} else {
			$return['message'] = elgg_echo('blog:error:cannot_delete_post');
		}
	} else {
		$return['message'] = elgg_echo('blog:error:post_not_found');
	}
	
	return $return;
}
	
expose_function('blog.delete_post',
				"blog_delete_post",
				array('guid' => array ('type' => 'string'),
						'username' => array ('type' => 'string'),
					),
				"Read a blog post",
				'POST',
				true,
				false);			
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
function blog_get_post($guid, $username) {

    $return = array();
    $blog = get_entity($guid);

//    if (!elgg_instanceof($blog, 'object', 'new_user_email')) {
    if (!elgg_instanceof($blog, 'object', 'blog')) {
        $return['content'] = elgg_echo('blog:error:post_not_found');
        return $return;
    }
    
    $user = get_user_by_username($username);
    if ($user) {
        if (!has_access_to_entity($blog, $user)) {
            $return['content'] = elgg_echo('blog:error:post_not_found');
            return $return;
        }
        
        if ($blog->status!='published' && $user->guid!=$blog->owner_guid) {
            $return['content'] = elgg_echo('blog:error:post_not_found');
            return $return;
        }
    } else {
        if($blog->access_id!=2) {
            $return['content'] = elgg_echo('blog:error:post_not_found');
            return $return;
        }
    }

    $return['title'] = htmlspecialchars($blog->title);
    $return['content'] = strip_tags($blog->description);
    $return['excerpt'] = $blog->excerpt;
    $return['tags'] = $blog->tags;
    $return['owner_guid'] = $blog->owner_guid;
    $return['access_id'] = $blog->access_id;
    $return['status'] = $blog->status;
    $return['comments_on'] = $blog->comments_on;
    return $return;
}
    
expose_function('blog.get_post',
                "blog_get_post",
                array('guid' => array ('type' => 'string'),
                        'username' => array ('type' => 'string', 'required' => false),
                    ),
                "Read a blog post",
                'GET',
                false,
                false);
/**
 * Web service to retrieve comments on a blog post
 *
 * @param string $guid blog guid
 * @param string $limit    Number of users to return
 * @param string $offset   Indexing offset, if any
 * @param string $type     comment type: 0/1/2
 * @param string $context  what is based upon to get the comment. can be "user" or "post"
 *
 * @return array
 */                    
function blog_get_comments($guid, $limit = 10, $offset, $type, $context, $username){
    if ($type == 0) {
        $comment_type = "generic_comment";
    } else if ($type == 1) {
        $comment_type = "product_comment";
    } else if ($type == 2) {
        $comment_type = "ideas_comment";
    } else {
        throw new InvalidParameterException("blog:comment_type");
    }

//    $blog = get_entity($guid);

    if ($context == "post") {
        $options = array(
            'annotations_name' => $comment_type,
            'guid' => $guid,
            'limit' => $limit,
            'pagination' => false,
            'reverse_order_by' => true,
        );
    } else if ($context == "user") {
        if(!$username) {
            $user = get_loggedin_user();
        } else {
           $user = get_user_by_username($username);
            if (!$user) {
                throw new InvalidParameterException('registration:usernamenotvalid');
	    }
        }  
        $options = array(
            'annotations_name' => $comment_type,
            'annotation_owner_guid' => $user->guid,
            'limit' => $limit,
            'pagination' => false,
            'reverse_order_by' => true,
        );
    } else {
        throw new InvalidParameterException('registration:contextnamenotvalid');
    }
    $comments = elgg_get_annotations($options);

    $total_num = 0;
    if($comments){
        foreach($comments as $single){
            $comment['guid'] = $single->id;

            if ($type == 1) {
                $comment_rate = unserialize(strip_tags($single->value));
                $comment['description'] = $comment_rate['text'];
                $comment['rate'] = $comment_rate['rate'] ? $comment_rate['rate'] : "0";
            } else {

                $comment_rate = unserialize(strip_tags($single->value));
//                if ($comment_rate['text']) {
//                    $comment['description'] = $comment_rate['text'];
//                } else {
                    $comment['description'] = $comment_rate;
//                }
                $comment['rate'] = "0";
            }

            $entity = get_entity($single->entity_guid);

            if ($entity) {
                $comment['entity']['guid'] = $single->entity_guid;
                $comment['entity']['title'] = $entity->title;
                if ($type == 1) { //product
                    $post_images = unserialize($entity->images);
   	            $blog['images'] = null;
                    foreach ($post_images as $key => $value) {
                        if ($value == 1) {
                            $blog['images'][] = elgg_normalize_url("market/image/".$entity->guid."/$key/"."large/");
                        } 
                    }
                    $comment['entity']['images'] = $blog['images'];
                } else if ($type == 2) { // idea
                    $comment['entity']['images'] = $entity->tip_thumbnail_image_url;
                }
            }
            $owner = get_entity($single->owner_guid);
            if ($owner) {
                $comment['review_user']['guid'] = $owner->guid;
                $comment['review_user']['name'] = $owner->name;
                $comment['review_user']['username'] = $owner->username;
                $comment['review_user']['avatar_url'] = get_entity_icon_url($owner,'small');
                $comment['review_user']['is_seller'] = $owner->is_seller;
            }        
            $comment['time_created'] = (int)$single->time_created;
            $return['reviews'][] = $comment;
            $total_num ++;
        }
    } else {
        $msg = elgg_echo('comment:$comment_type:none');
        throw new InvalidParameterException($msg);
    }
    $return['total_number'] = $total_num;
    return $return;
}
expose_function('blog.get_comments',
                "blog_get_comments",
                array(  'guid' => array ('type' => 'string', 'required' => false, 'default' => 0),
                        'limit' => array ('type' => 'int', 'required' => false, 'default' => 10),
                        'offset' => array ('type' => 'int', 'required' => false, 'default' => 0),
                        'type' => array ('type' => 'int', 'required' => false, 'default' => 0),
                        'context' => array ('type' => 'string', 'required' => false, 'default' => 'post'),
                        'username' => array ('type' => 'string', 'required' => false, 'default' => ""),
                    ),
                "Get comments for a blog post",
                'GET',
                true,
                false);    
/**
 * Web service to comment on a post
 *
 * @param int $guid blog guid
 * @param string $text
 * @param int $access_id
 * @param int $rate
 * @param int $type  (0: generic_comment; 1: product_comment; 2: ideas_comment)
 *
 * @return array
 */                    
function blog_post_comment($guid, $text, $rate, $type){
    
    $entity = get_entity($guid);
    $user = elgg_get_logged_in_user_entity();

    $num_comments = 0;
    $old_rate = 0;

    if ($type == 1) { // add rate for product comment
        $comment['rate'] = $rate;
        $comment['text'] = $text;
    } else {
/*
        $comment['rate'] = $rate;
        $comment['text'] = $text;
*/
        $comment = $text;
    }
    if ($type == 0) {
        $comment_type = "generic_comment";
    } else if ($type == 1) {
        $comment_type = "product_comment";

        $options = array(
                'annotations_name' => 'product_comment',
                'guid' => $entity->guid,
                'limit' => 0,
                'pagination' => false,
                'reverse_order_by' => true,
                );

        $comments = elgg_get_annotations($options);
        $num_comments = count($comments);
        $old_rate = $entity->rate;
    } else if ($type == 2) {
        $comment_type = "ideas_comment";
    } else {
        throw new InvalidParameterException("blog:comment_type");
    }

    // for product, we only allow one comment.
    if (($type == 1) && elgg_annotation_exists($entity->guid, $comment_type, $user->guid)) {
        // get and update the existing comment.
        $options = array(
            'annotations_name' => $comment_type,
            'annotation_owner_guid' => $user->guid,
            'limit' => 1,
            'pagination' => false,
            'reverse_order_by' => true,
        );

        $my_comment = elgg_get_annotations($options);

        if(!$my_comment){
            throw new InvalidParameterException("blog:comment:update:null");
	}

        $annotation = update_annotation($my_comment[0]->id,
            $comment_type,
            serialize($comment),
            "",
            $user->guid,
            $entity->access_id);
        // update overall product rate
        $entity->rate = ($entity->rate * $num_comments - $old_rate + $rate) / $num_comments;
        $entity->save();
    } else {
        $annotation = create_annotation($entity->guid,
            $comment_type,
            serialize($comment),
            "",
            $user->guid,
            $entity->access_id);
        $entity->rate = ($entity->rate * $num_comments + $rate) / ($num_comments + 1);
        $entity->save();
    }
    if($annotation){
        // notify if poster wasn't owner
        if ($entity->owner_guid != $user->guid) {

            notify_user($entity->owner_guid,
                    $user->guid,
                    elgg_echo('generic_comment:email:subject'),
                    elgg_echo('generic_comment:email:body', array(
                        $entity->title,
                        $user->name,
                        $comment['text'],
                        $entity->getURL(),
                        $user->name,
                        $user->getURL()
                ))
            );
        }
        $return['success']['message'] = elgg_echo("$comment_type:posted");
    } else {
        $msg = elgg_echo('comment:$comment_type:failure');
        throw new InvalidParameterException($msg);
    }
    return $return;
}
expose_function('blog.post_comment',
                "blog_post_comment",
                array(    'guid' => array ('type' => 'int'),
                          'text' => array ('type' => 'string'),
                          'rate' => array ('type' => 'float', 'required' => false, 'default' => 0),
                          'type' => array ('type' => 'int', 'required' => false, 'default' => 0),
                    ),
                "Post a comment with rate on a blog post",
                'POST',
                true,
                true);

/**
 * Web service for delete a blog post
 *
 * @param string $guid     GUID of a blog entity
 * @param string $username Username of reader (Send NULL if no user logged in)
 * @param string $password Password for authentication of username (Send NULL if no user logged in)
 *
 * @return bool
 */
function blog_delete_post2($guid) {
        if (!elgg_is_logged_in()) {
            register_error(elgg_echo("blog:notloggedin"));
        }
        $comment = elgg_get_annotation_from_id($guid);
        $user = elgg_get_logged_in_user_entity();
        if ($user->guid != $comment->owner_guid) {
            throw new InvalidParameterException("user is not authorized to delete");
        }

	$return = array();
	$return['success'] = elgg_delete_annotation_by_id($guid);
	return $return;
}
	
expose_function('blog.delete_post2',
    "blog_delete_post2",
    array('guid' => array ('type' => 'string'),
    ),
    "Delete a blog post",
    'POST',
    true,
    true);			