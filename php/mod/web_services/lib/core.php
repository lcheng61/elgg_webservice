<?php
/**
 * Elgg Webservices plugin 
 * 
 * @package Webservice
 * @author Mark Harding
 *
 */

/**
 * Heartbeat web service
 *
 * @return string $response Hello
 */
function site_test() {
    $response['success'] = true;
    $response['message'] = "Hello";
    return $response;
} 

expose_function('site.test',
                "site_test",
                array(),
                "Get site information",
                'GET',
                false,
                false);

function site_test_auth() {
    $response['success'] = true;
    $response['message'] = "Hello";
    return $response;
} 

expose_function('site.test_auth',
                "site_test_auth",
                array(),
                "Get site information, must be authorized",
                'GET',
                true,
                true);

/**
 * Web service to get site information
 *
 * @return string $url URL of Elgg website
 * @return string $sitename Name of Elgg website
 * @return string $language Language of Elgg website
 * @return string $enabled_services List of enabled services
 */
function site_getinfo() {
    $site = elgg_get_config('site');

    $siteinfo['url'] = elgg_get_site_url();
    $siteinfo['sitename'] = $site->name;
    $siteinfo['language'] = elgg_get_config('language');
    $siteinfo['enabled_services'] = $enabled = unserialize(elgg_get_plugin_setting('enabled_webservices', 'web_services'));
    
    //return OAuth info
    if(elgg_is_active_plugin('oauth',0) == true){
        $siteinfo['OAuth'] = "running";
    } else {
        $siteinfo['OAuth'] = "no";
    }
    
    return $siteinfo;
} 

expose_function('site.getinfo',
                "site_getinfo",
                array(),
                "Get site information",
                'GET',
                false,
                false);
                
/**
 * Retrive river feed
 *
 * @return array $river_feed contains all information for river
 */            
function site_river_feed($limit){
    
    global $jsonexport;
    
    elgg_view_river_items();

    return $jsonexport['activity'];
    
}
expose_function('site.river_feed',
                "site_river_feed",
                array('limit' => array('type' => 'int', 'required' => 'no')),
                "Get river feed",
                'GET',
                false,
                false);
                
/**
 * Performs a search of the elgg site
 *
 * @return array $results search result
 */
 
function site_search($query, $offset, $limit, $sort, $order, $search_type, $entity_type, $entity_subtype, $owner_guid, $container_guid){
    
    $params = array(
                    'query' => $query,
                    'offset' => $offset,
                    'limit' => $limit,
                    'sort' => $sort,
                    'order' => $order,
                    'search_type' => $search_type,
                    'type' => $entity_type,
                    'subtype' => $entity_subtype,
                //    'tag_type' => $tag_type,
                    'owner_guid' => $owner_guid,
                    'container_guid' => $container_guid,
                    );
                    
    $types = get_registered_entity_types();
    
    foreach ($types as $type => $subtypes) {

        $results = elgg_trigger_plugin_hook('search', $type, $params, array());
        if ($results === FALSE) {
            // someone is saying not to display these types in searches.
            continue;
        }
        
        if($results['count']){
            foreach($results['entities'] as $single){
        
                //search matched critera
                /*
                $result['search_matched_title'] = $single->getVolatileData('search_matched_title');
                $result['search_matched_description'] = $single->getVolatileData('search_matched_description');
                $result['search_matched_extra'] = $single->getVolatileData('search_matched_extra');
                */
                if($type == 'group' || $type== 'user'){
                    $result['title'] = $single->name;    
                } else {
                    $result['title'] = $single->title;
                }
                $result['guid'] = $single->guid;
                $result['type'] = $single->type;
                $result['subtype'] = get_subtype_from_id($single->subtype);
                
                $result['avatar_url'] = get_entity_icon_url($single,'small');
                
                $return[$type] = $result;
            }
        }
    }

    return $return;
}
expose_function('site.search',
                "site_search",
                array(    'query' => array('type' => 'string'),
                        'offset' =>array('type' => 'int', 'required'=>false, 'default' => 0),
                        'limit' =>array('type' => 'int', 'required'=>false, 'default' => 10),
                        'sort' =>array('type' => 'string', 'required'=>false, 'default' => 'relevance'),
                        'order' =>array('type' => 'string', 'required'=>false, 'default' => 'desc'),
                        'search_type' =>array('type' => 'string', 'required'=>false, 'default' => 'all'),
                        'entity_type' =>array('type' => 'string', 'required'=>false, 'default' => ELGG_ENTITIES_ANY_VALUE),
                        'entity_subtype' =>array('type' => 'string', 'required'=>false, 'default' => ELGG_ENTITIES_ANY_VALUE),
                        'owner_guid' =>array('type' => 'int', 'required'=>false, 'default' => ELGG_ENTITIES_ANY_VALUE),
                        'container_guid' =>array('type' => 'int', 'required'=>false, 'default' => ELGG_ENTITIES_ANY_VALUE),
                        ),
                "Perform a search",
                'GET',
                false,
                false);

