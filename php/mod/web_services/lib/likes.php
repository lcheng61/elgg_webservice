<?php
/**
 * Elgg Webservices plugin 
 * 
 * @package Webservice
 * @author Saket Saurabh
 *
 */
 
 /**
 * Web service to like an entity
 *
 * @param string $entity_guid guid of object to like
 *
 * @return bool
 */
function likes_add($entity_guid) {
//     echo ("user = \n");

     if (elgg_annotation_exists($entity_guid, 'likes')) {
//         return elgg_echo("likes:alreadyliked");
         return "likes:alreadyliked";
     }
     // Let's see if we can get an entity with the specified GUID
     $entity = get_entity($entity_guid);
     if (!$entity) {
//         return elgg_echo("likes:notfound");
         return "likes:notfound";
     }
     // limit likes through a plugin hook (to prevent liking your own content for example)
     if (!$entity->canAnnotate(0, 'likes')) {
//         return elgg_echo("likes:notallowed");
         return "likes:notallowed";
     }
     $user = elgg_get_logged_in_user_entity();
     $annotation = create_annotation($entity->guid,
                                        'likes',
                                        "likes",
                                        "",
                                        $user->guid,
                                        $entity->access_id);
      // tell user annotation didn't work if that is the case
      if (!$annotation) {
//          return elgg_echo("likes:failure");
          return "likes:failure";
      }
      add_to_river('annotation/annotatelike', 'likes', $user->guid, $entity->guid, "", 0, $annotation);

     if ($entity->getsubtype() == "market") {
          $user->liked_products .= "$entity_guid,";
     } else if ($entity->getsubtype() == "ideas") {
          $user->liked_ideas .= "$entity_guid,";
     }
//     return elgg_echo("likes:likes");
     return "likes:likes";
} 
            
expose_function('likes.add',
                "likes_add",
                array('entity_guid' => array ('type' => 'int'),
                ),
                "Add a like",
                'POST',
                 true,
                 false);
                                                                     
/**
 * Web service to unlike an entity
 *
 * @param string $entity_guid guid of object to like
 *
 * @return bool
 */
function likes_delete($entity_guid) {
     $entity = get_entity($entity_guid);
     if (!$entity) {
//         return elgg_echo("likes:notfound");
         return "likes:notfound";
     }
    $likes = elgg_get_annotations(array(
        'guid' => $entity_guid,
        'annotation_owner_guid' => elgg_get_logged_in_user_guid(),
        'annotation_name' => 'likes',
    ));
    if ($likes) {
        if ($likes[0]->canEdit()) {
            $likes[0]->delete();

            $user = elgg_get_logged_in_user_entity();
            $pattern = "/$entity_guid".",/";
            $replacement = "";
            $match_limit = -1;
            $match_count = 0;

//echo("subtype: $entity->getsubtype()<br>");
            if ($entity->getsubtype() == "market") {
//                echo("before delete: $user->liked_products<br>");
                $user->liked_products = preg_replace($pattern, $replacement, $user->liked_products, $match_limit, $match_count);
//                echo("after delete: $user->liked_products<br>");
            } else if ($entity->getsubtype() == "ideas") {
               $user->liked_ideas = preg_replace($pattern, $replacement, $user->liked_ideas, $match_limit, $match_count);
            }
//            return elgg_echo("likes:deleted");
            return "likes:deleted";
        }
    }
 
//    return elgg_echo("likes:notdeleted");
    return "likes:notdeleted";
} 
            
expose_function('likes.delete',
                "likes_delete",
                array('entity_guid' => array ('type' => 'int'),
                     ),
               "Delete a like",
               'POST',
               true,
               false);
                                                                    
/**
 * Web service to count number of likes
 *
 * @param string $entity_guid guid of object 
 *
 * @return bool
 */
function likes_count_number_of_likes($entity_guid) {
     $entity = get_entity($entity_guid);
     return likes_count($entity);
} 
            
expose_function('likes.count',
                "likes_count_number_of_likes",
                array('entity_guid' => array ('type' => 'int'),
                     ),
                "Count number of likes",
                'GET',
                false,
                false);
                                                                          
/**
 * Web service to get users who liked an entity
 *
 * @param string $entity_guid guid of object 
 *
 * @return bool
 */
function likes_getusers($entity_guid) {
    $entity = get_entity($entity_guid);
    if( likes_count($entity) > 0 ) {
        $list = elgg_get_annotations(array('guid' => $entity_guid, 'annotation_name' => 'likes', 'limit' => 99));
        foreach($list as $singlelike) {
            $like['userid'] = $singlelike->owner_guid;
            $like['time_created'] = $singlelike->time_created;
//            $like['access_id'] = $singlelike->access_id;
            $return[] = $like;
        }
    }
    else {
//        $likes = elgg_echo('likes:userslikedthis', array(likes_count($entity)));
        $return = "likes:userslikedthis is 0";
    }
    return $return;
}

                
expose_function('likes.getusers',
                "likes_getusers",
                array('entity_guid' => array ('type' => 'int'),
                     ),
                "Get users who liked an entity",
                'GET',
                false,
                false);

/**
 * Web service to get products liked by the user
 *
 * @param string $entity_guid guid of user
 * @param string $subtype can be "market", "ideas" or "all"
 *
 * @return bool
 */
