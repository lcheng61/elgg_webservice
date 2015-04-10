<?php

/**
 * Elgg Webservices plugin 
 * market product
 * 
 * @package Webservice
 * @author Liang Cheng
 *
 */
//////////////
function product_get_posts($context, $limit = 10, $offset = 0, $from_seller_portal,
    $group_guid, $category, $username) {

    if (($from_seller_portal != 1) && ($limit != 0)) {
        $recommend_list = recommend_list($category, $offset, $limit);

        if ($recommend_list['total_number'] != 0) {
            if ($recommend_list['total_number'] < $limit) {
                $product_limit = $limit - $recommend_list['total_number'];
                $product_offset = $recommend_list['total_number'];
                $product_return = product_get_posts_common($context, $product_limit, 0, $from_seller_portal,
                        $group_guid, $category, $username);
            }
            if (is_array($product_return['products'])) {
                $recommend_list['products'] = array_merge($recommend_list['products'], $product_return['products']);
            }
            $recommend_list['total_number'] = count($recommend_list['products']);
            return $recommend_list;
        }

        $recommend_list = recommend_list($category, 0, 0);
        $total_number_recommend = count($recommend_list['products']);
        if ($offset >= $total_number_recommend) {
            $offset -= $total_number_recommend;
        }
    }
    return product_get_posts_common($context, $limit, $offset, $from_seller_portal,
            $group_guid, $category, $username);
}

