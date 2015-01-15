<?php
/**
 * Elgg Webservices plugin 
 * 
 * @package Webservice
 * @author Saket Saurabh
 *
 */

/**
 * Web service to get profile labels
 *
 * @return string $profile_labels Array of profile labels
 */
function user_get_profile_fields() {    
    $user_fields = elgg_get_config('profile_fields');
    foreach ($user_fields as $key => $type) {
        $profile_labels[$key]['label'] = elgg_echo('profile:'.$key);
        $profile_labels[$key]['type'] = $type;
    }
    return $profile_labels;
}
    
expose_function('user.get_profile_fields',
                "user_get_profile_fields",
                array(),
                "Get user profile labels",
                'GET',
                false,
                false);

/**
 * Web service to get profile information
 *
 * @param string $username username to get profile information
 *
 * @return string $profile_info Array containin 'core', 'profile_fields' and 'avatar_url'
 */
function user_get_profile($username) {
    //if $username is not provided then try and get the loggedin user
    $me = null;
    if(!$username){
        $user = get_loggedin_user();
    } else {
        $user = get_user_by_username($username);
        $me = get_loggedin_user();
    }
    
    if (!$user) {
        throw new InvalidParameterException('registration:usernamenotvalid');
    }
    
    $user_fields = elgg_get_config('profile_fields');
    
    foreach ($user_fields as $key => $type) {
        if($user->$key){
            $profile_fields[$key]['label'] = elgg_echo('profile:'.$key);
//            $profile_fields[$key]['type'] = $type;
            if(is_array($user->$key)){
            $profile_fields[$key]['value'] = $user->$key;

            } else {
            $profile_fields[$key]['value'] = strip_tags($user->$key);
            }
        }
    }

    $params = array(
            'types' => 'object',
            'subtypes' => 'ideas',
            'owner_guid' => $user->guid,
            'limit' => 0,
            'full_view' => FALSE,
            'offset' => $offset,
	    );
    $ideas_num = count(elgg_get_entities($params));
    $profile_info['ideas_num'] = $ideas_num;
////////
/*
    $options = array(
        'annotation_names' => array('likes'),
        'annotation_owner_guids' => array($entity->guid),
        'order_by' => 'maxtime DESC',
        'full_view' => false,
    );
    $content = elgg_list_entities_from_annotations($options);
    if (!$content) {
        $content = elgg_echo('liked_content:noresults');
    }
*/
////////~

// add liked items
   
    $dbprefix = elgg_get_config('dbprefix');
    $likes_metastring = get_metastring_id('likes');

    $options = array(
        'annotation_names' => array('likes'),
        'annotation_owner_guids' => array($user->guid),
        'order_by' => 'maxtime DESC',
        'full_view' => false,
    );
    $content = elgg_list_entities_from_annotations($options);

    if (!$content) {
        $content = elgg_echo('liked_content:noresults');
    }
    $title = elgg_echo('liked_content:liked_content');
    $layout = elgg_view_layout('content', array(
             'title' => elgg_view_title($title),
             'content' => $content,
	     'filter' => elgg_view('liked_content/navigation/filter'),
    ));
    $likes_items = json_decode(elgg_view_page($title, $layout), true);
//~

/* old get likes_number    
    $objs_array = explode(",", $user->liked_products);
    $liked_products_count = count ($objs_array);
        $objs_array = explode(",", $user->liked_ideas);
    $liked_ideas_count = count ($objs_array);
    $profile_info['likes_number'] = $liked_products_count + $liked_ideas_count;
*/

// new likes_number
    $profile_info['likes_number'] = count($likes_items['object']['market']) + 
            count($likes_items['object']['ideas']);
//~

//    $profile_info['likes_total'] = $user->countAnnotations('likes');
//    $profile_info['likes_total'] = $user->getAnnotationsSum('likes');

    $friends = get_user_friends($user->guid, '' , 0, 0);
    $follower_number = 0;
    if($friends){
        $following_number = count($friends);
    }
    $profile_info['following_number'] = $following_number;

    $friends = get_user_friends_of($user->guid, '' , 0, 0);
    $follower_number = 0;
    if($friends) {
        $follower_number = count($friends);
    }
    $profile_info['follower_number'] = $follower_number;
    
    $options = array(
                'annotations_name' => 'ideas_comment',
                'annotation_owner_guids' => array($user->guid),
                'limit' => 0,
                'pagination' => false,
                'reverse_order_by' => true,
                );
    $comments = elgg_get_annotations($options);
    $num_comments = count($comments);
    $profile_info['comments_number'] = $num_comments;

    $options = array(
                'annotations_name' => 'product_comment',
                'annotation_owner_guids' => array($user->guid),
                'limit' => 0,
                'pagination' => false,
                'reverse_order_by' => true,
                );
    $comments = elgg_get_annotations($options);
    $num_comments = count($comments);
    $profile_info['reviews_number'] = $num_comments;


    $profile_info['user_id'] = $user->guid;
    $profile_info['name'] = $user->name;
    $profile_info['username'] = $user->username;
    $profile_info['profile_fields'] = $profile_fields;
    $profile_info['avatar_url'] = get_entity_icon_url($user,'medium');

    $profile_info['do_i_follow'] = user_is_friend($me->guid, $user->guid);

    return $profile_info;
}

