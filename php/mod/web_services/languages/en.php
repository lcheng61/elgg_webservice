<?php
/**
 * Elgg Web Services language pack.
 * 
 * @package Webservice
 * @author Saket Saurabh
 */

$english = array(
	'web_services:user' => "User", 
	'web_services:blog' => "Blog", 
	'web_services:wire' => "Wire", 
	'web_services:core' => "Core", 
	'web_services:group' => "Group",
	'web_services:file' => "File",
	'web_services:messages' => "Messages",
	'web_services:market' => "Market",
	'web_services:ideas' => "Ideas",
	'web_services:settings_description' => "Select the web services below that you wish to be enabled:",
	'web_services:selectfeatures' => "Select the features to be enabled",
	'friends:alreadyadded' => "%s is already added as friend",
	'friends:remove:notfriend' => "%s is not your friend",
	'blog:message:notauthorized' => "Not authorized to carry this request",
	'blog:message:noposts' => "No blog posts by user",

	'admin:utilities:web_services' => 'Web Services Tests',
	'web_services:tests:instructions' => 'Run the unit tests for the web services plugin',
	'web_services:tests:run' => 'Run tests',
	'web_services:likes' => 'Likes',
	'web_services:follow' => 'Follow user',
	'likes:notallowed' => 'Not allowed to like',
	
	//A resolution to json convertion error (for river)
	'river:update:user:default' => ' updated their profile ',


	'product_review:email:subject' => 'You have a new review!',
	'product_review:email:body2' => "You have a new review on your product \"%s\" from %s. It reads:


%s

Rate: %s

To reply or view the original item, click here:

%s

To view %s's profile, click here:

%s

You cannot reply to this email.",

	'product_review:email:body' => "You have a new review on your product \"%s\" from %s. It reads:


%s

Rate: %s

You cannot reply to this email.",

	'idea_comment:email:subject' => 'You have a new comment!',
	'idea_comment:email:body2' => "You have a new comment on your idea \"%s\" from %s. It reads:


%s


To reply or view the original item, click here:

%s

To view %s's profile, click here:

%s

You cannot reply to this email.",

	'idea_comment:email:body' => "You have a new comment on your idea \"%s\" from %s. It reads:


%s


You cannot reply to this email.",

);
				
add_translation("en", $english);