expose_function('product.get_posts',
                "product_get_posts",
                array(
                      'context' => array ('type' => 'string', 'required' => false, 'default' => 'all'),
                      'limit' => array ('type' => 'int', 'required' => false, 'default' => 10),
                      'offset' => array ('type' => 'int', 'required' => false, 'default' => 0),
                      'from_seller_portal' => array ('type' => 'int', 'required' => false, 'default' => 0),
                      'group_guid' => array ('type'=> 'int', 'required'=>false, 'default' =>0),
                      'category' => array ('type' => 'string', 'required' => false, 'default' => 'all'),
                      'username' => array ('type' => 'string', 'required' => false, 'default' => ''),
                    ),
                "Get list of market posts",
                'GET',
                false,
                false);

 /**
 * Web service to get market product list by all users
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

function product_get_posts_common($context, $limit = 10, $offset = 0, $from_seller_portal,
    $group_guid, $category, $username) {

    if($context == "mine" && !get_loggedin_user()){
        throw new InvalidParameterException('registration:minenotvalid');
    }

    if($context == "user" && $username == ""){
        throw new InvalidParameterException('registration:usernamenotvalid');
    }
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
            'metadata_name_value_pairs' => array(
                array(
                    'name' => 'marketcategory',
                    'value' => $category,
                    'case_sensitive' => false
                ),
            )
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
            'metadata_name_value_pairs' => array(
                array(
                    'name' => 'marketcategory',
                    'value' => $category,
                    'case_sensitive' => false
                ),
            )
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
            'metadata_name_value_pairs' => array(
                array(
                    'name' => 'marketcategory',
                    'value' => $category,
                    'case_sensitive' => false
                ),
            )
        );
    }
    if($context == "friends"){
        $latest_blogs = get_user_friends_objects($user->guid, 'market', $limit, $offset);
    }
    if (!$from_seller_portal) {
        if ($category == "all") {
	    $latest_blogs = elgg_get_entities($params);
 	} else {
	    $latest_blogs = elgg_get_entities_from_metadata($params);
        }
    } else { // hackhack, server loop to avoid database memory leak. This should be replaced by client pagination
        $tmp = array();
        $latest_blogs = array();
        $step = 50;
        $limit = $step;
        $offset_tmp = 0;
        $iter_count = 0;
        $total_steps = 10;

        do {
            $params = array(
                'types' => 'object',
                'subtypes' => 'market',
                'owner_guid' => $user->guid,
                'limit' => $limit,
                'full_view' => FALSE,
                'offset' => $offset_tmp,
            );
            $tmp = elgg_get_entities($params);
            $offset_tmp += $step;
            $latest_blogs = array_merge($tmp, $latest_blogs);
            $iter_count ++;
            if ($iter_count >= $total_steps) {
                break;
            }
        } while($tmp);
    }

    if($latest_blogs) {
        $return['category'] = $category;
        $return['offset'] = $offset;

        $display_product_number = 0;
        foreach($latest_blogs as $single ) {

//            if (($single->marketcategory == $category) || 
//                    ($category == "all")) {
            if (1) {
                $blog['product_id'] = $single->guid;

                $comments = $single->getAnnotations(
                     'product_comment',    // The type of annotation
                     0,   // The number to return
                     0,  // Any indexing offset
                     'asc'   // 'asc' or 'desc' (default 'asc')
                );
                $num_comments = count($comments);
//                   $num_comments = $single->getAnnotationsSum('product_comment');

                 $display_product_number++;
                 $blog['product_name'] = $single->title;
                 $blog['product_price'] = floatval($single->price);

                 $items = $single->getEntitiesFromRelationship("sponsor", true, 0, 0);
                 $blog['tips_number'] = count($items);

		 //XXX: hard-code sold_count;		 		 
                 $single->sold_count = 0;
                 $blog['sold_count'] = $single->sold_count;

                 if ($from_seller_portal) {
                     $product_id = $single->guid;
                     $like = elgg_get_annotation_from_id($product_id);
                     if (!$like) {
                         $likes = elgg_get_annotations(array(
                                 'guid' => $product_id,
                                 'annotation_owner_guid' => elgg_get_logged_in_user_guid(),
                                 'annotation_name' => 'likes',
                         ));
                         $like = $likes[0];
                     }
                     $blog['liked'] = ($like && $like->canEdit());
                     $blog['likes_number'] = intval(likes_count(get_entity($product_id)));

                     $items = $single->getEntitiesFromRelationship("sponsor", true, 0, 0);
                     $blog['tips_number'] = count($items);

                     $blog['delivery_time'] = $single->delivery_time;

                     $comments = $single->getAnnotations(
                             'product_comment',    // The type of annotation
                              0,   // The number to return
                              0,  // Any indexing offset
                             'asc'   // 'asc' or 'desc' (default 'asc')
                     );
                     $blog['reviews_number'] = count($comments);

                     $blog['product_description'] = $single->description;
                 } // if (from_seller_portal)
                 $blog['shipping_fee'] = $single->shipping_fee;
                 $blog['free_shipping_quantity_limit'] = $single->free_shipping_quantity_limit;
                 $blog['free_shipping_cost_limit'] = $single->free_shipping_cost_limit;

                 if ($single->quantity < 0) {
                     $blog['quantity'] = 0;
                 } else {
                     $blog['quantity'] = $single->quantity;
		 }

                 $blog['rate'] = $single->rate;
                 $blog['product_category'] = $single->marketcategory;

                 $post_images = unserialize($single->images);
		 $blog['images'] = null;
                 foreach ($post_images as $key => $value) {
                     if ($value == 1) {
//                         $blog['images'][] = elgg_normalize_url("market/image/".$single->guid."/$key/"."large/");
                         $blog['images'][] = elgg_get_config('cdn_link').'/market/image/'.$single->guid.'/'.$key.'/'.'large/';
                     }
                 }

// affiliate attributes
                 $blog['affiliate']['is_affiliate'] = ($single->is_affiliate ? $single->is_affiliate : 0);
                 $blog['affiliate']['affiliate_product_id'] = ($single->affiliate_product_id ? $single->affiliate_product_id : 0);
                 $blog['affiliate']['affiliate_product_url'] = ($single->affiliate_product_url ? $single->affiliate_product_url : "");
                 $blog['affiliate']['is_archived'] = ($single->is_archived ? $single->is_archived : 0);
                 $blog['affiliate']['affiliate_syncon'] = ($single->affiliate_syncon ? $single->affiliate_syncon : 0);
                 if ($blog['affiliate']['is_affiliate'] == 1) {
                     $blog['images'][] = $single->affiliate_image;
                 }
//~

                 $blog['likes_number'] = intval(likes_count(get_entity($single->guid)));
                 $blog['reviews_number'] = $num_comments;

                 $owner = get_entity($single->owner_guid);
                 $blog['product_seller']['user_id'] = $owner->guid;
//                $blog['product_seller']['name'] = $owner->name;
                 $blog['product_seller']['user_name'] = $owner->username;
                 $blog['product_seller']['user_avatar_url'] = get_entity_icon_url($owner,'small');
                 $blog['product_seller']['is_seller'] = ($owner->is_seller == "true");
                 $blog['product_seller']['do_i_follow'] = user_is_friend($user->guid, $owner->guid);
               
                 $blog['is_recommend'] = $single->is_recommend;
 
//                $blog['container_guid'] = $single->container_guid;
//                $blog['access_id'] = $single->access_id;
//                $blog['time_created'] = (int)$single->time_created;
//                $blog['time_updated'] = (int)$single->time_updated;
//                $blog['last_action'] = (int)$single->last_action;
                 $return['products'][] = $blog;
            }
        }
        $return['total_number'] = $display_product_number;
    }
    else {
        $return['total_number'] = 0;
        $return['products'] = "";
    }

    return $return;
}


expose_function('product.get_posts_common',
                "product_get_posts_common",
                array(
                      'context' => array ('type' => 'string', 'required' => false, 'default' => 'all'),
                      'limit' => array ('type' => 'int', 'required' => false, 'default' => 10),
                      'offset' => array ('type' => 'int', 'required' => false, 'default' => 0),
                      'from_seller_portal' => array ('type' => 'int', 'required' => false, 'default' => 0),
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
 * @param int $guid
 * @param int $username (optional) default 0
 *
 * @return array $file Array of files uploaded
 */

