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
         return elgg_echo("likes:alreadyliked");
     }
     // Let's see if we can get an entity with the specified GUID
     $entity = get_entity($entity_guid);
     if (!$entity) {
         return elgg_echo("likes:notfound");
     }
     // limit likes through a plugin hook (to prevent liking your own content for example)
     if (!$entity->canAnnotate(0, 'likes')) {
         return elgg_echo("likes:notallowed");
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
          return elgg_echo("likes:failure");
      }
      add_to_river('annotation/annotatelike', 'likes', $user->guid, $entity->guid, "", 0, $annotation);

     if ($entity->getsubtype() == "market") {
          $user->liked_products .= "$entity_guid,";
     } else if ($entity->getsubtype() == "ideas") {
          $user->liked_ideas .= "$entity_guid,";
     }
     return elgg_echo("likes:likes");
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
         return elgg_echo("likes:notfound");
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
            return elgg_echo("likes:deleted");
        }
    }
 
    return elgg_echo("likes:notdeleted");
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
        $likes = elgg_echo('likes:userslikedthis', array(likes_count($entity)));
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
 *
 * @return bool
 */
function likes_getitems($entity_guid, $subtype) {

    $user = get_entity($entity_guid);

    echo ("liked_products = ".$user->liked_products."<br>");
    echo ("liked_ideas = ".$user->liked_ideas."<br>");

    if ($subtype == "ideas") { 
        $objs_array = explode(",", $user->liked_ideas);
        foreach ($objs_array as $id) {
//echo("hello here1 <br>");
            $id = intval($id);
            $obj_post = get_entity($id);
            if ($obj_post) { // if the product id is a valid one
                $blog['product_id'] = $obj_post->guid;
                $options = array(
                        'annotations_name' => 'generic_comment',
                        'guid' => $obj_post->$guid,
                        'limit' => $limit,
                        'pagination' => false,
                        'reverse_order_by' => true,
                        );

                 $comments = elgg_get_annotations($options);
                 $num_comments = count($comments);

//                 echo ("obj_post = $obj_post->title");

                 $blog['product_name'] = $obj_post->title;
                 $blog['product_price'] = $obj_post->price;
                 $blog['tips_number'] = $obj_post->tips_number;
		 //XXX: hard-code sold_count;		 		 
                 $single->sold_count = 0;
                 $blog['sold_number'] = $obj_post->sold_count;
                 $blog['product_category'] = $obj_post->marketcategory;
                 $blog['product_image'] = elgg_normalize_url("market/image/".$obj_post->guid."/1/"."large/");
                 $blog['likes_number'] = likes_count(get_entity($obj_post->guid));
                 $blog['reviews_number'] = $num_comments;

                 $owner = get_entity($obj_post->owner_guid);
                 $blog['product_seller']['user_id'] = $owner->guid;
                 $blog['product_seller']['user_name'] = $owner->username;
                 $blog['product_seller']['user_avatar_url'] = get_entity_icon_url($owner,'small');
                 $blog['product_seller']['is_seller'] = $owner->is_seller;
            
                 $return[] = $blog;
            }
        } // done with listing products of that tip.
    } else if ($subtype == "market") { 
        $objs_array = explode(",", $user->liked_products);
        foreach ($objs_array as $id) {
//echo("hello here2: $id <br>");
            $id = intval($id);
            $obj_post = get_entity($id);
            if ($obj_post) { // if the product id is a valid one
                $blog['product_id'] = $obj_post->guid;
                $options = array(
                        'annotations_name' => 'generic_comment',
                        'guid' => $obj_post->$guid,
                        'limit' => $limit,
                        'pagination' => false,
                        'reverse_order_by' => true,
                        );

                 $comments = elgg_get_annotations($options);
                 $num_comments = count($comments);

//                 echo ("obj_post = $obj_post->title");

                 $blog['product_name'] = $obj_post->title;
                 $blog['product_price'] = $obj_post->price;
                 $blog['tips_number'] = $obj_post->tips_number;
                 $blog['sold_number'] = $obj_post->sold_count;
                 $blog['product_category'] = $obj_post->marketcategory;
                 $blog['product_image'] = elgg_normalize_url("market/image/".$obj_post->guid."/1/"."large/");
                 $blog['likes_number'] = likes_count(get_entity($obj_post->guid));
                 $blog['reviews_number'] = $num_comments;

                 $owner = get_entity($obj_post->owner_guid);
                 $blog['product_seller']['user_id'] = $owner->guid;
                 $blog['product_seller']['user_name'] = $owner->username;
                 $blog['product_seller']['user_avatar_url'] = get_entity_icon_url($owner,'small');
                 $blog['product_seller']['is_seller'] = $owner->is_seller;
            
                 $return[] = $blog;
            }
        } // done with listing products of that tip.
    }
    return $return;
}

expose_function('likes.getitems',
                "likes_getitems",
                array('entity_guid' => array ('type' => 'int'),
                      'subtype' => array ('type' => 'string'),
                     ),
                "Get products liked by the current user",
                'GET',
                false,
                false);
