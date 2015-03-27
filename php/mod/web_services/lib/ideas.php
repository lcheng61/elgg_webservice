<?php
/**
 * Elgg Webservices plugin 
 * ideas
 * 
 * @package Webservice
 * @author Liang Cheng
 *
 */
 
 /**
 * Web service to get tips list by all users
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

function ideas_get_posts($context,  $limit = 10, $offset = 0, $group_guid, $category, $username) {
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
            'subtypes' => 'ideas',
            'limit' => $limit,
            'full_view' => FALSE,
            'offset' => $offset,
            );
    }

    if($context == "mine" || $context ==  "user"){
        $params = array(
            'types' => 'object',
            'subtypes' => 'ideas',
            'owner_guid' => $user->guid,
            'limit' => $limit,
            'full_view' => FALSE,
            'offset' => $offset,
        );
    }
    if($context == "group"){
        $params = array(
            'types' => 'object',
            'subtypes' => 'ideas',
            'container_guid'=> $group_guid,
            'limit' => $limit,
            'full_view' => FALSE,
            'offset' => $offset,
        );
    }
    $latest_blogs = elgg_get_entities($params);
        
    if($context == "friends"){
        $latest_blogs = get_user_friends_objects($user->guid, 'ideas', $limit, $offset);
    }

    if($latest_blogs) {
        $return['category'] = $category;
        $return['offset'] = $offset;
        $display_ideas_number = 0;
    
        foreach($latest_blogs as $single ) {
            if (($single->ideascategory == $category) || 
                    ($category == "all")) {
                $blog['tip_id'] = $single->guid;
/*
                $options = array(
                        'annotations_name' => 'ideas_comment',
                        'guid' => $single->guid,
                        'limit' => $limit,
                        'pagination' => false,
                        'reverse_order_by' => true,
                        );

                 $comments = elgg_get_annotations($options);
                 $num_comments = count($comments);
*/
//                 $num_comments = $single->getAnnotationsSum('ideas_comment');
                 $comments = $single->getAnnotations(
                     'ideas_comment',    // The type of annotation
                     0,   // The number to return
                     0,  // Any indexing offset
                     'asc'   // 'asc' or 'desc' (default 'asc')
                 );
                 $num_comments = count($comments);


                 $display_ideas_number++;
                 $blog['tip_title'] = $single->title;
                 $blog['tip_category'] = $single->ideascategory;
//                 $blog['tip_thumbnail_image_url'] = $single->tip_thumbnail_image_url;
                 if (!$single->tip_thumbnail_image_url) {
                     $blog['tip_thumbnail_image_url'] = elgg_get_config('cdn_link').'/ideas/image/'.$single->guid."/"."0"."/"."large/";
                 } else {
                     $blog['tip_thumbnail_image_url'] = $single->tip_thumbnail_image_url;
                 }

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

                 $return['tips'][] = $blog;
            }
        }
        $return['total_number'] = $display_ideas_number;
    }
    else {
        $return['total_number'] = 0;
        $return['tips'] = "";
    }

    return $return;

}

expose_function('ideas.get_posts',
                "ideas_get_posts",
                array(
                      'context' => array ('type' => 'string', 'required' => false, 'default' => 'all'),
                      'limit' => array ('type' => 'int', 'required' => false, 'default' => 10),
                      'offset' => array ('type' => 'int', 'required' => false, 'default' => 0),
                      'group_guid' => array ('type'=> 'int', 'required'=>false, 'default' =>0),
                      'category' => array ('type' => 'string', 'required' => false, 'default' => 'all'),
                      'username' => array ('type' => 'string', 'required' => false),
                    ),
                "Get list of idea posts",
                'GET',
                false,
                false);

 /**
 * Web service to get idea detail
 *
 * @param int $tip_id
 * @param int $username (optional) default 0
 *
 * @return array $file Array of files uploaded
 */