expose_function('user.get_profile',
                "user_get_profile",
                array('username' => array ('type' => 'string', 'required' => false)
                    ),
                "Get user profile information",
                'GET',
                false,
                false);
/**
 * Web service to update profile information
 *
 * @param string $username username to update profile information
 *
 * @return bool 
 */
function user_save_profile($username, $profile) {
    if(!$username){
        $user = get_loggedin_user();
    } else {
        $user = get_user_by_username($username);
    }
    if (!$user) {
        throw new InvalidParameterException('registration:usernamenotvalid');
    }
    $owner = get_entity($user->guid);
    $profile_fields = elgg_get_config('profile_fields');
    foreach ($profile_fields as $shortname => $valuetype) {
        $value = $profile[$shortname];
        $value = html_entity_decode($value, ENT_COMPAT, 'UTF-8');

        if ($valuetype != 'longtext' && elgg_strlen($value) > 250) {
            $error = elgg_echo('profile:field_too_long', array(elgg_echo("profile:{$shortname}")));
            return $error;
        }

        if ($valuetype == 'tags') {
            $value = string_to_tag_array($value);
        }
        $input[$shortname] = $value;
    }
    
    $name = strip_tags($profile['name']);
    if ($name) {
        if (elgg_strlen($name) > 50) {
            return elgg_echo('user:name:fail');
        } elseif ($owner->name != $name) {
            $owner->name = $name;
            return $owner->save();
            if (!$owner->save()) {
                return elgg_echo('user:name:fail');
            }
        }
    }
    
    if (sizeof($input) > 0) {
        foreach ($input as $shortname => $value) {
            $options = array(
                'guid' => $owner->guid,
                'metadata_name' => $shortname
            );
            elgg_delete_metadata($options);
            
            if (isset($accesslevel[$shortname])) {
                $access_id = (int) $accesslevel[$shortname];
            } else {
                // this should never be executed since the access level should always be set
                $access_id = ACCESS_DEFAULT;
            }
            
            if (is_array($value)) {
                $i = 0;
                foreach ($value as $interval) {
                    $i++;
                    $multiple = ($i > 1) ? TRUE : FALSE;
                    create_metadata($owner->guid, $shortname, $interval, 'text', $owner->guid, $access_id, $multiple);
                }
                
            } else {
                create_metadata($owner->guid, $shortname, $value, 'text', $owner->guid, $access_id);
            }
        }
        
    }
    
    return "Success";
}
    
expose_function('user.save_profile',
                "user_save_profile",
                array('username' => array ('type' => 'string'),
                     'profile' => array ('type' => 'array', 'required' => false),
                    ),
                "Get user profile information with username",
                'POST',
                true,
                false);

/**
 * Web service to get all users registered with an email ID
 *
 * @param string $email Email ID to check for
 *
 * @return string $foundusers Array of usernames registered with this email ID
 */
function user_get_user_by_email($email) {
    if (!validate_email_address($email)) {
        throw new RegistrationException(elgg_echo('registration:notemail'));
    }

    $user = get_user_by_email($email);
    if (!$user) {
        throw new InvalidParameterException('registration:emailnotvalid');
    }
    foreach ($user as $key => $singleuser) {
        $foundusers[$key] = $singleuser->username;
    }
    return $foundusers;
}

expose_function('user.get_user_by_email',
                "user_get_user_by_email",
                array('email' => array ('type' => 'string'),
                    ),
                "Get Username by email",
                'GET',
                false,
                false);

