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
//    $profile_info['likes_items'] = json_decode(elgg_view_page($title, $layout), true);
//~

/* old get likes_number    
    $objs_array = explode(",", $user->liked_products);
    $liked_products_count = count ($objs_array);
        $objs_array = explode(",", $user->liked_ideas);
    $liked_ideas_count = count ($objs_array);
    $profile_info['likes_number'] = $liked_products_count + $liked_ideas_count;
*/

// new likes_number
    $profile_info['likes_number'] = count($profile_info['likes_items']['object']['market']) + 
            count($profile_info['likes_items']['object']['ideas']);
//~

//    $profile_info['likes_total'] = $user->countAnnotations('likes');
//    $profile_info['likes_total'] = $user->getAnnotationsSum('likes');

    $friends = get_user_friends($user->guid, '' , 0, 0);
    $follower_number = 0;
    if($friends){
        $follower_number = count($friends);
    }
    $profile_info['follower_number'] = $follower_number;

    $friends = get_user_friends_of($user->guid, '' , 0, 0);
    $following_number = 0;
    if($friends) {
        $following_number = count($friends);
    }
    $profile_info['following_number'] = $following_number;
    
        $options = array(
                'annotations_name' => 'generic_comment',
                'guid' => $user->$guid,
                'limit' => $limit,
                'pagination' => false,
                'reverse_order_by' => true,
                );
        $comments = elgg_get_annotations($options);
        $num_comments = count($comments);
        $profile_info['comments_number'] = $num_comments;

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
function user_register($name, $email, $username, $password) {
    $user = get_user_by_username($username);
    if (!$user) {
        $return['success'] = true;
        $return['guid'] = register_user($username, $password, $name, $email);
    } else {
        throw new RegistrationException(elgg_echo('registration:userexists'));
    }
    return user_get_profile($username);
//    return $return;
}

expose_function('user.register',
                "user_register",
                array('name' => array ('type' => 'string'),
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
    $friends = get_user_friends($user->guid, '' , $limit, $offset);
    
    if($friends){
        $return['total_number'] = count($friends);
        $return['offset'] = $offset;
        foreach($friends as $single) {
            $friend['guid'] = $single->guid;
            $friend['username'] = $single->username;
            $friend['name'] = $single->name;
            $friend['is_seller'] = $single->is_seller;
            $friend['avatar_url'] = get_entity_icon_url($single,'small');
            $friend['do_i_follow'] = user_is_friend($single->guid, $user->guid);
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
    $friends = get_user_friends_of($user->guid, '' , $limit, $offset);

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
        throw new InvalidParameterException("cannot_upload_avatar");
    }

    if ($_FILES['avatar']['error'] != 0) {
	register_error(elgg_echo('avatar:upload:fail'));
        throw new InvalidParameterException("cannot_upload_avatar");
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
            throw new InvalidParameterException("cannot_upload_avatar");
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
function profile_array_decoder(&$v) {
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
                array_walk_recursive($value, 'profile_array_decoder');
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
