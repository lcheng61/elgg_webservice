<?php
/**
 * Elgg Webservices plugin 
 * 
 * @package Webservice
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

	    );
    $ideas_num = count(elgg_get_entities($params));
    $profile_info['ideas_num'] = $ideas_num;

    if (!$user->points) {
        $user->points = 0; // initial signup points
        $user->save();
    }
    $profile_info['points'] = intval($user->points);

////////Add message number
   $total_params = array(
            'type' => 'object',
            'subtype' => 'messages',
            'metadata_name' => 'toId',
            'metadata_value' => $user->guid,

//            'metadata_name' => 'readYet',
//            'metadata_value' => false,

            'owner_guid' => $user->guid,
            'limit' => 0,
            'offset' => 0,
            'full_view' => false,
                        );
    $total_list = elgg_get_entities_from_metadata($total_params);

    $msg_count = 0;
    if ($total_list) {
        foreach($total_list as $single ) {
            if($single->readYet == false){
                $msg_count ++;
            }
        }
    }
    $profile_info['message_unread_number'] = $msg_count; //count($total_list);

////////
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
    $profile_info['email'] = $user->email;
    $profile_info['profile_fields'] = $profile_fields;
    $profile_info['avatar_url'] = get_entity_icon_url($user,'large');

    if (!$user->is_seller) {
        $profile_info['is_seller'] = "false";
    } else {
        $profile_info['is_seller'] = $user->is_seller;
    }
    $params = array(
            'types' => 'object',
            'subtypes' => 'market',
            'owner_guid' => $user->guid,
            'limit' => 0,
            'full_view' => FALSE,
	    'count' => TRUE,
            );
    $products_number = elgg_get_entities($params);
    $profile_info['products_number'] = $products_number;


    if ($me && $user) {
        $profile_info['do_i_follow'] = user_is_friend($me->guid, $user->guid);
    } else {
        $profile_info['do_i_follow'] = false;
    }

    return $profile_info;
}

expose_function('user.get_profile',
                "user_get_profile",
                array('username' => array ('type' => 'string', 'required' => false, 'default' => '')
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
                     'profile' => array ('type' => 'array', 'required' => false, 'default' => ''),
                    ),
                "Get user profile information with username",
                'POST',
                true,
                false);


/**
 * Web service to get seller setting
 *
 */
function user_get_seller_setting($username) {    
    if(!$username) {
        $user = get_loggedin_user();
        if (!$user) {
            throw new InvalidParameterException('registration:usernamenotvalid');
        }
    } else {
        $user = get_user_by_username($username);
        if (!$user) {
            throw new InvalidParameterException('registration:usernamenotvalid');
	}
    }
    if (!$user->seller_setting) {
        return "";
    }
    return $user->seller_setting;
}

expose_function('user.get_seller_setting',
                "user_get_seller_setting",
                array(
                      'username' => array ('type' => 'string', 'required' => false, 'default' => ""),
                     ),
                "Get seller setting",
                'GET',
                true,
                false);

/**
 * Web service to set seller setting
 *
 */
function user_set_seller_setting($message) {
    $user = elgg_get_logged_in_user_entity();
    if (!$user) {
        throw new InvalidParameterException('registration:usernamenotvalid');
    }
    if (!$user->is_seller) {
        throw new InvalidParameterException('registration:notseller');
    }
    $json = json_decode($message, true);

    if (!$json['logo']) {
        $json['logo'] = get_entity_icon_url($user,'large');
    }
    
    login($user);
    $user->seller_setting = $message; //json_encode($json);
    if(!$user->save()) {
        throw new RegistrationException(elgg_echo('registration:usercannotsave'));
    }
    logout();
    return "success";
}
    