/**
 * Web service to check availability of username
 *
 * @param string $username Username to check for availaility 
 *
 * @return bool
 */           
function user_check_username_availability($username) {
    $user = get_user_by_username($username);
    if (!$user) {
        return true;
    } else {
        return false;
    }
}

expose_function('user.check_username_availability',
                "user_check_username_availability",
                array('username' => array ('type' => 'string'),
                    ),
                "Get Username by email",
                'GET',
                false,
                false);


////////////// register new user by email and name only
//
//////////////

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function user_register_email($email, $msg="", $name="") {

    $email = trim($email);
    $msg = trim($msg);

    $is_new_user = 0;
    $username = substr($email, 0, strpos($email, '@'));
    if (strlen($username) < 4) {
        $username = str_replace("@", "_at_", $email);
    }
    if (strlen($username) < 4) {
        throw new RegistrationException(elgg_echo('registration:emailtooshort'));
    }
    $password = generateRandomString(20);
    $user_array = get_user_by_email($email);

    if (!$user_array) { // when email is not registered, check if username can be used.
        $is_new_user = 1;
        $user_array = get_user_by_username($username);
        if ($user_array) { // if username is already used, change username
            $username = $username."_".generateRandomString(10);
        }
        if ($name == "") {
            $name = $username;
        }
        $user_guid = register_user($username, $password, $name, $email);
        if(!user_guid) {
            throw new RegistrationException(elgg_echo('registration:usercannotregister'));
        }
    } else {
        $user = $user_array[0];
        $name = $user->name;
        $user_guid = $user->guid;
    }
    $owner = get_entity($user_guid);
    if ($is_new_user) {
        login($owner);
        $owner->email_subscriber = true;
        if(!$owner->save()) {
            throw new RegistrationException(elgg_echo('registration:usercannotsave'));
        }
        logout();
    }

    if ($msg != "") {
        // create a post with the current user's msg
        login($owner);
	$obj = new ElggObject();
	$obj->subtype = "new_user_email";
	$obj->owner_guid = $owner->guid;
	$obj->access_id = ACCESS_PUBLIC;
	$obj->method = "api";
	$obj->description = $msg;
//	$obj->title = elgg_substr(strip_tags($title), 0, 140);
	$obj->status = 'published';
//	$obj->comments_on = 'On';
//	$obj->excerpt = strip_tags($excerpt);
//	$obj->tags = strip_tags($tags);

	$guid = $obj->save();
	add_to_river('river/object/blog/create',
   	    'create',
	    $owner->guid,
	    $obj->guid
	);
        $return['msg_guid'] = $guid;
        logout();
    }
    $return['success'] = true;
    $return['is_new_user'] = $is_new_user;
    $return['guid'] = $user_guid;
    $return['username'] = $username;
    $return['email'] = $email;
    $return['name'] = $name;
    return $return;
}

expose_function('user.register.email',
                "user_register_email",
                array(
                        'email' => array ('type' => 'string'),
                        'msg' => array ('type' => 'string', 'required' =>false),
                        'name' => array ('type' => 'string', 'required' =>false),
                    ),
                "Register user by email only",
                'POST',
                false,
                false);

/**
 * Web service to register user
 *
 * @param string $name     Display name 
 * @param string $email    Email ID 
 * @param string $username Username
 * @param string $password Password 
 *
 * @return bool
 */           
function user_register($name="", $email="", $username="", $password="") {

    $username = trim($username);
    $email = trim($email);
    $name = trim($name);

    $user = get_user_by_username($username);
    if ($name == "") {
        $name = $username;
    }

    if (!$user) {
        $return['success'] = true;
        $return['guid'] = register_user($username, $password, $name, $email);
    } else {
        throw new RegistrationException(elgg_echo('registration:userexists'));
    }

    $return['username'] = $username;
    $return['email'] = $email;
    $return['name'] = $name;
    $return['profile_fields'] = user_get_profile_fields();
    $return['token'] = create_user_token($username, 527040);

    return $return;
}

expose_function('user.register',
                "user_register",
                array('name' => array ('type' => 'string', 'required' => false, 'default' => ""),
                        'email' => array ('type' => 'string'),
                        'username' => array ('type' => 'string'),
                        'password' => array ('type' => 'string'),
                    ),
                "Register user",
                'POST',
                false,
                false);