function tip_get_detail($tip_id) {
    $return = array();

    $blog = get_entity($tip_id);

/*
    $options = array(
        'annotations_name' => 'ideas_comment',
        'guid' => $tip_id,
        'limit' => 10,
        'pagination' => false,
        'reverse_order_by' => true,
    );
    $comments = elgg_get_annotations($options);
    $num_comments = count($comments);
*/
//    $num_comments = $blog->getAnnotationsSum('ideas_comment');

                 $comments = $blog->getAnnotations(
                     'ideas_comment',    // The type of annotation
                     0,   // The number to return
                     0,  // Any indexing offset
                     'asc'   // 'asc' or 'desc' (default 'asc')
                 );
                 $num_comments = count($comments);


    if (!elgg_instanceof($blog, 'object', 'ideas')) {
        $return['content'] = elgg_echo('blog:error:post_not_found');
        return $return;
    }

    $return['tip_title'] = $blog->title;
    $return['tip_category'] = $blog->ideascategory;
    $return['tip_thumbnail_image_url'] = $blog->tip_thumbnail_image_url;
    $return['tip_id'] = $tip_id;

    $return['tip_notes'] = $blog->tip_notes;
    $return['tip_tags'] = $blog->tip_tags;

    $return['tip_pages'] =             json_decode($blog->tip_pages, true);

////// get products
    $return['products_number'] = $blog->countEntitiesFromRelationship("sponsor", false);
/*
    $items = $blog->getEntitiesFromRelationship("sponsor", false, 0, 0);
    foreach ($items as $item) {
        $product_info['id'] = $item->guid;
        $product_info['name'] = $item->title;
        $images = unserialize($item->images);
        $product_info['images'] = "";
        foreach ($images as $key => $value) {
            if ($value == 1) {
//              $product_info['images'][] = elgg_normalize_url("market/image/".$item->guid."/$key/"."large/");
                $product_info['images'][] = elgg_get_config('cdn_link').'/market/image/'.$item->guid."/".$key."/"."master/";
            } else {
	        $product_info['images'][] = "";
      	    }
        }
        if ($item->is_affiliate) {
            $product_info['images'][] = $item->affiliate_image;
        }
//        $return['products'][] = $product_info;
    } 
*/
//////~

    $owner = get_entity($blog->owner_guid);
    $return['tip_author']['user_id'] = $owner->guid;
    $return['tip_author']['user_name'] = $owner->username;
    $return['tip_author']['is_seller'] = $owner->is_seller;
    $return['tip_author']['user_avatar_url'] = get_entity_icon_url($owner,'small');

    $me = get_loggedin_user();
    if ($me) {
        $return['tip_author']['do_i_follow'] = user_is_friend($me->guid, $owner->guid);;
    } else {
        $return['tip_author']['do_i_follow'] = false;
    }


    $return['likes_number'] = likes_count(get_entity($tip_id));
    $return['comments_number'] = $num_comments;

//    $return['tip_tags'] = $blog->tags;

///// check if the product has already been liked
    $like = elgg_get_annotation_from_id($tip_id);
    if (!$like) {
        $likes = elgg_get_annotations(array(
                'guid' => $tip_id,
                'annotation_owner_guid' => elgg_get_logged_in_user_guid(),
                'annotation_name' => 'likes',
        ));
        $like = $likes[0];
    }
    $return['liked'] = ($like && $like->canEdit());
////// done like checking

    return $return;
}
    
expose_function('ideas.get_detail',
        "tip_get_detail",
        array('tip_id' => array ('type' => 'int'),
             ),
        "Read an idea post",
        'GET',
        false,
        false);

