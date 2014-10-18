<?php
/**
 * Elgg Webservices plugin 
 * market product
 * 
 * @package Webservice
 * @author Liang Cheng
 *
 */
 
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

function product_get_posts($context, $limit = 10, $offset = 0, $group_guid, $category, $username) {
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
//   $latest_blogs = elgg_list_entities_from_metadata($params);
    $latest_blogs = elgg_get_entities($params);
        
    if($context == "friends"){
        $latest_blogs = get_user_friends_objects($user->guid, 'market', $limit, $offset);
    }
    
    if($latest_blogs) {
        $return['total_number'] = count($latest_blogs);
        $return['category'] = $category;
        $return['offset'] = $offset;
                 
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
//              $blog['product_description'] = $single->description;
                 $blog['product_price'] = $single->price;
                 $blog['tips_number'] = $single->tips_number;
		 //XXX: hard-code sold_count;		 		 
                 $single->sold_count = 0;
                 $blog['sold_number'] = $single->sold_count;
                 $blog['product_category'] = $single->marketcategory;
//                $blog['market_type'] = $single->market_type;
//                $blog['custom'] = $single->custom;
//                $blog['product_tags'] = $single->tags;
                 $blog['product_image'] = elgg_normalize_url("market/image/".$single->guid."/1/"."large/");
                 $blog['likes_number'] = likes_count(get_entity($single->guid));
                 $blog['reviews_number'] = $num_comments;

                 $owner = get_entity($single->owner_guid);
                 $blog['product_seller']['user_id'] = $owner->guid;
//                $blog['product_seller']['name'] = $owner->name;
                 $blog['product_seller']['user_name'] = $owner->username;
                 $blog['product_seller']['user_avatar_url'] = get_entity_icon_url($owner,'small');
                 $blog['product_seller']['is_seller'] = $owner->is_seller;
            
//                $blog['container_guid'] = $single->container_guid;
//                $blog['access_id'] = $single->access_id;
//                $blog['time_created'] = (int)$single->time_created;
//                $blog['time_updated'] = (int)$single->time_updated;
//                $blog['last_action'] = (int)$single->last_action;
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


expose_function('product.get_posts',
                "product_get_posts",
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

    $return['title'] = htmlspecialchars($blog->title);
    $return['product_image'] = elgg_normalize_url("market/image/".$product_id."/1/"."large/");
    $return['content'] = strip_tags($blog->description);
    $return['product_price'] = $blog->price;
    $return['excerpt'] = $blog->excerpt;
    $return['product_description'] = $blog->description;

    $owner = get_entity($blog->owner_guid);
    $return['product_seller']['user_id'] = $owner->guid;
    $return['product_seller']['user_name'] = $owner->username;
    $return['product_seller']['user_avatar_url'] = get_entity_icon_url($owner,'small');
    $return['product_seller']['is_seller'] = $owner->is_seller;

    $return['likes_number'] = likes_count(get_entity($product_id));
    $return['tips_number'] = $blog->tips_number;
    $return['reviews_number'] = 0;
// to add
    $return['rating'] = 0;
    $return['rating_number'] = 0;

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
        'annotations_name' => 'generic_comment',
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
        $msg = elgg_echo('generic_comment:none');
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

    echo("product_id is $product_id <br>");
    echo("product is $product_id <br>");
    echo("product->tips is $product->tips <br>");


    if($product->tips){
        // Parse products string to extract its individual product guid's.
        $tip_id_array = explode(",", $product->tips);

        echo ("tip_id_array is $product->tips <br>");


        foreach ($tip_id_array as $id) {
            $id = intval($id);
            echo ("tip_id = $id <br>");
            $tip_obj = get_entity($id);

            if ($tip_obj) { // if the tip id is a valid one
                echo ("$id is valid <br>");
                // print tip details here
                
                $tip['tip_id'] = $id;
                $tip['tip_title'] = $tip_obj->title;
                $tip['tip_thumbnail_image_url'] = $tip_obj->tip_thumbnail_image_url;
                $tip['tip_category'] = $tip_obj->ideascategory;

                $owner = get_entity($tip_obj->owner_guid);

                $tip['owner']['user_id'] = $owner->guid;
                $tip['owner']['user_name'] = $owner->username;
                $tip['owner']['user_avatar_url'] = get_entity_icon_url($owner,'small');
                echo ("tip = $tip <br>");
        
                $tip['likes_number'] = likes_count(get_entity($id));

                $options = array(
                        'annotations_name' => 'generic_comment',
                        'guid' => $id,
                        'limit' => $limit,
                        'pagination' => false,
                        'reverse_order_by' => true,
                        );

                $comments = elgg_get_annotations($options);
                $num_comments = count($comments);

                $tip['comments_number'] = $num_comments;
                $tip['products_number'] = $tip_obj->products_number;
                $tip['time_created'] = (int)$tip_obj->time_created;
                $return[] = $tip;
            }
        }

    } else {
        $msg = elgg_echo('generic_comment:none');
        throw new InvalidParameterException($msg);
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
    $return = product_get_posts($context, $limit, $offset, 0, $category, $seller_username);

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
 
function product_search($query, $category, $offset, $limit, 
        $sort, $order, $search_type, $entity_type,
        $entity_subtype, $owner_guid, $container_guid){
    
    $params = array(
                    'query' => $query,
                    'offset' => $offset,
                    'limit' => $limit,
                    'sort' => $sort,
                    'order' => $order,
                    'search_type' => $search_type,
                    'type' => $entity_type,
                    'subtype' => $entity_subtype,
                    'owner_guid' => $owner_guid,
                    'container_guid' => $container_guid,
                    );
                    
    $type = $entity_type;
    $results = elgg_trigger_plugin_hook('search', $type, $params, array());
    if ($results === FALSE) {
        // search plugin returns error.
        continue;
    }
    if($results['count']){
        foreach($results['entities'] as $single){
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
                 $blog['product_price'] = $single->price;
                 $blog['tips_number'] = $single->tips_number;
                 $blog['sold_number'] = $single->sold_count;
                 $blog['product_category'] = $single->marketcategory;
                 $blog['product_image'] = elgg_normalize_url("market/image/".$single->guid."/1/"."large/");
                 $blog['likes_number'] = likes_count(get_entity($single->guid));
                 $blog['reviews_number'] = $num_comments;

                 $owner = get_entity($single->owner_guid);
                 $blog['product_seller']['user_id'] = $owner->guid;
                 $blog['product_seller']['user_name'] = $owner->username;
                 $blog['product_seller']['user_avatar_url'] = get_entity_icon_url($owner,'small');
                 $blog['product_seller']['is_seller'] = $owner->is_seller;
            
                 $return[] = $blog;
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
                        'owner_guid' =>array('type' => 'int', 'required'=>false, 'default' => ELGG_ENTITIES_ANY_VALUE),
                        'container_guid' =>array('type' => 'int', 'required'=>false, 'default' => ELGG_ENTITIES_ANY_VALUE),
                        ),
                "Perform a search for products",
                'GET',
                false,
                false);