/**
 * Web service to add as friend
 *
 * @param string $username Username
 * @param string $friend Username to be added as friend
 *
 * @return bool
 */           
function user_friend_add($friend, $username) {
    if(!$username){
        $user = get_loggedin_user();
    } else {
        $user = get_user_by_username($username);
    }
    if (!$user) {
        throw new InvalidParameterException('registration:usernamenotvalid');
    }
    
    $friend_user = get_user_by_username($friend);
    if (!$friend_user) {
        $msg = elgg_echo("friends:add:failure", array($friend_user->name));
         throw new InvalidParameterException($msg);
    }
    
    if($friend_user->isFriendOf($user->guid)) {
        $msg = elgg_echo('friends:alreadyadded', array($friend_user->name));
         throw new InvalidParameterException($msg);
    }
    
    
    if ($user->addFriend($friend_user->guid)) {
        // add to river
        add_to_river('river/relationship/friend/create', 'friend', $user->guid, $friend_user->guid);
        $return['success'] = true;
        $return['message'] = elgg_echo('friends:add:successful' , array($friend_user->name));
    } else {
        $msg = elgg_echo("friends:add:failure", array($friend_user->name));
         throw new InvalidParameterException($msg);
    }
    return $return;
}

expose_function('user.friend.follow',
                "user_friend_add",
                array(
                        'friend' => array ('type' => 'string'),
                        'username' => array ('type' => 'string', 'required' =>false),
                    ),
                "Add a user as friend",
                'POST',
                true,
                false);    
                

/**
 * Web service to remove friend
 *
 * @param string $username Username
 * @param string $friend Username to be removed from friend
 *
 * @return bool
 */           
function user_friend_remove($friend,$username) {
    if(!$username){
        $user = get_loggedin_user();
    } else {
        $user = get_user_by_username($username);
    }
    if (!$user) {
         throw new InvalidParameterException('registration:usernamenotvalid');
    }
    
    $friend_user = get_user_by_username($friend);
    if (!$friend_user) {
        $msg = elgg_echo("friends:remove:failure", array($friend_user->name));
        throw new InvalidParameterException($msg);
    }
    
    if(!$friend_user->isFriendOf($user->guid)) {
        $msg = elgg_echo("friends:remove:notfriend", array($friend_user->name));
        throw new InvalidParameterException($msg);
    }
    
    
    if ($user->removeFriend($friend_user->guid)) {
        $return['message'] = elgg_echo("friends:remove:successful", array($friend->name));
        $return['success'] = true;
    } else {
        $msg = elgg_echo("friends:remove:failure", array($friend_user->name));
         throw new InvalidParameterException($msg);
    }
    return $return;
}

expose_function('user.friend.unfollow',
                "user_friend_remove",
                array(
                        'friend' => array ('type' => 'string'),
                        'username' => array ('type' => 'string', 'required' => false),
                    ),
                "Remove friend",
                'POST',
                true,
                true);                
                
/**
 * Web service to get friends of a user
 *
 * @param string $username Username
 * @param string $limit    Number of users to return
 * @param string $offset   Indexing offset, if any
 *
 * @return array
 */           
function user_get_friends($username, $limit = 10, $offset = 0) {
    if($username){
        $user = get_user_by_username($username);
    } else {
        $user = get_loggedin_user();
    }
    if (!$user) {
        throw new InvalidParameterException(elgg_echo('registration:usernamenotvalid'));
    }
    $friends = get_user_friends_of($user->guid, '' , $limit, $offset);
    
    if($friends){
        $return['total_number'] = count($friends);
        $return['offset'] = $offset;
        foreach($friends as $single) {
            $friend['guid'] = $single->guid;
            $friend['username'] = $single->username;
            $friend['name'] = $single->name;
            $friend['is_seller'] = $single->is_seller;
            $friend['avatar_url'] = get_entity_icon_url($single,'small');
            $friend['do_i_follow'] = user_is_friend($user->guid, $single->guid);
            $return['follower'][] = $friend;
        }
    } else {
        $msg = elgg_echo('friends:none');
        throw new InvalidParameterException($msg);
    }
    return $return;
}

expose_function('user.friend.get_follower',
                "user_get_friends",
                array('username' => array ('type' => 'string', 'required' => false),
                        'limit' => array ('type' => 'int', 'required' => false),
                        'offset' => array ('type' => 'int', 'required' => false),
                    ),
                "friend get follower",
                'GET',
                false,
                false);    
                