function product_get_detail($product_id) {
    $return = array();
    $blog = get_entity($product_id);

    if (!elgg_instanceof($blog, 'object', 'market')) {
        $return['content'] = elgg_echo('blog:error:post_not_found');
        return $return;
    }

//    $return['product_name'] = htmlspecialchars($blog->title);
    $return['product_name'] = $blog->title;

    $images = unserialize($blog->images);

    foreach ($images as $key => $value) {
        if ($value == 1) {
//            $return['images'][] = elgg_normalize_url("market/image/".$blog->guid."/$key/"."large/");
            $return['images'][] = elgg_get_config('cdn_link').'/market/image/'.$blog->guid.'/'.$key.'/'.'master/';
        } else {
	    $return['images'][] = "";
	}
    }
    // get ideas linked to this product
    $items = $blog->getEntitiesFromRelationship("sponsor", true, 0, 0);

    foreach ($items as $item) {
        $idea['id'] = $item->guid;
        $idea['name'] = $item->tip_title;
        $return['ideas'][] = $idea;
    }

    $return['content'] = strip_tags($blog->description);
    $return['product_id'] = $product_id;
    $return['product_price'] = floatval($blog->price);
    $return['product_description'] = $blog->description;

    $return['category'] = $blog->marketcategory;
    if ($blog->quantity < 0) {
        $return['quantity'] = 0;
    } else {
        $return['quantity'] = $blog->quantity;
    }

///// seller portal used
    $return['tags'] = $blog->tags;
    $return['delivery_time'] = $blog->delivery_time;
    $return['shipping_fee'] = floatval($blog->shipping_fee);
    $return['free_shipping_quantity_limit'] = $blog->free_shipping_quantity_limit;
    $return['free_shipping_cost_limit'] = $blog->free_shipping_cost_limit;
/////~

// affiliated product used
//    $return['images'][] = ($blog->is_affiliate ? $blog->affiliate_image : "");
    $return['affiliate']['is_affiliate'] = ($blog->is_affiliate ? $blog->is_affiliate : 0);
    $return['affiliate']['affiliate_product_id'] = ($blog->affiliate_product_id ? $blog->affiliate_product_id : 0);
    $return['affiliate']['affiliate_product_url'] = ($blog->affiliate_product_url ? $blog->affiliate_product_url : "");
    $return['affiliate']['is_archived'] = ($blog->is_archived ? $blog->is_archived : 0);
    $return['affiliate']['affiliate_syncon'] = ($blog->affiliate_syncon ? $blog->affiliate_syncon : 0);

    if ($return['affiliate']['is_affiliate'] == 1) {
        $return['images'][] = $blog->affiliate_image;
        $return['affiliate']['affiliate_image'] = $blog->affiliate_image;
    }

//~

    $return['sold_count'] = $blog->sold_count;
    $return['rate'] = $blog->rate;

    $owner = get_entity($blog->owner_guid);
    $return['product_seller']['user_id'] = $owner->guid;
    $return['product_seller']['user_name'] = $owner->username;
    $return['product_seller']['user_avatar_url'] = get_entity_icon_url($owner,'small');
    $return['product_seller']['is_seller'] = ($owner->is_seller == "true");
    $me = get_loggedin_user();
    if ($me) {
        $return['product_seller']['do_i_follow'] = user_is_friend($me->guid, $owner->guid);;
    } else {
        $return['product_seller']['do_i_follow'] = false;
    }
///// check if the product has already been liked
    $like = elgg_get_annotation_from_id($product_id);

    if (!$like) {
        $likes = elgg_get_annotations(array(
                'guid' => $product_id,
                'annotation_owner_guid' => elgg_get_logged_in_user_guid(),
                'annotation_name' => 'likes',
        ));
        $like = $likes[0];
    }
    $return['liked'] = ($like && $like->canEdit());
////// done like checking


    $return['likes_number'] = intval(likes_count(get_entity($product_id)));

    // get ideas linked to this product
    $items = $blog->getEntitiesFromRelationship("sponsor", true, 0, 0);
    $return['tips_number'] = count($items); //$blog->tips_number;

/*
    $options = array(
        'annotations_name' => 'product_comment',
            'guid' => $single->guid,
            'limit' => $limit,
            'pagination' => false,
            'reverse_order_by' => true,
               );
    $return['reviews_number'] = count(elgg_get_annotations($options));
*/

$comments = $blog->getAnnotations(
    'product_comment',    // The type of annotation
     0,   // The number to return
     0,  // Any indexing offset
    'asc'   // 'asc' or 'desc' (default 'asc')
);
    $return['reviews_number'] = count($comments);

//    $return['reviews_number'] = $blog->getAnnotationsSum('product_comment');
    
    $return['rate'] = $blog->rate;

    return $return;
}
    
expose_function('product.get_detail',
        "product_get_detail",
        array('product_id' => array ('type' => 'string'),
             ),
        "Read a product post",
        'GET',
        false,
        false);

/**
 * Web service to retrieve comments on a product post
 *
 * @param string $guid market product guid
 * @param string $limit    Number of users to return
 * @param string $offset   Indexing offset, if any
 *
 * @return array
 */
                    
function product_get_comments_by_id($product_id, $limit = 10, $offset = 0){
    $market = get_entity($product_id);
    $options = array(
        'annotations_name' => 'product_comment',
        'guid' => $product_id,
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
        $msg = elgg_echo('product_comment:none');
        throw new InvalidParameterException($msg);
    }
    return $return;
}
expose_function('product.get_comments_by_id',
    "product_get_comments_by_id",
    array('product_id' => array ('type' => 'string'),
          'limit' => array ('type' => 'int', 'required' => false, 'default' => 10),
          'offset' => array ('type' => 'int', 'required' => false, 'default' => 0),
         ),
    "Get comments for a market post",
    'GET',
    false,
    false);    

 /**
 * Web service to get tips list by the product
 *
 * @param int $product_id
 * @param int $limit  (optional) default 10
 * @param int $offset (optional) default 0
 *
 * @return array $file Array of files uploaded
 */