/**
 * The auth.gettoken API.
 * This API call lets a user log in, returning an authentication token which can be used
 * to authenticate a user for a period of time. It is passed in future calls as the parameter
 * auth_token.
 *
 * @param string $username Username
 * @param string $password Clear text password
 *
 * @return string Token string or exception
 * @throws SecurityException
 * @access private
 */
/*
function auth_gettoken2_old($username, $password, $expire=527040) {
        // check if username is an email address
        if (is_email_address($username)) {
                $users = get_user_by_email($username);
                        
                // check if we have a unique user
                if (is_array($users) && (count($users) == 1)) {
                        $username = $users[0]->username;
                }
        }
        
        // validate username and password
        if (true === elgg_authenticate($username, $password)) {
                $token = create_user_token_same($username, $expire);
                if ($token) {
                        return $token;
                }
        }

        throw new SecurityException(elgg_echo('SecurityException:authenticationfailed'));
}
expose_function(
    "auth.gettoken2_old",
    "auth_gettoken2_old",
    array(
        'username' => array ('type' => 'string'),
        'password' => array ('type' => 'string'),
        'expire' => array ('type' => 'int', 'default' => 527040),
    ),
    elgg_echo('auth.gettoken'),
    'POST',
    true,
    false
);
*/
/**
 * The auth.gettoken API.
 * This API call lets a user log in, returning an authentication token which can be used
 * to authenticate a user for a period of time. It is passed in future calls as the parameter
 * auth_token.
 *
 * @param string $username Username
 * @param string $password Clear text password
 *
 * @return string Token string or exception
 * @throws SecurityException
 * @access private
 */
function auth_gettoken2($username, $password, $expire=527040) {
        // check if username is an email address
        if (is_email_address($username)) {
                $users = get_user_by_email($username);
                        
                // check if we have a unique user
                if (is_array($users) && (count($users) == 1)) {
                        $username = $users[0]->username;
                }
        }
        
        // validate username and password
        if (true === elgg_authenticate($username, $password)) {
                $token = create_user_token_same($username, $expire);
                if ($token) {
                    $return['token'] = $token;
                } else {
                    throw new SecurityException(elgg_echo('SecurityException:authenticationfailed'));
                }
                $user = get_user_by_username($username);
                if (!$user) {
                    throw new InvalidParameterException('registration:usernamenotvalid');
                }
                login($user);
$return['is_seller_raw'] = $user->is_seller;
                if ($user->is_seller) {
		    if ($user->is_seller == "true") {
                        $return['is_seller'] = true;
                    } else {
                        $return['is_seller'] = false;
                    }
		} else {
                    $return['is_seller'] = false;
		}
   	        if ($user && $user->isAdmin()) {
                    $return['is_admin'] = true;
                } else {
                    $return['is_admin'] = false;
                }
        } else {
            throw new SecurityException(elgg_echo('SecurityException:authenticationfailed'));
        }

        return $return;
}
expose_function(
    "auth.gettoken2",
    "auth_gettoken2",
    array(
        'username' => array ('type' => 'string'),
        'password' => array ('type' => 'string'),
        'expire' => array ('type' => 'int', 'default' => 527040),
    ),
    elgg_echo('auth.gettoken'),
    'POST',
    true,
    false
);

/*
 * Push message to a target user
 */