/**
 * Web service to obtains the people who have made a given user a friend
 *
 * @param string $username Username
 * @param string $limit    Number of users to return
 * @param string $offset   Indexing offset, if any
 *
 * @return array
 */           
function user_get_friends_of($username, $limit = 10, $offset = 0) {
    if(!$username){
        $user = get_loggedin_user();
    } else {
        $user = get_user_by_username($username);
    }
    if (!$user) {
        throw new InvalidParameterException(elgg_echo('registration:usernamenotvalid'));
    }
    $friends = get_user_friends($user->guid, '' , $limit, $offset);

    if($friends) {
        $return['total_number'] = count($friends);
        $return['offset'] = $offset;
        foreach($friends as $single) {
            $friend['guid'] = $single->guid;
            $friend['username'] = $single->username;
            $friend['name'] = $single->name;
            $friend['is_seller'] = $single->is_seller;
            $friend['avatar_url'] = get_entity_icon_url($single,'small');
     	    $return['following'][] = $friend;
        }
    } else {
        $msg = elgg_echo('friends:none');
        throw new InvalidParameterException($msg);
//        $return['error']['message'] = elgg_echo('friends:none');
    }
    return $return;
}

expose_function('user.friend.get_following', //friends_of',
                "user_get_friends_of",
                array('username' => array ('type' => 'string', 'required' => false),
                        'limit' => array ('type' => 'int', 'required' => false),
                        'offset' => array ('type' => 'int', 'required' => false),
                    ),
                "Get following friends of current user",
                'GET',
                false,
                false);    
                

/**
 * Web service to retrieve the messageboard for a user
 *
 * @param string $username Username
 * @param string $limit    Number of users to return
 * @param string $offset   Indexing offset, if any
 *
 * @return array
 */                    
function user_get_messageboard($limit = 10, $offset = 0, $username){
    if(!$username){
        $user = get_loggedin_user();
    } else {
        $user = get_user_by_username($username);
        if (!$user) {
            throw new InvalidParameterException('registration:usernamenotvalid');
        }
    }
    
$options = array(
    'annotations_name' => 'messageboard',
    'guid' => $user->guid,
    'limit' => $limit,
    'pagination' => false,
    'reverse_order_by' => true,
);

    $messageboard = elgg_get_annotations($options);
    
    if($messageboard){
    foreach($messageboard as $single){
        $post['id'] = $single->id;
        $post['description'] = $single->value;
        
        $owner = get_entity($single->owner_guid);
        $post['owner']['guid'] = $owner->guid;
        $post['owner']['name'] = $owner->name;
        $post['owner']['username'] = $owner->username;
        $post['owner']['avatar_url'] = get_entity_icon_url($owner,'small');
        
        $post['time_created'] = (int)$single->time_created;
        $return[] = $post;
    }
} else {
        $msg = elgg_echo('messageboard:none');
        throw new InvalidParameterException($msg);
    }
     return $return;
}
expose_function('user.get_messageboard',
                "user_get_messageboard",
                array(
                        'limit' => array ('type' => 'int', 'required' => false, 'default' => 10),
                        'offset' => array ('type' => 'int', 'required' => false, 'default' => 0),
                        'username' => array ('type' => 'string', 'required' => false),
                    ),
                "Get a users messageboard",
                'GET',
                false,
                false);    
/**
 * Web service to post to a messageboard
 *
 * @param string $text
 * @param string $to - username
 * @param string $from - username
 *
 * @return array
 */                    
function user_post_messageboard($text, $to, $from){
    if(!$to){
        $to_user = get_loggedin_user();
    } else {
        $to_user = get_user_by_username($to);
        if (!$to_user) {
            throw new InvalidParameterException('registration:usernamenotvalid');
        }
    }
    if(!$from){
        $from_user = get_loggedin_user();
    } else {
        $from_user = get_user_by_username($from);
        if (!$from_user) {
            throw new InvalidParameterException('registration:usernamenotvalid');
        }
    }
    
    $result = messageboard_add($from_user, $to_user, $text, 2);

    if($result){
        $return['success']['message'] = elgg_echo('messageboard:posted');
    } else {
        $return['error']['message'] = elgg_echo('messageboard:failure');
    }
    return $return;
}
expose_function('user.post_messageboard',
                "user_post_messageboard",
                array(
                        'text' => array ('type' => 'string'),
                        'to' => array ('type' => 'string', 'required' => false),
                        'from' => array ('type' => 'string', 'required' => false),
                    ),
                "Post a messageboard post",
                'POST',
                true,
                true);