function product_get_tips_by_product($product_id, $limit = 10, $offset = 0) {

    $return = array();
    $product = get_entity($product_id);

    if (!elgg_instanceof($product, 'object', 'market')) {
        $return['content'] = elgg_echo('product:error:post_not_found');
        return $return;
    }

//    echo("product_id is $product_id <br>");
//    echo("product is $product_id <br>");
//    echo("product->tips is $product->tips <br>");

////////////
    // get ideas linked to this product
    $items = $product->getEntitiesFromRelationship("sponsor", true, $limit, $offset);
    $return['total_number'] = count($items);

    foreach ($items as $item) {
        $tip['tip_id'] = $item->guid;
        $tip['tip_title'] = $item->title;
        $tip['tip_thumbnail_image_url'] = $item->tip_thumbnail_image_url;
        $tip['tip_category'] = $item->ideascategory;

        $owner = get_entity($item->owner_guid);
        $tip['owner']['user_id'] = $owner->guid;
        $tip['owner']['user_name'] = $owner->username;
        $tip['owner']['user_avatar_url'] = get_entity_icon_url($owner,'small');
        
        $tip['likes_number'] = likes_count($item);
        $options = array(
                'annotations_name' => 'product_comment',
                'guid' => $item->guid,
                'limit' => 0,
                'pagination' => false,
                'reverse_order_by' => true,
                );
        $comments = elgg_get_annotations($options);
        $num_comments = count($comments);

        $tip['comments_number'] = $num_comments;
        $tip['products_number'] = $item->countEntitiesFromRelationship("sponsor", false);

        $tip['time_created'] = (int)$item->time_created;
        $return['tips'][] = $tip;
    }

    return $return;
}
expose_function('product.get_tips_by_product',
    "product_get_tips_by_product",
    array('product_id' => array ('type' => 'string'),
      'limit' => array ('type' => 'int', 'required' => false, 'default' => 10),
      'offset' => array ('type' => 'int', 'required' => false, 'default' => 0),
       ),
    "Read tips of a product",
    'GET',
    false,
    false);


 /**
 * Web service to get market products listed by the seller linked to the current product id
 *
 * @param int $limit  (optional) default 10
 * @param int $offset (optional) default 0
 * @param string $category(optional) eg. fashion, gadget, etc
 * @param string $product_id
 *
 * @return array list of products.
 */

function product_get_seller_other_posts($limit = 10, 
        $offset = 0, $category, $product_id) {

    $context = "user";
    $product_post = get_entity($product_id);
    if (!elgg_instanceof($product_post, 'object', 'market')) {
        throw new InvalidParameterException('blog:error:post_not_found');
    }
    $owner = get_entity($product_post->owner_guid);
    $seller_username = $owner->username;

    $return = product_get_posts($context, $limit, $offset, 0, 0, $category, $seller_username);

    return $return;

}


expose_function('product.get_seller_other_posts',
                "product_get_seller_other_posts",
                array(
                      'limit' => array ('type' => 'int', 'required' => false, 'default' => 10),
                      'offset' => array ('type' => 'int', 'required' => false, 'default' => 0),
                      'category' => array ('type' => 'string', 'required' => false, 'default' => 'all'),
                      'product_id' => array ('type' => 'string', 'required' => true),
                    ),
                "Get other product posts from the seller who sells product_id",
                'GET',
                false,
                false);

/**
 * Performs a search for market product
 *
 * @return array $results search result
 */
 
