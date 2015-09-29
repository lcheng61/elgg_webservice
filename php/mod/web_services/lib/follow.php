<?php

/**
 * Web service to add as follower
 *
 * @param string $username Username
 * @param string $follow Username to be added as follow
 *
 * @return bool
 */           
function user_follow_add($friend, $username) {
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

expose_function('user.follow.add',
                "user_follow_add",
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
function user_follow_remove($friend,$username) {
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
        $msg = elgg_echo("friends:add:failure", array($friend_user->name));
         throw new InvalidParameterException($msg);
    }
    return $return;
}

expose_function('user.follow.remove',
                "user_friend_remove",
                array(
                        'friend' => array ('type' => 'string'),
                        'username' => array ('type' => 'string', 'required' => false),
                    ),
                "Remove friend",
                'GET',
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
function user_get_followers($username, $limit = 10, $offset = 0) {
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
    foreach($friends as $single) {
        $friend['guid'] = $single->guid;
        $friend['username'] = $single->username;
        $friend['name'] = $single->name;
        $friend['avatar_url'] = get_entity_icon_url($single,'small');
        $return[] = $friend;
    }
    } else {
        $msg = elgg_echo('friends:none');
        throw new InvalidParameterException($msg);
    }
    return $return;
}

expose_function('user.follow.get_followers',
                "user_get_followers",
                array('username' => array ('type' => 'string', 'required' => false),
                        'limit' => array ('type' => 'int', 'required' => false),
                        'offset' => array ('type' => 'int', 'required' => false),
                    ),
                "Register user",
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
function user_get_followers_of($username, $limit = 10, $offset = 0) {
    if(!$username){
        $user = get_loggedin_user();
    } else {
        $user = get_user_by_username($username);
    }
    if (!$user) {
        throw new InvalidParameterException(elgg_echo('registration:usernamenotvalid'));
    }
    $friends = get_user_friends_of($user->guid, '' , $limit, $offset);
    
    $success = false;
    foreach($friends as $friend) {
        $return['guid'] = $friend->guid;
        $return['username'] = $friend->username;
        $return['name'] = $friend->name;
        $return['avatar_url'] = get_entity_icon_url($friend,'small');
        $success = true;
    }
    
    if(!$success) {
        $return['error']['message'] = elgg_echo('friends:none');
    }
    return $return;
}

expose_function('user.follow.get_followers_of',
                "user_get_followers_of",
                array('username' => array ('type' => 'string', 'required' => true),
                        'limit' => array ('type' => 'int', 'required' => false),
                        'offset' => array ('type' => 'int', 'required' => false),
                    ),
                "Register user",
                'GET',
                false,
                false);    
                