///////////////////////////// Upload avatar
/**
 * Web service to upload an image for user profile avatar
 *
 * @param string $display_name
 *
 * @return array
 */                    
function user_upload_avatar($display_name)
{
    $owner = elgg_get_logged_in_user_entity();
    $guid = $owner->guid;
    if ($display_name) {
        $owner->name = $display_name;
	$owner->save();
    }
    if (!$owner || !($owner instanceof ElggUser) || !$owner->canEdit()) {
        register_error(elgg_echo('avatar:upload:fail'));
        throw new InvalidParameterException("cannot_upload_avatar1");
    }

    if ($_FILES['avatar']['error'] != 0) {
	register_error(elgg_echo('avatar:upload:fail'));
        throw new InvalidParameterException("cannot_upload_avatar2");
    }

    $icon_sizes = elgg_get_config('icon_sizes');

    // get the images and save their file handlers into an array
    // so we can do clean up if one fails.
    $files = array();
    foreach ($icon_sizes as $name => $size_info) {

        $resized = get_resized_image_from_uploaded_file('avatar', 
            $size_info['w'], $size_info['h'], $size_info['square'], 
            $size_info['upscale']);

        if ($resized) {
            //@todo Make these actual entities.  See exts #348.
            $file = new ElggFile();
            $file->owner_guid = $guid;
            $file->setFilename("profile/{$guid}{$name}.jpg");
            $file->open('write');
            $file->write($resized);
            $file->close();
            $files[] = $file;

        } else {
            // cleanup on fail
            foreach ($files as $file) {
                $file->delete();
            }
            register_error(elgg_echo('avatar:resize:fail'));
            throw new InvalidParameterException("cannot_upload_avatar3");
	}
    }

    // reset crop coordinates
    $owner->x1 = 0;
    $owner->x2 = 0;
    $owner->y1 = 0;
    $owner->y2 = 0;

    $owner->icontime = time();
    if (elgg_trigger_event('profileiconupdate', $owner->type, $owner)) {
        system_message(elgg_echo("avatar:upload:success"));
        $return = "avatar:upload:success";

        $view = 'river/user/default/profileiconupdate';
        elgg_delete_river(array('subject_guid' => $owner->guid, 'view' => $view));
        add_to_river($view, 'update', $owner->guid, $owner->guid);
    }
    return $return;
}
expose_function('user.upload_avatar',
                "user_upload_avatar",
                array(
                        'display_name' => array ('type' => 'string', 'required' => false, 'default' => null),
                    ),
                "Upload Avatar picture",
                'POST',
                true,
                true);

/////////////////////////////

///////////////////////////// Upload avatar
/**
 * Profile Edit action
 *
 * @param string $profile_str
 *
 * @return array
 */                    
/**
 * wrapper for recursive array walk decoding
 */
function profile_array_decoder2(&$v) {
        $v = _elgg_html_decode($v);
}