function likes_getitems($username, $limit=10, $offset=0) {
    if(!$username) {
        $user = get_loggedin_user();
    } else {
        $user = get_user_by_username($username);
        if (!$user) {
            throw new InvalidParameterException('registration:usernamenotvalid');
	}
    }
///////////////////////
    $dbprefix = elgg_get_config('dbprefix');
    $likes_metastring = get_metastring_id('likes');

    $entity = get_user_by_username($username);
    if (!elgg_instanceof($entity, 'user')) {
        return false;
    }
//    $filter = 'most_liked'; // get_input('filter'); // XXX
    $options = array(
        'annotation_names' => array('likes'),
        'annotation_owner_guids' => array($entity->guid),
        'order_by' => 'maxtime DESC',
        'full_view' => false,
    );
/*										
    if ($filter == 'most_liked') {
        $options = array(
            'container_guid' => $entity->guid,
	    'annotation_names' => array('likes'),
	    'selects' => array("(SELECT count(distinct l.id) FROM {$dbprefix}annotations l WHERE l.name_id = $likes_metastring AND l.entity_guid = e.guid) AS likes"),
	    'order_by' => 'likes DESC',
	    'full_view' => false
	);
    }
*/
    $content = elgg_list_entities_from_annotations($options);

    if (!$content) {
        $content = elgg_echo('liked_content:noresults');
    }
    $title = elgg_echo('liked_content:liked_content');
    $layout = elgg_view_layout('content', array(
             'title' => elgg_view_title($title),
//             'content' => $content,
	     'filter' => elgg_view('liked_content/navigation/filter'),
    ));
// copy layout to return;

    $liked_items = json_decode(elgg_view_page($title, $layout));
    $liked_products = $liked_items->object->market;
    $liked_ideas = $liked_items->object->ideas;

    $blog = array();
    $display_liked_products_number = 0;
    foreach($liked_products as $product_info ) {
        $single = get_entity($product_info->guid);
        $blog['product_id'] = $single->guid;
        $options = array(
                'annotations_name' => 'generic_comment',
                'guid' => $single->guid,
                'limit' => $limit,
                'pagination' => false,
                'reverse_order_by' => true,
                );

        $comments = elgg_get_annotations($options);
        $num_comments = count($comments);

        $display_liked_products_number++;
        $blog['product_name'] = $single->title;
        $blog['product_price'] = floatval($single->price);
        $blog['tips_number'] = $single->tips_number;
        //XXX: hard-code sold_count;		 		 
        $single->sold_count = 0;
        $blog['sold_number'] = $single->sold_count;
        $blog['product_category'] = $single->marketcategory;

        $post_images = unserialize($single->images);
        $blog['images'] = null;
        foreach ($post_images as $key => $value) {
            if ($value == 1) {
                $blog['images'][] = elgg_normalize_url("market/image/".$single->guid."/$key/"."large/");
            }
        }
	if ($single->is_affiliate) {
              $blog['images'][] = ($single->affiliate_product_url ? $single->affiliate_product_url : "");
	}
        
        $blog['is_affiliate'] = ($single->is_affiliate ? $single->is_affiliate : 0);
        $blog['likes_number'] = intval(likes_count(get_entity($single->guid)));
        $blog['reviews_number'] = $num_comments;

        $owner = get_entity($single->owner_guid);
        $blog['product_seller']['user_id'] = $owner->guid;
        $blog['product_seller']['user_name'] = $owner->username;
        $blog['product_seller']['user_avatar_url'] = get_entity_icon_url($owner,'small');
        $blog['product_seller']['is_seller'] = ($owner->is_seller == "true");
        $blog['product_seller']['do_i_follow'] = user_is_friend($user->guid, $owner->guid);
 
        $return['liked_products'][] = $blog;
    }
    $return['liked_products_num'] = $display_liked_products_number;

    // liked ideas
    $blog = array();
    $display_liked_ideas_number = 0;
    foreach($liked_ideas as $idea_info ) {
        $single = get_entity($idea_info->guid);
        $blog['tip_id'] = $single->guid;
        $options = array(
                'annotations_name' => 'generic_comment',
                'guid' => $single->guid,
                'limit' => $limit,
                'pagination' => false,
                'reverse_order_by' => true,
                );

         $comments = elgg_get_annotations($options);
         $num_comments = count($comments);

         $display_liked_ideas_number++;
         $blog['tip_title'] = $single->title;
         $blog['tip_category'] = $single->ideascategory;
         $blog['tip_thumbnail_image_url'] = $single->tip_thumbnail_image_url;
         $blog['likes_number'] = likes_count(get_entity($single->guid));
         $blog['comments_number'] = $num_comments;

         $owner = get_entity($single->owner_guid);
         $blog['tip_author']['user_id'] = $owner->guid;
         $blog['tip_author']['user_name'] = $owner->username;
         $blog['tip_author']['user_avatar_url'] = get_entity_icon_url($owner,'small');
         $blog['tip_author']['is_seller'] = $owner->is_seller;
         $blog['tip_author']['do_i_follow'] = user_is_friend($user->guid, $owner->guid);

         $blog['likes_number'] = likes_count(get_entity($single->guid));
         $blog['comments_number'] = $num_comments;

         $blog['products_number'] = $single->countEntitiesFromRelationship("sponsor", false);

         $return['liked_ideas'][] = $blog;
    }
    $return['liked_ideas_num'] = $display_liked_ideas_number;

    return $return;

}

expose_function('likes.getitems',
                "likes_getitems",
                array('username' => array ('type' => 'string', 'required' => false),
                        'limit' => array ('type' => 'int', 'required' => false),
                        'offset' => array ('type' => 'int', 'required' => false),
                     ),
                "Get products liked by the current user",
                'GET',
                false,
                false);