/**
 * Web service for posting a tip to ideas
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

function ideas_post_tip($message, $idea_id)
{
    $user = elgg_get_logged_in_user_entity();
    if (!$user) {
        throw new InvalidParameterException('registration:usernamenotvalid');
    }
    // edit or create a new entity
    if ($idea_id) {
        $entity = get_entity($idea_id);
        if (elgg_instanceof($entity, 'object', 'ideas') && $entity->canEdit()) {
            $post = $entity;
        } else {
            register_error(elgg_echo('ideas:error:post_not_found'));
            throw new InvalidParameterException("ideas:error:post_not_found");
        }
	$post->deleteRelationships("sponsor");
    } else {
        $post = new ElggObject();
        $post->subtype = 'ideas';
        if (!$post->save()) { // to get $post->guid
            throw new InvalidParameterException("cannot_save");
        }
        $idea_id = $post->guid;
        $new_post = true;
    }

    // save all info of this tip
    $json = json_decode($message, true);

    $post->tip_pages = json_encode($json['tip_pages']);

    $post->title = $json['tip_title'];
    $post->tip_thumbnail_image_url = $json['tip_thumbnail_image_url'];
    $post->ideascategory = $json['category'];

    $post->tip_notes = $json['tip_notes'];
    $post->tip_tags = $json['tip_tags'];

    $post->products = json_encode($json['products'], true);

//  $post->user_id = $json['tip_author']['user_id'];
//  $post->user_name = $json['tip_author']['user_name'];
//  $post->user_avatar_url = $json['tip_author']['user_avatar_url'];
//  $post->is_seller = $json['tip_author']['is_seller'];

    $post->likes_number = $json['likes_number'];
    $post->comments_number = $json['comments_number'];

    $post->access_id = ACCESS_PUBLIC;

//    $return['products_string'] = $post->products;

    $save_result = $post->save();
    if (!$save_result) {
        throw new InvalidParameterException("cannot_save");
    }

    $return['tip_title'] = $post->title;
    $return['tip_category'] = $post->ideascategory;
    $return['tip_thumbnail_image_url'] = $post->tip_thumbnail_image_url;

    // process camera/local file upload (tip_image_local)
    $page_num = 0;
    $image_num = 1;
    $idea_id = $post->guid;
    $img_item = array();


    elgg_load_library('ideas');

    if ($json['tip_image_local_cover'] == "true") {
        $file_name = "tip_image_local_cover";
        // upload image
        if ((isset($_FILES[$file_name]['name'])) && (substr_count($_FILES[$file_name]['type'],'image/'))) {
            $imgdata = get_uploaded_file($file_name);
	    ideas_add_image($post, $imgdata, "0");

                $image_link = elgg_get_config('cdn_link').'/ideas/image/'.$idea_id."/0/"."large/";
//                $image_link = elgg_normalize_url("ideas/image/".$idea_id."/"."0"."/"."large/");

            $json['tip_thumbnail_image_url'] = $image_link;
            $return['tip_thumbnail_image_url'] = $image_link;

            $post->tip_thumbnail_image_url = $json['tip_thumbnail_image_url'];
        }
        
    }

    foreach ($json['tip_pages'] as $page) {
        if ($page['tip_image_local']) {
            if ($page['tip_image_local'] == "true") {
                if ($image_num > 10) {
                    break;
                }
	        $file_name = "tip_image_local_".$image_num;

	        // upload image
	        if ((isset($_FILES[$file_name]['name'])) && (substr_count($_FILES[$file_name]['type'],'image/'))) {
	            $imgdata = get_uploaded_file($file_name);
		    ideas_add_image($post, $imgdata, $image_num);

//                  $image_link = elgg_normalize_url("ideas/image/".$idea_id."/".$image_num."/"."large/");
                    $image_link = elgg_get_config('cdn_link').'/ideas/image/'.$idea_id."/".$image_num."/"."large/";

                    $json['tip_pages'][$page_num]['tip_image_url'] = $image_link;
                    $img_item[] = $image_link;
	        }
	    } else {
                $img_item[] = "";
            }
        } else {
            $img_item[] = "";
	}
	$page_num ++;
        $image_num ++;
    }
    $post->tip_pages = json_encode($json['tip_pages']);
    $return['tip_pages'] = $post->tip_pages;
    if (!$post->save()) {
        throw new InvalidParameterException("cannot_save_with_local_images");
    }

    $return['local_images'] = $img_item;

    // process product relationship
    foreach ($json['products_id'] as $id) {
        $id = intval($id);
        $product_post = get_entity($id);
        if ($product_post) { // if the product id is a valid one

            $post->addRelationship($id, "sponsor");

            $return['products_id'][] = $id;
            $return['products_name'][] = $product_post->title;
        } else {
            throw new InvalidParameterException("product_post($id) doesn't exist");
        }
    }

    if ($post->save()) {
        if ($new_post) {
            if (!$user->points) {
                $user->points = 0; // signup points
            }
            $user->points += 0;
            add_to_river('river/object/market/create','create', $user->guid, $post->guid);
        }
    } else {
        register_error(elgg_echo('ideas:error:cannot_save'));
        throw new InvalidParameterException("cannot_save");
    }
    $return['idea_id'] = $idea_id;

    return $return;
}

expose_function('ideas.post_tip',
                "ideas_post_tip",
                array(
                        'message' => array ('type' => 'string', 'required' => true, 'default' => ''),
                        'idea_id' => array ('type' => 'int', 'required' => false, 'default' => 0),
                    ),
                "Post a list of tips",
                'POST',
                true,
                true);


function ideas_post_tip_old($title,
                    $tip_thumbnail_image_url,
                    $tip_pages,
                    $tip_video_url,
                    $tip_image_url,
                    $tip_image_caption,
                    $tip_text,
                    $tip_notes,
                    $tip_tags,
                    $tip_category,
                    $products,
                    $access) {

    $user = get_loggedin_user();
    if (!$user) {
        throw new InvalidParameterException('registration:usernamenotvalid');
    }
    
    $obj = new ElggObject();
    $obj->subtype = "ideas";
    $obj->owner_guid = $user->guid;
    $obj->access_id = strip_tags($access);
    $obj->method = "api";
    $obj->description = strip_tags($tip_text);
    $obj->title = elgg_substr(strip_tags($title), 0, 140);
    $obj->status = 'published';
    $obj->comments_on = 'On';

    $obj->tags = strip_tags($tip_tags);
    $obj->tip_thumbnail_image_url = strip_tags($tip_thumbnail_image_url);
    $obj->tip_pages =               strip_tags($tip_pages);
    $obj->tip_video_url =           strip_tags($tip_video_url);
    $obj->tip_image_url =           strip_tags($tip_image_url);
    $obj->tip_image_caption =       strip_tags($tip_image_caption);
    $obj->tip_text =                strip_tags($tip_text);
    $obj->tip_notes =               strip_tags($tip_notes);
    $obj->ideascategory =           strip_tags($tip_category);
    $obj->products =                strip_tags($products);

    // Parse products string to extract its individual product guid's.
    $product_id_array = explode(",", $obj->products);

//  echo ("obj->products is $obj->products\n");

    $obj->products_number = 0;
    foreach ($product_id_array as $id) {
        $id = intval($id);
//        echo ("product_id = $id\n");
        $product_post = get_entity($id);
        if ($product_post) { // if the product id is a valid one
//            echo ("$id is valid\n");
            // XXX: we assume the same product id can't be linked to the same tip.
            $product_post->tips_number ++;
            $product_post->tips .= "$guid,";
        }
        $obj->products_number ++;
    }

    $guid = $obj->save();
    add_to_river('river/object/ideas/create',
            'create',
            $user->guid,
            $obj->guid
    );

//    echo ("product_post->tips = $product_post->tips\n");
//    echo ("product_post->tips_number = $product_post->tips_number");
//    echo ("guid = $guid, product_post->tips = $product_post->tips\n");

    $return['success'] = true;
    $return['message'] = elgg_echo('ideas:message:saved');
    return $return;
} 
    
expose_function('ideas.post_tip_old',
                "ideas_post_tip_old",
                array(
                        'title' => array ('type' => 'string', 'required' => true),
                        'tip_thumbnail_image_url' => array ('type' => 'string', 'required' => false),
                        'tip_pages' => array ('type' => 'int', 'required' => false),
                        'tip_video_url' => array ('type' => 'string', 'required' => false),
                        'tip_image_url' => array ('type' => 'string', 'required' => false),
                        'tip_image_caption' => array ('type' => 'string', 'required' => false),
                        'tip_text' => array ('type' => 'string', 'required' => false),
                        'tip_notes' => array ('type' => 'string', 'required' => false),
                        'tip_tags' => array ('type' => 'string', 'required' => false, 'default' => "blog"),
                        'tip_category' => array ('type' => 'string', 'required' => false),
                        'products' => array ('type' => 'string', 'required' => false),
                        'access' => array ('type' => 'string', 'required' => false, 'default'=>ACCESS_PUBLIC),
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

function ideas_delete_tip($tip_id) {
    $return = array();

    // Make sure we're logged in
    if (!elgg_is_logged_in()) {
        register_error(elgg_echo("ideas:notdeleted"));
    }

    $blog = get_entity($tip_id);

    if (!elgg_instanceof($blog, 'object', 'ideas')) {
        throw new InvalidParameterException('blog:error:post_not_found1');
    }

    if($user->guid!=$blog->owner_guid) {
        $return['message'] = elgg_echo('blog:message:notauthorized');
    }
    // Extract the product ids from this tip and remove the current tip id from the corresponding products

    $return['products'] = $blog->products;
    $product_id_json = json_decode($blog->products, true);
    foreach ($product_id_json as $product) {
        $id = intval($product['product_id']);
        $product_post = get_entity($id);
        if ($product_post) { // if the product id is a valid one
            $return['under if'] = "ok";
            $pattern = "/$tip_id".",/";
            $replacement = "";
            $match_limit = 1;
            $match_count = 0;
            
            $product_post->tips = preg_replace($pattern, $replacement, $product_post->tips, $match_limit, $match_count);
            $return[$id] = $product_post->tips;
        }
    } // done with removing such tips from linked products

    // Remove the tip object itself.
    if (elgg_instanceof($blog, 'object', 'ideas') && $blog->canEdit()) {

        elgg_load_library('ideas');
        $return = ideas_delete_post($blog);
        if ($return) {
            system_message(elgg_echo("ideas:deleted"));
            $ret_msg = "product deleted";
        } else {
                // Error message
            register_error(elgg_echo("ideas:notdeleted"));
            $msg = elgg_echo('ideas:notdeleted');
            throw new InvalidParameterException($msg);
        }
/*
        if ($blog->delete()) {
            $return['success'] = true;
            $return['message'] = elgg_echo('blog:message:deleted_post');
        } else {
            $return['message'] = elgg_echo('blog:error:cannot_delete_post');
        }
*/
    } else {
        $return['message'] = elgg_echo('blog:error:user_cannot_delete_tip');
    }

    return $return;
}
    