function user_edit_profile($profile_str)
{
    $owner = elgg_get_logged_in_user_entity();
    $guid = $owner->guid;

    if (!$owner || !($owner instanceof ElggUser) || !$owner->canEdit()) {
        register_error(elgg_echo('profile:edit:fail'));
        throw new InvalidParameterException("cannot_upload_avatar");
    }

    $json = json_decode($profile_str, true);

    // grab the defined profile field names and their load the values from POST json.
    // each field can have its own access, so sort that too.
    $input = array();
    $accesslevel = $json['accesslevel'];

    if (!is_array($accesslevel)) {
        $accesslevel = array();
    }

    $profile_fields = elgg_get_config('profile_fields');
    foreach ($profile_fields as $shortname => $valuetype) {
        // the decoding is a stop gap to prevent &amp;&amp; showing up in profile fields
        // because it is escaped on both input (get_input()) and output (view:output/text). see #561 and #1405.
        // must decode in utf8 or string corruption occurs. see #1567.
        $value = $json[$shortname];

        $return[$shortname] = $value;

        if (is_array($value)) {
                array_walk_recursive($value, 'profile_array_decoder2');
        } else {
                $value = _elgg_html_decode($value);
        }
        // limit to reasonable sizes
        // @todo - throwing away changes due to this is dumb!
        if (!is_array($value) && $valuetype != 'longtext' && elgg_strlen($value) > 250) {
                $error = elgg_echo('profile:field_too_long', array(elgg_echo("profile:{$shortname}")));
                register_error($error);
                throw new InvalidParameterException("profile_data_invalid");
        }

        if ($value && $valuetype == 'url' && !preg_match('~^https?\://~i', $value)) {
                $value = "http://$value";
        }

        if ($valuetype == 'tags') {
                $value = string_to_tag_array($value);
        }

        $input[$shortname] = $value;
    }

    // display name is handled separately
    $name = strip_tags(get_input('name'));
    if ($name) {
        if (elgg_strlen($name) > 50) {
                register_error(elgg_echo('user:name:fail'));
        } elseif ($owner->name != $name) {
                $owner->name = $name;
                $owner->save();
        }
    }

    // go through custom fields
    if (sizeof($input) > 0) {
        foreach ($input as $shortname => $value) {
                $options = array(
                        'guid' => $owner->guid,
                        'metadata_name' => $shortname,
                        'limit' => false
                );
                elgg_delete_metadata($options);
                if (!is_null($value) && ($value !== '')) {
                        // only create metadata for non empty values (0 is allowed) to prevent metadata records with empty string values #4858

                        if (isset($accesslevel[$shortname])) {
                                $access_id = (int) $accesslevel[$shortname];
                        } else {
                                // this should never be executed since the access level should always be set
                                $access_id = ACCESS_DEFAULT;
                        }
                        if (is_array($value)) {
                                $i = 0;
                                foreach ($value as $interval) {
                                        $i++;
                                        $multiple = ($i > 1) ? TRUE : FALSE;
                                        create_metadata($owner->guid, $shortname, $interval, 'text', $owner->guid, $access_id, $multiple);
                                }
                        } else {
                                create_metadata($owner->getGUID(), $shortname, $value, 'text', $owner->getGUID(), $access_id);
                        }
                }
        }

        $owner->save();

        $return['input'] = $input;
        $return['accesslevel'] = $accesslevel;

        // Notify of profile update
        elgg_trigger_event('profileupdate', $owner->type, $owner);

        elgg_clear_sticky_form('profile:edit');
        system_message(elgg_echo("profile:saved"));
    }
    return $return;
}

expose_function('user.edit_profile',
                "user_edit_profile",
                array(
                        'profile' => array ('type' => 'string', 'required' => true, 'default' => null),
                    ),
                "Edit user profile",
                'POST',
                true,
                true);

/**
 * Web service to logout
 *
 * @return 0
 */
function user_logout() {
    logout();
    return 0;
}

expose_function('user.logout',
                "user_logout",
                array(),
                "Log out the current user",
                'POST',
                true,
                true);

function user_change_username($new_username) {
    if (!user_check_username_availability($new_username)) {
        throw new RegistrationException(elgg_echo('change_username:usernameexists'));
    }
    if(!$new_username){
        throw new InvalidParameterException('change_username:usernamenotvalid');
    }
    $user = get_loggedin_user();

    $user->username = $new_username;
    if (!$user->save()) {
        throw new InvalidParameterException('change_username:cannotsave');
    }
    return "username changed";
}
expose_function('user.change_username',
                "user_change_username",
                array(
                        'new_username' => array ('type' => 'string', 'required' => true, 'default' => null),
                    ),
                "Change username of the logged in user",
                'POST',
                true,
                true);

/////

/**
 * Web service to request lost password
 *
 * @return true/false
 */
function user_request_lost_password($username) {

    // allow email addresses
    if (strpos($username, '@') !== false && ($users = get_user_by_email($username))) {
        $username = $users[0]->username;
    }
    $user = get_user_by_username($username);
    if ($user) {
        if (send_new_password_request($user->guid)) {
                system_message(elgg_echo('user:password:resetreq:success'));
                $return['username'] = $user->username;
                $return['email_sent'] = $user->email;
                $return['message'] = elgg_echo('user:password:resetreq:success');
        } else {
                register_error(elgg_echo('user:password:resetreq:fail'));
                throw new InvalidParameterException(elgg_echo('user:password:resetreqfail'));
        }
    } else {
        register_error(elgg_echo('user:username:notfound', array($username)));
        throw new InvalidParameterException(elgg_echo('user:username:notfound'));
    }
    return $return;
}