expose_function('user.set_seller_setting',
                "user_set_seller_setting",
                array(
                        'message' => array ('type' => 'string', 'required' => true, 'default' => ''),
                    ),
                "Set seller setting",
                'POST',
                true,
                true);


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
        return 1;
    } else {
        return 0;
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


function user_check_email_availability($email) {
    $user = get_user_by_email($email);
    if (!$user) {
        return 1;
    } else {
        $user = $user[0];
        if ($user->email_subscriber == true) {
            return 1;
        }
        return 0;
    }
}

expose_function('user.check_email_availability',
                "user_check_email_availability",
                array('email' => array ('type' => 'string'),
                    ),
                "Check email availability",
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
        user_send_subscriber_register_mail($email);
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
                        'email' => array ('type' => 'string', 'default' =>""),
                        'msg' => array ('type' => 'string', 'required' =>false, 'default' =>""),
                        'name' => array ('type' => 'string', 'required' =>false, 'default' =>""),
                    ),
                "Register user by email only",
                'POST',
                false,
                false);


function user_reset_password2($guid) {

    $user = get_entity($guid);

    if (($user instanceof ElggUser)) {
        $password = generate_random_cleartext_password();

         if (force_user_password_reset($user->guid, $password)) {
             system_message(elgg_echo('admin:user:resetpassword:yes'));

             $subject = "password reset";
             $body = "
                         Hi $user->username, \n
                         Your password was reset. New password: $password \n\n
                         Yours truly, \n
                         Lovebeauty Team
                     ";
             notify_user($user->guid,
                 elgg_get_site_entity()->guid,
                 $subject,
                 $body,
                 array(),
                 'email');
         } else {
             register_error(elgg_echo('admin:user:resetpassword:no'));
         }
    } else {
        register_error(elgg_echo('admin:user:resetpassword:no'));
    }
    return true;
}


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
function user_register($name="", $email="", $username="", $password="", $is_seller=0) {

    $username = trim($username);
    $email = trim($email);
    $name = trim($name);

    $user = get_user_by_username($username);
    if ($user) {
        throw new RegistrationException(elgg_echo('registration:userexists'));
    } else {
        $user = get_user_by_email($email);
        $user = $user[0];
    }
    if ($name == "") {
        $name = $username;
    }

    if (!$user) {
        $return['success'] = true;
        $return['guid'] = register_user($username, $password, $name, $email);
        if ($is_seller == 1) {
            user_send_seller_register_mail($email, $name, $username, $password);
        } else {
            user_send_register_mail($email, $name, $username, $password);
        }
        $return['email_sent'] = true;
        $user = get_user($return['guid']);

    } else {
        // must be user from email
        if ($user->email_subscriber == true) {

            $return['success'] = true;

            if ($is_seller == 0) {
                user_send_register_mail($email, $name, $username, $password);
            } else if ($is_seller == 1) {
                user_send_seller_register_mail($email, $name, $username, $password);
            }
            $return['email_sent'] = true;
            
            login($user);
            if (!force_user_password_reset($user->guid, $password)) {
                throw new RegistrationException("Password changing failed");
            }
            $user->username = $username;
            $user->name = $name;
            $user->email_subscriber = 0;  // convert from an email subscriber to a common user
            $user->save();
            logout();
        } else { // for common users, we don't user provided password. We send password reset
            $result = send_new_password_request2($user->guid);
            throw new RegistrationException("Note: Email exists. This was reported to $user->email ");
        }
    }

    $return['username'] = $username;
    $return['email'] = $email;
    $return['name'] = $name;
    $return['profile_fields'] = user_get_profile_fields();
    $return['token'] = create_user_token_same($username, 527040);

// add sign up points
    login($user);
    $user->points = 0;
    if (!$user->save()) {
        throw new InvalidParameterException('registration:cannotaddpoints');
    }

    return $return;
}

