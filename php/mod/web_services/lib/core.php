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
                $token = create_user_token($username, $expire);
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
                $token = create_user_token($username, $expire);
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