expose_function('user.request_lost_password',
                "user_request_lost_password",
                array(
                        'username' => array ('type' => 'string', 'required' => true, 'default' => ""),
                ),
                "request lost password",
                'POST',
                true,
                false);



/**
 * Used to Pull in the latest avatar from facebook.
 *
 * @access public
 * @param array $user
 * @param string $file_location
 * @return void
 * Facility function and no API exposed
 */
function facebook_import_avatar($user, $file_location) {
    global $CONFIG;
    $tempfile=$CONFIG->dataroot.$user->getGUID().'img.jpg';
    $imgContent = file_get_contents($file_location);
    $fp = fopen($tempfile, "w");
    fwrite($fp, $imgContent);
    fclose($fp);
    $sizes = array(
        'topbar' => array(16, 16, TRUE),
        'tiny' => array(25, 25, TRUE),
        'small' => array(40, 40, TRUE),
        'medium' => array(100, 100, TRUE),
        'large' => array(200, 200, FALSE),
        'master' => array(550, 550, FALSE),
    );

    $filehandler = new ElggFile();
    $filehandler->owner_guid = $user->getGUID();
    foreach ($sizes as $size => $dimensions)
    {
        $image = get_resized_image_from_existing_file(
                $tempfile,
                $dimensions[0],
                $dimensions[1],
                $dimensions[2]
        );

        $filehandler->setFilename("profile/$user->guid$size.jpg");
        $filehandler->open('write');
        $filehandler->write($image);
        $filehandler->close();
    }

    // update user's icontime
    $user->icontime = time();
    return TRUE;
}

/**
 * Used to create user with facebook data
 *
 * @access public
 * @param array $fbData facebook data of user
 * @return void
 */
function user_register_facebook($msg) {
    $user = FALSE;

    $fbData = json_decode($msg, true);

    $facebook_users = elgg_get_entities_from_metadata(array(
                                                    'type' => 'user',
                                                    'metadata_name_value_pairs' => array(
                                                            'name' => 'facebook_uid',
                                                            'value' => $fbData['user_profile']['id'],
                                                    )
                                    ));
    if (is_array($facebook_users) && count($facebook_users) == 1) {
            // reuse existing account
        $user = $facebook_users[0];

        $username = $user->username; // $fbData['user_profile']['username'];
        $token = create_user_token($username, 527040);
        return $token;
    }

    // create new user
    if (!$user) {
        $email= $fbData['user_profile']['email'];
        $users= get_user_by_email($email);

        if(!$users) {
            // Elgg-ify facebook credentials
            if(!empty($fbData['user_profile']['username'])) {
                $username = $fbData['user_profile']['username'];
            } else {
                $username = str_replace(' ', '', strtolower($fbData['user_profile']['name']));
            }
            $usernameTmp =$username;
            while (get_user_by_username($username)) {
                $username = $usernameTmp . '_' . rand(1000, 9999);
            }
            $password = generate_random_cleartext_password();
            $name = $fbData['user_profile']['name'];
            $user = new ElggUser();
            $user->username = $username;
            $user->name = $name;
            $user->email = $email;
            $user->access_id = ACCESS_PUBLIC;
            $user->salt = generate_random_cleartext_password();
            $user->password = generate_user_password($user, $password);
            $user->owner_guid = 0;
            $user->container_guid = 0;
            $user->facebook_uid = $fbData['user_profile']['id'];
            if (!$user->save()) {
                register_error(elgg_echo('registerbad'));
                throw new RegistrationException(elgg_echo('registration:facebook:cannotsaveuser'));
            } else {
                // send mail to user
//                send_user_password_mail($email, $name, $username, $password);
                // pull in facebook icon
                $url = 'https://graph.facebook.com/' . $fbData['user_profile']['id'] .'/picture?type=large';
                    facebook_import_avatar($user, $url);
            }
        } else {
            // CL: if email is used before, then we don't copy facebook's data anymore. Sounds good ? XXX
            $user= $users[0];
            $token = create_user_token($user->username, 527040);
            return $token;
        }
    }
    $token = create_user_token($user->username, 527040);
    return $token;
}

expose_function('user.register.facebook',
                "user_register_facebook",
                array('msg' => array ('type' => 'string', 'required' => true),
                    ),
                "Registration using Facebook",
                'POST',
                true,
                false);