expose_function('user.register',
                "user_register",
                array('name' => array ('type' => 'string', 'required' => false, 'default' => ""),
                        'email' => array ('type' => 'string', 'required' => true, 'default' => ""),
                        'username' => array ('type' => 'string', 'required' => true, 'default' => ""),
                        'password' => array ('type' => 'string', 'required' => true, 'default' => ""),
                        'is_seller' => array ('type' => integer, 'required' => false, 'default' => 0),
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
//        $msg = elgg_echo('friends:alreadyadded', array($friend_user->name));
        $msg = "friends:alreadyadded";
         throw new InvalidParameterException($msg);
    }
    
    
    if ($user->addFriend($friend_user->guid)) {
        // add to river
        add_to_river('river/relationship/friend/create', 'friend', $user->guid, $friend_user->guid);
        $return['success'] = true;
//        $return['message'] = elgg_echo('friends:add:successful' , array($friend_user->name));
        $return['message'] = "friends:add:successful";
        // send email
/*
        $subject = "$user->name followed you.";
        $email = "$user->name followed you.";
//        $ret = message_send_one($subject, "", $friend, 0);
//        $return['email_sent'] = $ret;

        $return['email_sent'] = notify_user($friend_user->guid, elgg_get_site_entity()->guid,
            $subject, $email, array(), 'email');
*/
        // send push notification
        $push_msg  = "$user->name followed you.";
        $push_link = "lovebeauty://follow?username=$username";
        $ret = push_notification($push_msg, $push_link, $friend_user->guid);
        $return['notification_sent'] = "true";
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
//        $return['message'] = elgg_echo("friends:remove:successful", array($friend->name));
        $return['message'] = "friends:remove:successful";
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
function user_get_friends($limit = 10, $offset = 0, $username = "") {
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
            $friend['avatar_url'] = get_entity_icon_url($single,'large');
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
                array(
                        'limit' => array ('type' => 'int', 'required' => false),
                        'offset' => array ('type' => 'int', 'required' => false),
			'username' => array ('type' => 'string', 'required' => false),
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
function user_get_friends_of($limit = 10, $offset = 0, $username = "") {
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
            $friend['avatar_url'] = get_entity_icon_url($single,'large');
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
                array(
                        'limit' => array ('type' => 'int', 'required' => false),
                        'offset' => array ('type' => 'int', 'required' => false),
                        'username' => array ('type' => 'string', 'required' => false),
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
        $post['owner']['avatar_url'] = get_entity_icon_url($owner,'large');
        
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
    $name = strip_tags($json["name"]);
    if ($name) {
        if (elgg_strlen($name) > 50) {
                register_error(elgg_echo('user:name:fail'));
        } elseif ($owner->name != $name) {
                $owner->name = $name;
                $owner->save();
        }
        $input["name"] = $name;
    }
    // user name is handled seperately
    $new_username = strip_tags($json["username"]);
    if ($new_username) {
        if (!user_check_username_availability($new_username)) {
           if ($owner->username != $new_username) { // you are using the username of someone else
               throw new RegistrationException(elgg_echo('change_username:usernameexists'));
           }
        }
        if (elgg_strlen($new_username) > 50) {
                register_error(elgg_echo('user:name:fail'));
        }
        $owner->username = $new_username;
        $owner->save();
        $input["username"] = $new_username;
    }
    $new_password = strip_tags($json["password"]);
    if ($new_password) {
        $owner->salt = _elgg_generate_password_salt();
        $owner->password = generate_user_password($owner, $new_password);
        $owner->save();
        $input["password"] = $new_password;
    }
    // handle is_seller and gender seperately
    $input['is_seller'] = $owner->is_seller;
    $input['gender'] = $owner->gender;

    //~

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


function execute_new_password_request2($user_guid, $conf_code) {

    $password = null;
    $user_guid = (int)$user_guid;
    $user = get_entity($user_guid);
    if (!$user) {
        return "user_id $user_guid doesn't exist";
    }

    if ($password === null) {
        $password = generate_random_cleartext_password();
        $reset = true;
    }

    if (!elgg_instanceof($user, 'user')) {
        return false;
    }

    $saved_code = $user->getPrivateSetting('passwd_conf_code');
    $code_time = (int) $user->getPrivateSetting('passwd_conf_time');

    if (!$saved_code || $saved_code != $conf_code) {
        return false;
    }

    // Discard for security if it is 24h old
    if (!$code_time || $code_time < time() - 24 * 60 * 60) {
        return false;
    }

    if (force_user_password_reset($user_guid, $password)) {
        remove_private_setting($user_guid, 'passwd_conf_code');
        remove_private_setting($user_guid, 'passwd_conf_time');
        // clean the logins failures
        reset_login_failure_count($user_guid);

        $ns = $reset ? 'resetpassword' : 'changepassword';
 
$subject = "Lovebeauty password reset";
$body = "
   Hi $user->username, \n\n
   Your password was reset. New password: $password \n\n

   Yours truly,
   Lovebeauty Team
        ";

        notify_user($user->guid,
            elgg_get_site_entity()->guid,
            $subject, $body, $user->language,
            array(),
            'email'
        );
        forward(elgg_get_site_url()."/reset_pass_success.html");
//        return "Your password was reset. Check your email $user->email";
    }

    return false;
}

expose_function('user.execute_password_reset',
                "execute_new_password_request2",
                array(
                        'user_id' => array ('type' => 'string', 'required' => true, 'default' => null),
                        'conf_code' => array ('type' => 'string', 'required' => true, 'default' => null),
                    ),
                "Reset Password",
                'GET',
                false,
                false);

function send_new_password_request2($user_guid) {
    $user_guid = (int)$user_guid;

    $user = get_entity($user_guid);

    if ($user instanceof ElggUser) {
        // generate code
        $code = generate_random_cleartext_password();
        $user->setPrivateSetting('passwd_conf_code', $code);
        $user->setPrivateSetting('passwd_conf_time', time());
 
        // email subject
        $subject = "[LoveBeauty] Email reset request";

        // link for changing the password
        $link = elgg_get_site_url()."/services/api/rest/json/?method="."user.execute_password_reset"."&user_id=".$user_guid."&conf_code=".$code;

        // email message body
        $email = "
Dear $user->username,\n\n
Someone requrested to reset your password or to use your email to register. You can ignore this message if you don't know it. Your account and password will not be changed. To confirm reseting your password, click on the link below: \n
$link.\n\n

Best regards,\n
Lovebeauty Team\n
                 ";
        return notify_user($user->guid, elgg_get_site_entity()->guid,
            $subject, $email, array(), 'email');
    }
 
    return false;
}

expose_function('user.send_new_pw',
                "send_new_password_request2",
                array(
                        'user_id' => array ('type' => 'string', 'required' => true, 'default' => null),
                    ),
                "Reset Password",
                'POST',
                false,
                false);


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
//        if (user_reset_password2($user->guid)) {
        if (send_new_password_request2($user->guid)) {
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


function user_send_register_mail($email, $name, $username, $password) {
    $site = elgg_get_site_entity();
    $email = trim($email);

    // send out other email addresses
    if (!is_email_address($email)) {
        register_error(elgg_echo('user:email:wrong'));
        return false;
    }

    $display_name = $name ? $name : $username;
    $message = "
Dear $display_name,

Thank you for registering for Lovebeauty! Please enjoy the APP.

Your username is:
    $username

Regards,
Lovebeauty Team
";

        $subject = "Thank you for your register";

        $from = "team@lovebeauty.me";
        elgg_send_email($from, $email, $subject, $message);
        return 1;
}

function user_send_seller_register_mail($email, $name, $username, $password) {
    $site = elgg_get_site_entity();
    $email = trim($email);

    // send out other email addresses
    if (!is_email_address($email)) {
        register_error(elgg_echo('user:email:wrong'));
        return false;
    }

    $message = "
Dear $name,

Thank you for registering for Lovebeauty as a seller! We will contact you shortly.

Once your request is approved, you'll be able to use Lovebeauty's seller portal. Now feel free to download the APP and have fun.

https://itunes.apple.com/us/app/lovebeauty/id955373916?mt=8

Your username is:
    $username

Regards,
Lovebeauty Team
               ";

        $subject = "Thank you for your register as a seller";

        $from = "team@lovebeauty.me";
        elgg_send_email($from, $email, $subject, $message);
        elgg_send_email($from, $from, $subject, $message);
        return 1;
}

function user_send_subscriber_register_mail($email) {
    $site = elgg_get_site_entity();
    $email = trim($email);

    // send out other email addresses
    if (!is_email_address($email)) {
        register_error(elgg_echo('user:email:wrong'));
        return false;
    }

    $message = "
Hi,

Thank you for your interest in Lovebeauty. We will contact you shortly.

Regards,
Lovebeauty Team
               ";

        $subject = "Thank you for your interest";

        $from = "team@lovebeauty.me";
        elgg_send_email($from, $email, $subject, $message);
        return 1;
}


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
    $return['status'] = "";
    if (is_array($facebook_users) && count($facebook_users) == 1) {
            // reuse existing account
        $user = $facebook_users[0];
        $username = $user->username; // $fbData['user_profile']['username'];

        $return['status'] = "facebook account already exist";
        $return['username'] = $username;
        $token = create_user_token_same($username, 527040);
        $return['token'] = $token;
        return $return;
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
            }
            // send mail to user
            // send_user_password_mail($email, $name, $username, $password);
            // pull in facebook icon
            $url = 'https://graph.facebook.com/' . $fbData['user_profile']['id'] .'/picture?type=large';
                    facebook_import_avatar($user, $url);

            $return['status'] = "new facebook account is created";
            $return['username'] = $user->username;
            $token = create_user_token_same($user->username, 527040);
            $return['token'] = $token;

        } else {
            // CL: if email is used before, then we don't copy facebook's data anymore. Sounds good ? XXX
            $user= $users[0];

            $return['status'] = "email used before, won't copy facebook data";
            $return['username'] = $user->username;
            $token = create_user_token_same($username, 527040);
            $return['token'] = $token;
        }
    }
    return $return;
}

expose_function('user.register.facebook',
                "user_register_facebook",
                array('msg' => array ('type' => 'string', 'required' => true),
                    ),
                "Registration using Facebook",
                'POST',
                true,
                false);

function user_list_signup($signup_only) {

    $user = FALSE;
    $value = true;
    if ($signup_only != "true") {
        $value = NULL;
    }
    $signup_users = elgg_get_entities_from_metadata(array(
            'types' => 'user',
            'metadata_name_value_pairs' => array(
                'name' => 'email_subscriber',
                'value' => $value,
             ),
            'limit' => 0
        ));

    $return['total_number'] = count($signup_users);
    foreach($signup_users as $user) {
        $result = "";
        if ($user->email_subscriber == true) {
            $state = "Email subscriber";
        } else {
            $state = "Common user";
	}
        $result['is_seller'] = $user->is_seller;
        $result['is_admin'] = $user->is_admin;
        $result['state'] = $state;
        $result['email'] = $user->email;
        $result['username'] = $user->username;
        $result['name'] = $user->name;
        $result['time'] = date('Y-m-d H:i:sP', $user->time_created);

//        $user_string = $state."    ".$user->email."    ".$user->username."    ".$user->name."    ";
//        $user_string = $user_string.date('Y-m-d H:i:sP', $user->time_created)."    ";

        $params = array(
            'types' => 'object',
            'subtypes' => 'new_user_email',
            'owner_guid' => $user->guid,
            'limit' => 0,
            'full_view' => FALSE,
        );
        $blogs = elgg_get_entities($params);
//        $user_string = $user_string.count($blogs);

        if(blogs) {
            $result['contact_us'] = "";
            foreach($blogs as $single ) {
                $item['title'] = $single->title;
                $item['description'] = $single->description;
                $item['time'] = date('Y-m-d H:i:sP', $single->time_created);

//                $user_string = $user_string.$single->title."->";
//                $user_string = $user_string.$single->description."##";
                $result['contact_us'][] = $item;
            }
        }
        $return['user'][] = $result;
    }
    return $return;
}

expose_function('user.list.signup',
                "user_list_signup",
                array('signup_only' => array ('type' => 'string', 'required' => false, 'default' => "true"),
                    ),
                "Get email signup or all users",
                'GET',
                true,
                false);

function user_redeem_points($name, $address, $points) {
    $user = get_loggedin_user();

    if (!$user) {
        throw new RegistrationException(elgg_echo('Cannot find user'));
    }

    $subject = "LB Reward Check Redeem";
    $display_name = $user->name;
    if (!$display_name) {
        $display_name = $user->username;
    }
    $body = "
Dear $display_name,

We have received your request to redeem a Lovebeauty check at amount of $points points/cents.

Payments are distributed within two (2) weeks of request but may take up to sixty (60) days.
Checks will not be sent to P.O. Boxes. LoveBeauty is not responsible for replacing lost or stolen checks.

Your name to display on check: $name
Your address: $address

Please let us know if there's anything wrong.

Yours truly,
Lovebeauty Team
";
    $from = "team@lovebeauty.me";
    elgg_send_email($from, $from, $subject, $body);

    notify_user($user->guid,
        elgg_get_site_entity()->guid,
        $subject, $body, $user->language,
        array(),
        'email'
    );

    $user->points -= $points;
    if ($user->points < 0) {
        throw new RegistrationException(elgg_echo('points not insufficient'));    
    }
    $user->save();
    return "Points are redeemed.";
}

expose_function('user.redeem_points',
                "user_redeem_points",
                array(
                    'name' =>      array ('type' => 'string', 'required' => true, 'default' => ""),
                    'address' =>   array ('type' => 'string', 'required' => true, 'default' => ""),
                    'points' =>   array ('type' => 'string', 'required' => true, 'default' => "")
                    ),
                "User redeem points",
                'POST',
                true,
                true);

function user_delete($username) {
   $user = get_user_by_username($username);
 
   if ($guid == elgg_get_logged_in_user_guid()) {
       throw new RegistrationException(elgg_echo('admin:user:self:delete:no'));
   }
    
   $name = $user->name;
   $username = $user->username;
   
   if (($user instanceof ElggUser) && ($user->canEdit())) {
       if ($user->delete()) {
           return "delete success";
       } else {
           throw new RegistrationException(elgg_echo('admin:user:delete:no'));
       }
   } else {
       throw new RegistrationException(elgg_echo('admin:user:delete:no'));
   }
   return "delete success";
}   

expose_function('user.delete',
                "user_delete",
                array(
                    'username' =>   array ('type' => 'string', 'required' => true, 'default' => "")
                    ),
                "Delete user",
                'POST',
                true,
                true);

function user_set_admin($username) {
   $user = get_user_by_username($username);
 
   if (!$user) {
       throw new RegistrationException(elgg_echo('username:not:found'));
   }
   $user->is_admin = true;
   $user->save();

   return "admin is set.";
}   

expose_function('user.set_admin',
                "user_set_admin",
                array(
                    'username' =>   array ('type' => 'string', 'required' => true, 'default' => "")
                    ),
                "Set admin",
                'POST',
                true,
                true);

function user_set_seller($username) {
    $user = get_user_by_username($username);
 
    if (!$user) {
        throw new RegistrationException(elgg_echo('username:not:found'));
    }
    $user->is_seller = "true";
    if (!$user->save()) {
       throw new RegistrationException('User cannot be saved');
    }
    $return['is_seller'] = true;

    $subject = "Your Lovebeauty seller application is approved";
    $display_name = $user->name;
    if (!$display_name) {
        $display_name = $user->username;
    }
    $body = "
Dear $display_name,

Congratulations! Your seller application is approved. You can login your seller portal at www.lovebeauty.me/seller and start listing your product immediately.

Yours truly,
Lovebeauty Team
";
    $from = "team@lovebeauty.me";
    elgg_send_email($from, $from, $subject, $body);

    $return['cc_me'] = true;

    notify_user($user->guid,
        elgg_get_site_entity()->guid,
        $subject, $body, $user->language,
        array(),
        'email'
    );

    $return['seller_email_sent'] = true;
    return $return;
}   

expose_function('user.set_seller',
                "user_set_seller",
                array(
                    'username' =>   array ('type' => 'string', 'required' => true, 'default' => "")
                    ),
                "Set seller",
                'POST',
                true,
                true);


function user_set_points($username, $points) {
    $user = get_user_by_username($username);
 
    if (($user instanceof ElggUser) && ($user->canEdit())) {
       $user->points = $points;
       $user->save();
    }
    else {
        throw new RegistrationException(elgg_echo('username:not:found or user cannot edit'));
    }

   return "points are set";
}   

expose_function('user.set_points',
                "user_set_points",
                array(
                    'username' =>   array ('type' => 'string', 'required' => true, 'default' => ""),
                    'points' =>   array ('type' => 'integer', 'required' => false, 'default' => 0)
                    ),
                "Set points for user",
                'POST',
                true,
                true);