expose_function('ideas.delete_tip',
                "ideas_delete_tip",
                array('tip_id' => array ('type' => 'string'),
                     ),
                "Delete a tip and remove it from associated products",
                'POST',
                true,
                false);


/**
 * Web service for list products linked by one tip
 *
 * @param string  $guid     GUID of a tip
 * @param integer $offset 
 * @param integer $number
 *
 * @return bool
 */

function ideas_get_products_by_tip($tip_id, $offset = 0, $limit = 10, $username) {
    $return = array();
    $tip_obj = get_entity($tip_id);
    if (!elgg_instanceof($tip_obj, 'object', 'ideas')) {
        throw new InvalidParameterException('blog:error:post_not_found1');
    }

    if(!$username) {
        $user = get_loggedin_user();
    } else {
        $user = get_user_by_username($username);
        if (!$user) {
            throw new InvalidParameterException('registration:usernamenotvalid');
	}
    }

    $return['tip_title'] = $tip_obj->title;

////// get products
    $return['products_number'] = $tip_obj->countEntitiesFromRelationship("sponsor", false);
    $items = $tip_obj->getEntitiesFromRelationship("sponsor", /*reverse_relation*/false, $limit, $offset);
    foreach ($items as $item) {
        $product_info['product_id'] = $item->guid;
        $product_info['product_name'] = $item->title;
        $product_info['product_price'] = floatval($item->price);
        $product_info['product_category'] = $item->marketcategory;

/*
        $options = array(
                'annotations_name' => 'ideas_comment',
                'guid' => $item->guid,
                'limit' => $limit,
                'pagination' => false,
                'reverse_order_by' => true,
        );
        $comments = elgg_get_annotations($options);
        $num_comments = count($comments);
*/
//        $num_comments = $item->getAnnotationsSum('product_comment');
                 $comments = $item->getAnnotations(
                     'product_comment',    // The type of annotation
                     0,   // The number to return
                     0,  // Any indexing offset
                     'asc'   // 'asc' or 'desc' (default 'asc')
                 );
                 $num_comments = count($comments);


        $product_info['tips_number'] = $item->tips_number;
        //XXX: hard-code sold_count;		 		 
//        $blog['sold_number'] = $item->sold_count;
        $product_info['likes_number'] = likes_count(get_entity($item->guid));
        $product_info['reviews_number'] = $num_comments;

        $owner = get_entity($item->owner_guid);
        $product_info['product_seller']['user_id'] = $owner->guid;
        $product_info['product_seller']['user_name'] = $owner->username;
        $product_info['product_seller']['user_avatar_url'] = get_entity_icon_url($owner,'small');

        $images = unserialize($item->images);
        $product_info['images'] = "";
        foreach ($images as $key => $value) {
            if ($value == 1) {
//              $product_info['images'][] =
                     // elgg_normalize_url("market/image/".$item->guid."/$key/"."large/");
                $product_info['images'][] =
                        elgg_get_config('cdn_link').'/market/image/'.$item->guid."/".$key."/"."large/";
            } else {
	        $product_info['images'][] = "";
      	    }
        }
        if ($item->is_affiliate) {
            $product_info['images'][] = $item->affiliate_image;
        }
        $return['products'][] = $product_info;
    }
    if ($products_number == 0) {
        $return['products'] = array();
    }
    return $return;
}
    