//function product_search($query, $category, $offset, $limit, 
//        $sort, $order, $search_type, $entity_type,
//        $entity_subtype, $owner_guid, $container_guid){
function product_search($query, $category, $offset, $limit, 
        $sort, $order, $search_type, $entity_type,
        $entity_subtype){

    $return = "";

    $params = array(
                    'query' => $query,
                    'offset' => $offset,
                    'limit' => $limit,
                    'sort' => $sort,
                    'order' => $order,
                    'search_type' => $search_type,
                    'type' => $entity_type,
                    'subtype' => $entity_subtype,
//                    'owner_guid' => $owner_guid,
//                    'container_guid' => $container_guid,
                    );
    $type = $entity_type;
    $results = elgg_trigger_plugin_hook('search', $type, $params, array());
    if ($results === FALSE) {
        throw new InvalidParameterException("search engine returns error");
    }
    $return['total_number'] = count($results['entities']); //$results['count'];
//    if($results['count']){
    if($results['entities']){
        foreach($results['entities'] as $single){
            if (1) {
                $blog['product_id'] = $single->guid;
                $options = array(
                        'annotations_name' => 'product_comment',
                        'guid' => $single->guid,
                        'limit' => 0,
                        'pagination' => false,
                        'reverse_order_by' => true,
                        );

                 $comments = elgg_get_annotations($options);
                 $num_comments = count($comments);

                 $blog['product_name'] = $single->title;
                 $blog['product_price'] = floatval($single->price);

                 $items = $single->getEntitiesFromRelationship("sponsor", true, 0, 0);
                 $blog['tips_number'] = count($items);

                 $blog['sold_count'] = $single->sold_count;
                 $blog['product_category'] = $single->marketcategory;
//               $blog['product_image'] = elgg_normalize_url("market/image/".$single->guid."/1/"."large/");
                 $blog['product_image'] = elgg_get_config('cdn_link').'/market/image/'.$single->guid.'/1/'.'large/';

                 $blog['likes_number'] = likes_count(get_entity($single->guid));
                 $blog['reviews_number'] = $num_comments;

                 $owner = get_entity($single->owner_guid);
                 $blog['product_seller']['user_id'] = $owner->guid;
                 $blog['product_seller']['user_name'] = $owner->username;
                 $blog['product_seller']['user_avatar_url'] = get_entity_icon_url($owner,'small');
                 $blog['product_seller']['is_seller'] = $owner->is_seller;
 
                 $blog['affiliate']['is_affiliate'] = ($single->is_affiliate ? $single->is_affiliate : 0);
                 $blog['affiliate']['affiliate_product_id'] = ($single->affiliate_product_id ? $single->affiliate_product_id : 0);
                 $blog['affiliate']['affiliate_product_url'] = ($single->affiliate_product_url ? $single->affiliate_product_url : "");
                 $blog['affiliate']['is_archived'] = ($single->is_archived ? $single->is_archived : 0);
                 $blog['affiliate']['affiliate_syncon'] = ($single->affiliate_syncon ? $single->affiliate_syncon : 0);
//                 $blog['product_image'] = ($single->is_affiliate ? $single->affiliate_image : "");

                 if (($blog['affiliate']['is_affiliate'] == 1) && ($single->affiliate_image != "")) {
                     $blog['product_image'] = $single->affiliate_image;
                 }
           
                 $return['products'][] = $blog;
            }
        }
    }

    return $return;
}
expose_function('product.search',
                "product_search",
                array(  'query' => array('type' => 'string'),
                        'category' => array('type' => 'string', 'required'=>false, 'default' => 'all'),
                        'offset' =>array('type' => 'int', 'required'=>false, 'default' => 0),
                        'limit' =>array('type' => 'int', 'required'=>false, 'default' => 10),
                        'sort' =>array('type' => 'string', 'required'=>false, 'default' => 'relevance'),
                        'order' =>array('type' => 'string', 'required'=>false, 'default' => 'desc'),
                        'search_type' =>array('type' => 'string', 'required'=>false, 'default' => 'all'),
                        'entity_type' =>array('type' => 'string', 'required'=>false, 'default' => "object"),
                        'entity_subtype' =>array('type' => 'string', 'required'=>false, 'default' => "market"),
//                        'owner_guid' =>array('type' => 'int', 'required'=>false, 'default' => ELGG_ENTITIES_ANY_VALUE),
//                        'container_guid' =>array('type' => 'int', 'required'=>false, 'default' => ELGG_ENTITIES_ANY_VALUE),
                        ),
                "Perform a search for products",
                'GET',
                true,
                false);

function product_post($product_id, $title, $category, $description,
    $price, $tags, $quantity, $delivery_time, $shipping_fee,
    $free_shipping_quantity_limit, $free_shipping_cost_limit,
    $is_affiliate, $affiliate_product_id, $affiliate_product_url,
    $is_archived, $affiliate_syncon, $affiliate_image)
{

    $user = elgg_get_logged_in_user_entity();

    // edit or create a new entity
    if ($product_id) {
        $entity = get_entity($product_id);
        if (elgg_instanceof($entity, 'object', 'market') && $entity->canEdit()) {
            $post = $entity;
        } else {
            register_error(elgg_echo('market:error:post_not_found'));
            throw new InvalidParameterException("market:error:post_not_found");
        }
    } else {
        $post = new ElggObject();
        $post->subtype = 'market';
        $new_post = true;
    }

    $values = array(
        'title' => $title, //htmlspecialchars($title, ENT_QUOTES, 'UTF-8'),
        'marketcategory' => $category,
        'market_type' => 'sell',
        'custom' => '',
        'description' => $description,
        'price' => $price,
        'sold_count' => 0,
        'access_id' => ACCESS_PUBLIC,
        'tags' => string_to_tag_array($tags),
        'tips_number' => 0,
        'quantity' => $quantity,
        'delivery_time' => $delivery_time,
        'shipping_fee' => $shipping_fee,
        'free_shipping_quantity_limit' => $free_shipping_quantity_limit,
        'free_shipping_cost_limit' => $free_shipping_cost_limit,
        'rate' => 0,
        'is_affiliate' => $is_affiliate,
        'affiliate_product_id' => $affiliate_product_id,
        'affiliate_product_url' => $affiliate_product_url,
        'is_archived' => $is_archived,
        'affiliate_syncon' => $affiliate_syncon,
        'affiliate_image' => $affiliate_image,
    );

    // fail if a required entity isn't set
    $required = array('title', 'marketcategory', 'description', 'price');

    // load from POST and do sanity and access checking

    foreach ($values as $name => $value) {
        if (in_array($name, $required) && empty($value)) {
            $error = elgg_echo("market:error:missing:$name");
            // Because seller portal check this already. This usually means uploading timeout or image files too large
            throw new InvalidParameterException("The total size of the images exceeds the limit. Please try to reduce the number of images posted at one time, and then you can use view/edit to add more images. You can also reduce the size of each image so that you can upload all of them at one time.");
//            throw new InvalidParameterException("missing:$name");
        }
       $post->$name = $value;
    }
    elgg_load_library('market');

    if ($post->save()) {
	$product_id = $post->guid;
        $values['product_id'] = $product_id;

        // remove sticky form entries
        elgg_clear_sticky_form('market');

        system_message(elgg_echo('market:posted'));

        // add to river if changing status or published, regardless of new post
        // because we remove it for drafts.
        if ($new_post) {
            add_to_river('river/object/market/create','create', $user->guid, $post->guid);
        }

        // Image 1 upload
        if ((isset($_FILES['upload1']['name'])) && (substr_count($_FILES['upload1']['type'],'image/'))) {
            $imgdata1 = get_uploaded_file('upload1');
            market_add_image($post, $imgdata1, 1);
//	    $values['images'][] = elgg_normalize_url("market/image/".$product_id."/1/"."large/");
            $values['images'][] = elgg_get_config('cdn_link').'/market/image/'.$product_id.'/1/'.'large/';
        } else {
            $values['images'][] = "";
	}
        // Image 2 upload
        if ((isset($_FILES['upload2']['name'])) && (substr_count($_FILES['upload2']['type'],'image/'))) {
            $imgdata2 = get_uploaded_file('upload2');
            market_add_image($post, $imgdata2, 2);
//	    $values['images'][] = elgg_normalize_url("market/image/".$product_id."/2/"."large/");
            $values['images'][] = elgg_get_config('cdn_link').'/market/image/'.$product_id.'/2/'.'large/';
        } else {
            $values['images'][] = "";
	}        
        // Image 3 upload
        if ((isset($_FILES['upload3']['name'])) && (substr_count($_FILES['upload3']['type'],'image/'))) {
            $imgdata3 = get_uploaded_file('upload3');
            market_add_image($post, $imgdata3, 3);
//	    $values['images'][] = elgg_normalize_url("market/image/".$product_id."/3/"."large/");
            $values['images'][] = elgg_get_config('cdn_link').'/market/image/'.$product_id.'/3/'.'large/';
        } else {
            $values['images'][] = "";
	}        
        // Image 4 upload
        if ((isset($_FILES['upload4']['name'])) && (substr_count($_FILES['upload4']['type'],'image/'))) {
            $imgdata4 = get_uploaded_file('upload4');
            market_add_image($post, $imgdata4, 4);
//	    $values['images'][] = elgg_normalize_url("market/image/".$product_id."/4/"."large/");
            $values['images'][] = elgg_get_config('cdn_link').'/market/image/'.$product_id.'/4/'.'large/';
        } else {
            $values['images'][] = "";
	}        

        //  affliate image upload
        if($is_affiliate) {
	    $values['images'][] = $affiliate_image;
        }

    } else {
            register_error(elgg_echo('market:error:cannot_save'));
            throw new InvalidParameterException("cannot_save");
    }
    return $values;
}

