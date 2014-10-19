<?php
/**
 * Web Services plugin settings
 */


echo '<div>';
echo elgg_echo('web_services:settings_description');
echo elgg_view("input/checkboxes", array(
			'name' => 'params[enabled_webservices]',
			'value' => unserialize(elgg_get_plugin_setting('enabled_webservices', 'web_services')),
			'options' => array(elgg_echo("web_services:user") => 'user', 
   				           elgg_echo("web_services:blog") => 'blog', 
					   elgg_echo("web_services:wire") => 'wire', 
					   elgg_echo("web_services:group") => 'group',
					   elgg_echo("web_services:file") => 'file',
					   elgg_echo("web_services:market") => 'market',
					   elgg_echo("web_services:ideas") => 'ideas',
					   elgg_echo("web_services:likes") => 'likes',
					   elgg_echo("web_services:messages") => 'message',
					   elgg_echo("web_services:follow") => 'follow'),
			));

echo '</div>';