expose_function('ideas.get_products_by_tip',
                "ideas_get_products_by_tip",
                array('tip_id' => array ('type' => 'string', 'required' => true),
                      'offset' => array ('type' => 'integer', 'required' => false, 'default' => 0),
                      'limit' => array ('type' => 'integer', 'required' => false, 'default' => 10),
                      'username' => array ('type' => 'string', 'required' => false),
                     ),
                "List products associated to one tip.",
                'GET',
                true,
                false);


/**
 * Performs a search for ideas
 *
 * @return array $results search result
 */
 
function ideas_search($query, $category, $offset, $limit, 
        $sort, $order, $search_type, $entity_type,
        $entity_subtype, $owner_guid, $container_guid){
    
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
                    'owner_guid' => $owner_guid,
                    'container_guid' => $container_guid,
                    );
                    
    $type = $entity_type;
    $results = elgg_trigger_plugin_hook('search', $type, $params, array());
    if ($results === FALSE) {
        throw new InvalidParameterException("search engine returns error");
    }
    if($results['count']){
        foreach($results['entities'] as $single){
            if (($single->ideascategory == strtolower($category)) || 
                    (strtolower($category) == "all")) {
                $blog['tip_id'] = $single->guid;
/*
                $options = array(
                        'annotations_name' => 'ideas_comment',
                        'guid' => $single->guid,
                        'limit' => $limit,
                        'pagination' => false,
                        'reverse_order_by' => true,
                        );

                 $comments = elgg_get_annotations($options);
                 $num_comments = count($comments);
*/
//                 $num_comments = $single->getAnnotationsSum('ideas_comment');

                 $comments = $single->getAnnotations(
                     'ideas_comment',    // The type of annotation
                     0,   // The number to return
                     0,  // Any indexing offset
                     'asc'   // 'asc' or 'desc' (default 'asc')
                 );
                 $num_comments = count($comments);


                 $blog['tip_title'] = $single->title;
                 $blog['tip_category'] = $single->ideascategory;
//               $blog['tip_thumbnail_image_url'] = $single->tip_thumbnail_image_url;
                 if ($single->tip_thumbnail_image_url) {
                     $blog['tip_thumbnail_image_url'] = 
                             elgg_get_config('cdn_link').'/ideas/image/'.$single->guid."/"."0"."/"."large/";
                 } else {
                     $blog['tip_thumbnail_image_url'] = $single->tip_thumbnail_image_url;
      		 }

                 $blog['likes_number'] = likes_count(get_entity($single->guid));
                 $blog['comments_number'] = $num_comments;

                 $owner = get_entity($single->owner_guid);
                 $blog['tip_author']['user_id'] = $owner->guid;
                 $blog['tip_author']['user_name'] = $owner->username;
                 $blog['tip_author']['user_avatar_url'] = get_entity_icon_url($owner,'small');
                 $blog['tip_author']['is_seller'] = $owner->is_seller;

                 $blog['likes_number'] = likes_count(get_entity($single->guid));
                 $blog['comments_number'] = $num_comments;
                 $blog['products_number'] = $single->products;

                 $return['ideas'][] = $blog;
            }
        }
    }

    return $return;
}
expose_function('ideas.search',
                "ideas_search",
                array(  'query' => array('type' => 'string'),
                        'category' => array('type' => 'string', 'required'=>false, 'default' => 'all'),
                        'offset' =>array('type' => 'int', 'required'=>false, 'default' => 0),
                        'limit' =>array('type' => 'int', 'required'=>false, 'default' => 10),
                        'sort' =>array('type' => 'string', 'required'=>false, 'default' => 'relevance'),
                        'order' =>array('type' => 'string', 'required'=>false, 'default' => 'desc'),
                        'search_type' =>array('type' => 'string', 'required'=>false, 'default' => 'all'),
                        'entity_type' =>array('type' => 'string', 'required'=>false, 'default' => "object"),
                        'entity_subtype' =>array('type' => 'string', 'required'=>false, 'default' => "ideas"),
                        'owner_guid' =>array('type' => 'int', 'required'=>false, 'default' => ELGG_ENTITIES_ANY_VALUE),
                        'container_guid' =>array('type' => 'int', 'required'=>false, 'default' => ELGG_ENTITIES_ANY_VALUE),
                        ),
                "Perform a search for ideass",
                'GET',
                true,
                false);