expose_function('product.post',
                "product_post",
                array( 'product_id' => array('type' => 'int', 'required' => false, 'default' => 0),
                       'title' => array('type' => 'string', 'required' => false, 'default' => ''),
                       'category' => array('type' => 'string', 'required' => false, 'default' => ''),
                       'description' => array('type' => 'string', 'required' => false, 'default' => ''),
                       'price' => array('type' => 'float', 'required' => false, 'default' => ''),
                       'tags' => array('type' => 'string', 'required' => false, 'default' => ''),
                       'quantity' => array('type' => 'int', 'required' => false, 'default' => 0),
                       'delivery_time' => array('type' => 'string', 'required' => false, 'default' => ""),
                       'shipping_fee' => array('type' => 'float', 'required' => false, 'default' => 0),
                       'free_shipping_quantity_limit' => array('type' => 'int', 'required' => false, 'default' => 0),
                       'free_shipping_cost_limit' => array('type' => 'int', 'required' => false, 'default' => 0),

                       'is_affiliate' => array('type' => 'int', 'required' => false, 'default' => 0),
                       'affiliate_product_id' => array('type' => 'int', 'required' => false, 'default' => 0),
                       'affiliate_product_url' => array('type' => 'string', 'required' => false, 'default' => ""),
                       'is_archived' => array('type' => 'int', 'required' => false, 'default' => 0),
                       'affiliate_syncon' => array('type' => 'int', 'required' => false, 'default' => 0),
                       'affiliate_image' => array('type' => 'string', 'required' => false, 'default' => ""),
                     ),
                "Post a product by seller",
                "POST",
                true,
                true);

// this is to delete product id from its linked tips.
// this is required when deleting a product
function delete_product_from_linked_tips($product_id)
{
        // Delete the product id from the linked tips
	// 1. string to array
	$ret_msg['tips'] = $post->tips;
	// 2. get tip obj
        $tip_id_array = explode(",", $post->tips);
        foreach ($tip_id_array as $tip_id) {
            $entity = get_entity($tip_id);
            if (elgg_instanceof($entity, 'object', 'ideas') && $entity->canEdit()) {
                $post = $entity;
            } else {
                register_error(elgg_echo('ideas:error:post_not_found'));
                throw new InvalidParameterException("ideas:error:post_not_found");
            }
        }


	// 3. get message json
	// 4. find product id from the products[]
	// 5. delete the product item, including id and title
	


        // Delete the market post

}
function delete_tip_from_a_product($product_id, $tip_id)
{
}
expose_function('product.delete_tip_from_a_product',
                "product_delete_tip_from_a_product",
                array( 'product_id' => array('type' => 'int', 'required' => true, 'default' => 0),
                     ),
                "Delete a product of seller",
                "POST",
                true,
                true);