function push_notification($msg, $link, $user_id) {

    $url = 'https://api.parse.com/1/push';

    $appId = 'bY2NzaiWKmujzMfvUB6rH2y56l4Q4FNsBJdHAtQN';
    $restKey = 'iBKA3kaND3pKDUemHCUu7mzeAY6Oxu5nHnCjQJVD';

    $target_name = "lovebeautyUser";
    $target_id = "$user_id";

    $push_payload = json_encode(array(
        "where" => array(
                $target_name => $target_id
        ),
        "data" => array(
                "alert" => $msg,
                "p" => $link
        )
    ));

    $rest = curl_init();
    curl_setopt($rest,CURLOPT_URL,$url);
    curl_setopt($rest,CURLOPT_PORT,443);
    curl_setopt($rest,CURLOPT_POST,1);
    curl_setopt($rest,CURLOPT_POSTFIELDS,$push_payload);
    curl_setopt($rest,CURLOPT_HTTPHEADER,
        array("X-Parse-Application-Id: " . $appId,
                "X-Parse-REST-API-Key: " . $restKey,
                "Content-Type: application/json"));
    curl_setopt($rest, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($rest);

    $return['push_response'] = $response;
// save notification to the object
    $news_obj = new ElggObject();
    $news_obj->title = $msg;
    $news_obj->content = $link;
    $news_obj->subtype = "news_object";
    $news_obj->access_id = ACCESS_LOGGED_IN;
    $news_obj->recipient_guid = $user_id;
    $date = date_create();
    $news_obj->timestamp = date_format($date, 'U');
    $news_obj->is_read = false;

    if ($news_obj->save()) {
        $return['save_news_obj'] = true;
    } else {
        throw new InvalidParameterException(elgg_echo("news:object:saveerror"));
    }

    return $return;

}
expose_function(
    "push.notification",
    "push_notification",
    array(
        'msg' => array ('type' => 'string'),
        'user_id' => array ('type' => 'int'),
    ),
    elgg_echo('user.push_notification'),
    'POST',
    true,
    true
);

function news_list($limit, $offset) {
    $user = get_loggedin_user();
    if (!$user) {
        throw new InvalidParameterException('news_list:usernotloggedin');
    }
    if ($user->is_admin) {
        $params = array(
            'types' => 'object',
            'subtypes' => 'news_object',
            'limit' => $limit,
            'full_view' => FALSE,
            'offset' => $offset,
            );
        $latest_blogs = elgg_get_entities($params);
    } else {
        $params = array(
            'types' => 'object',
            'subtypes' => 'news_object',
            'limit' => $limit,
            'offset' => $offset,
            'metadata_name_value_pairs'=>array(
                array('name' => 'recipient_guid', 
                      'value' => $user->guid,
                      'operand' => '=' )));
        $latest_blogs = elgg_get_entities_from_metadata($params);
    }
    $return['offset'] = $offset;
    if($latest_blogs) {
        $display_number = 0;
        $return['user_id'] = $user->guid;
        foreach($latest_blogs as $single ) {
            $item['news_id'] = $single->guid;
            $item['title'] = $single->title;
            $item['content'] = $single->content;
            $item['timestamp'] = $single->timestamp;
            $item['is_read'] = $single->is_read;

            $display_number ++;
            $return['news'][] = $item;
        }
	
        $return['total_number'] = $display_number;
    }
    else {
        $return['news'] = array();
        $return['total_number'] = 0;
    }
    return $return;
}
expose_function(
    "news.list",
    "news_list",
    array(
        'limit' => array ('type' => 'int', 'required' => false, 'default' => 10),
        'offset' => array ('type' => 'int', 'required' => false, 'default' => 0)
    ),
    'List user notification/news',
    'GET',
    true,
    true
);


function create_user_token_same($username, $expire = 5256000) {
        global $CONFIG;

        $site_guid = $CONFIG->site_id;
        $user = get_user_by_username($username);
        $time = time();
        $time += 60 * $expire;
//	$token = md5(rand() . microtime() . $username . $time . $site_guid);
        $token = md5($username . $site_guid);

        if (!$user) {
                return false;
        }

        if (insert_data("INSERT into {$CONFIG->dbprefix}users_apisessions
                                (user_guid, site_guid, token, expires) values
                                ({$user->guid}, $site_guid, '$token', '$time')
                                on duplicate key update token='$token', expires='$time'")) {
                return $token;
        }

        return false;
}