function product_delete($product_id)
{
    // Make sure we're logged in
    if (!elgg_is_logged_in()) {
        register_error(elgg_echo("market:notdeleted"));
    }

    // Make sure we actually have permission to edit
    $post = get_entity($product_id);
    $ret_msg = "";

    if (!$post) {
        $msg = elgg_echo('market:notdeleted');
        throw new InvalidParameterException($msg);
    }
    if ($post->getSubtype() == "market" && $post->canEdit()) {
        elgg_load_library('market');

        $return = market_delete_post($post);
        if ($return) {
            system_message(elgg_echo("market:deleted"));
            $ret_msg = "product deleted";
        } else {
                // Error message
            register_error(elgg_echo("market:notdeleted"));
            $msg = elgg_echo('market:notdeleted');
            throw new InvalidParameterException($msg);
        }
    } else {
        $msg = elgg_echo('market:notdeleted');
        throw new InvalidParameterException($msg);
    }
    return $ret_msg;
}

expose_function('product.delete',
                "product_delete",
                array( 'product_id' => array('type' => 'int', 'required' => true, 'default' => 0),
                     ),
                "Delete a product of seller",
                "POST",
                true,
                true);


function product_image_delete($product_id, $image_id)
{
    // Make sure we're logged in
    if (!elgg_is_logged_in()) {
        register_error(elgg_echo("market:notdeleted"));
    }

    $post = get_entity($product_id);
    $ret_msg = "";

    // Make sure we actually have permission to edit
    $post = get_entity($product_id);
    $ret_msg = "";

/////
/*
    $user = elgg_get_logged_in_user_entity();
    $ret_msg['user'] = $user->username;
    $ret_msg['canedit'] = $post->canEdit();

    $owner = get_entity($post->owner_guid);
    $ret_msg['owner'] = $owner->username;
    return $ret_msg;
*/
////~
    if ($post->getSubtype() == "market" && $post->canEdit()) {
        elgg_load_library('market');
        // Delete the market post
        $return = market_delete_image($post, $image_id);
        if ($return) {
            $ret_msg = "image deleted";
        } else {
            $msg = "image not deleted";
            throw new InvalidParameterException($msg);
        }
    } else {
        $msg = "image can't be deleted";
        throw new InvalidParameterException($msg);
    }
    return $ret_msg;
}

expose_function('product.image.delete',
                "product_image_delete",
                array( 'product_id' => array('type' => 'int', 'required' => true, 'default' => 0),
                       'image_id' => array('type' => 'int', 'required' => true, 'default' => 0),
                     ),
                "Delete an image of a product",
                "POST",
                true,
                true);

function product_tip_delete($tip_id, $product_id)
{
    // Make sure we're logged in
    if (!elgg_is_logged_in()) {
        register_error(elgg_echo("market:notdeleted"));
    }

    $post = get_entity($product_id);
    $ret_msg = "";

    // Make sure we actually have permission to edit
    if ($post->getSubtype() == "market" && $post->canEdit()) {

        // Check if the tip_id belongs to a valid tip
        $tip = get_entity($tip_id);
        if (!elgg_instanceof($tip, 'object', 'ideas')) {
            throw new InvalidParameterException('ideas:error:post_not_found');
        }
        $tip->removeRelationship($product_id, 'sponsor');
        $ret_msg['message'] = "tip $tip_id was removed from product $product_id";

        // get current ideas linked to this product
        $items = $post->getEntitiesFromRelationship("sponsor", true, 0, 0);
        foreach ($items as $item) {
            $idea['id'] = $item->guid;
            $idea['name'] = $item->tip_title;
            $ret_msg['ideas'][] = $idea;
        }

    } else {
        throw new InvalidParameterException('market:error:post_not_found_or_not_editable');
    }
    return $ret_msg;
}

expose_function('product.tip.delete',
                "product_tip_delete",
                array( 'tip_id' => array('type' => 'int', 'required' => true, 'default' => 0),
                       'product_id' => array('type' => 'int', 'required' => true, 'default' => 0),
                     ),
                "Delete a tip linked to a product",
                "POST",
                true,
                true);

function get_affiliate_sync_time($product_id) {
    $post = get_entity($product_id);
    $return['affiliate_syncon'] = 0;

    if (!elgg_instanceof($post, 'object', 'market')) {
        throw new InvalidParameterException('blog:error:post_not_found');
    }
    if ($post->affiliate_syncon) {
        $return['affiliate_syncon'] = $post->affiliate_syncon;
    }
    return $return;
}

expose_function('product.get_affiliate_sync_time',
                "get_affiliate_sync_time",
                array( 'product_id' => array('type' => 'int', 'required' => true, 'default' => 0),
                     ),
                "Get affiliate product's last sync time",
                "GET",
                true,
                true);

function affiliate_archive($product_id) {
    $post = get_entity($product_id);

    if (!elgg_instanceof($post, 'object', 'market')) {
        throw new InvalidParameterException('blog:error:product_not_found');
    }
    if(!$post->canEdit()) {
        throw new InvalidParameterException('blog:error:cannot_edit');
    }
    $post->is_archived = 1;
    if (!$post->save()) {
        throw new InvalidParameterException("blog:error:cannot_save");
    }
    $return['product_id'] = $product_id;
    $return['is_archived'] = $post->is_archived;
    return $return;
}

expose_function('product.affiliate_archive',
                "affiliate_archive",
                array( 'product_id' => array('type' => 'int', 'required' => true, 'default' => 0),
                     ),
                "Archive the affiliate product.",
                "POST",
                true,
                true);

function recommend_set($product_id, $is_recommend) {
    $post = get_entity($product_id);

    if (!elgg_instanceof($post, 'object', 'market')) {
        throw new InvalidParameterException('blog:error:product_not_found');
    }
    $user = elgg_get_logged_in_user_entity();

/*
    if (!($user &&  $user->is_admin)) {
       throw new RegistrationException(elgg_echo('Only logged-in admin can recommend products'));
    }
*/
    if(!$post->canEdit()) {
        throw new InvalidParameterException('blog:error:cannot_edit');
    }
    $post->is_recommend = $is_recommend;
    if (!$post->save()) {
        throw new InvalidParameterException("blog:error:cannot_save");
    }
    $return['product_id'] = $product_id;
    $return['is_recommend'] = $post->is_recommend;

    return $return;
}

expose_function('product.recommend_set',
                "recommend_set",
                array( 'product_id' => array('type' => 'int', 'required' => true, 'default' => 0),
                       'is_recommend' => array('type' => 'int', 'required' => false, 'default' => 1),
                     ),
                "Recommend/unrecommend a product.",
                "POST",
                true,
                true);

function recommend_list($category, $offset, $limit) {


    if ($category == "all") {
        $options = array(
                    'offset' => $offset,
                    'limit' => $limit,
                    'types' => 'object',
                    'subtypes' => 'market',
                    'metadata_name_value_pairs' => array(
                        array(
                            'name' => 'is_recommend',
                            'value' => 1,
                        ),
                     )
                );
    } else {
        $options = array(
                    'offset' => $offset,
                    'limit' => $limit,
                    'types' => 'object',
                    'subtypes' => 'market',
                    'metadata_name_value_pairs' => array(
                        array(
                            'name' => 'is_recommend',
                            'value' => 1,
                        ),
                        array(
                            'name' => 'marketcategory',
                            'value' => $category,
                            'case_sensitive' => false
                        ),
                     )
                );
    }
    $recommended_products = elgg_get_entities_from_metadata($options);

    $return['total_number'] = count($recommended_products);

        foreach($recommended_products as $single){
                $blog['product_id'] = $single->guid;
                $options = array(
                        'annotations_name' => 'product_comment',
                        'guid' => $single->guid,
                        'limit' => 0,
                        'pagination' => false,
                        'reverse_order_by' => true,
                        );

                 $comments = elgg_get_annotations($options);
                 $num_comments = count($comments);

                 $blog['product_name'] = $single->title;
                 $blog['product_price'] = floatval($single->price);

                 $items = $single->getEntitiesFromRelationship("sponsor", true, 0, 0);
                 $blog['tips_number'] = count($items);

                 $blog['sold_count'] = $single->sold_count;
                 $blog['product_category'] = $single->marketcategory;
                 $blog['shipping_fee'] = $single->shipping_fee;
                 $blog['free_shipping_quantity_limit'] = $single->free_shipping_quantity_limit;
                 $blog['free_shipping_cost_limit'] = $single->free_shipping_cost_limit;
                 if ($single->quantity < 0) {
                     $blog['quantity'] = 0;
                 } else {
                     $blog['quantity'] = $single->quantity;
		 }
                 $blog['rate'] = $single->rate;

//               $blog['product_image'] = elgg_normalize_url("market/image/".$single->guid."/1/"."large/");
                 $blog['product_image'] = elgg_get_config('cdn_link').'/market/image/'.$single->guid.'/1/'.'large/';

                 $blog['likes_number'] = likes_count(get_entity($single->guid));
                 $blog['reviews_number'] = $num_comments;

                 $owner = get_entity($single->owner_guid);
                 $blog['product_seller']['user_id'] = $owner->guid;
                 $blog['product_seller']['user_name'] = $owner->username;
                 $blog['product_seller']['user_avatar_url'] = get_entity_icon_url($owner,'small');
                 $blog['product_seller']['is_seller'] = $owner->is_seller;
 
                 $blog['affiliate']['is_affiliate'] = ($single->is_affiliate ? $single->is_affiliate : 0);
                 $blog['affiliate']['affiliate_product_id'] = ($single->affiliate_product_id ? $single->affiliate_product_id : 0);
                 $blog['affiliate']['affiliate_product_url'] = ($single->affiliate_product_url ? $single->affiliate_product_url : "");
                 $blog['affiliate']['is_archived'] = ($single->is_archived ? $single->is_archived : 0);
                 $blog['affiliate']['affiliate_syncon'] = ($single->affiliate_syncon ? $single->affiliate_syncon : 0);

                 if (($blog['affiliate']['is_affiliate'] == 1) && ($single->affiliate_image != "")) {
                     $blog['product_image'] = $single->affiliate_image;
                 }
/*
                 if ($single->is_affiliate) {
                     $blog['product_image'] = $single->affiliate_image;
                 }
*/
                 $blog['is_recommend'] = $single->is_recommend;
           
                 $return['products'][] = $blog;
        }
    return $return;
}

expose_function('product.recommend_list',
                "recommend_list",
                array( 
                        'category' => array('type' => 'string', 'required'=>false, 'default' => 'all'),
                        'offset' =>array('type' => 'int', 'required'=>false, 'default' => 0),
                        'limit' =>array('type' => 'int', 'required'=>false, 'default' => 10),
                     ),
                "List recommended products.",
                "GET",
                false,
                false);
