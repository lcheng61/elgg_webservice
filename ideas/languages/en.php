<?php
/**
 * Elgg Ideas Plugin
 * @package ideas
 */

$english = array(
	
	// Menu items and titles	
	'ideas' => "Ideas posts",
	'ideas:posts' => "Ideas Posts",
	'ideas:title' => "The Ideas",
	'ideas:user:title' => "%s's posts on The Ideas",
	'ideas:user' => "%s's Ads",
	'ideas:user:friends' => "%s's friends Ideas",
	'ideas:user:friends:title' => "%s's friends posts on The Ideas",
	'ideas:mine' => "My Ads",
	'ideas:mine:title' => "My posts on The Ideas",
	'ideas:posttitle' => "%s's Ideas item: %s",
	'ideas:friends' => "Friends Ideas",
	'ideas:friends:title' => "My friends posts on The Ideas",
	'ideas:everyone:title' => "Everything on The Ideas",
	'ideas:everyone' => "All Ideas Posts",
	'ideas:read' => "View post",
	'ideas:add' => "Create New Ad",
	'ideas:add:title' => "Create a new post on The Ideas",
	'ideas:edit' => "Edit Ad",
	'ideas:imagelimitation' => "(Must be JPG, GIF or PNG)",
	'ideas:text' => "Give a brief description about the item",
	'ideas:uploadimages' => "Add images to your ad.",
	'ideas:uploadimage1' => "Image 1 (cover image)",
	'ideas:uploadimage2' => "Image 2",
	'ideas:uploadimage3' => "Image 3",
	'ideas:uploadimage4' => "Image 4",
	'ideas:image' => "Ad image",
	'ideas:delete:image' => "Delete this image",
	'ideas:imagelater' => "",
	'ideas:strapline' => "Created",
	'item:object:ideas' => 'Ideas posts',
	'ideas:none:found' => 'No ideas post found',
	'ideas:pmbuttontext' => "Send Private Message",
	'ideas:price' => "Price",
	'ideas:price:help' => "(in %s)",
	'ideas:text:help' => "(No HTML and max. 250 characters)",
	'ideas:title:help' => "(1-3 words)",
	'ideas:tags' => "Tags",
	'ideas:tags:help' => "(Separate with commas)",
	'ideas:access:help' => "(Who can see this ideas post)",
	'ideas:replies' => "Replies",
	'ideas:created:gallery' => "Created by %s <br>at %s",
	'ideas:created:listing' => "Created by %s at %s",
	'ideas:showbig' => "Show larger picture",
	'ideas:type' => "Type",
	'ideas:type:choose' => 'Choose ideas post type',
	'ideas:choose' => "Choose one...",
	'ideas:charleft' => "characters left",
	'ideas:accept:terms' => "I have read and accepted the %s",
	'ideas:terms' => "terms",
	'ideas:terms:title' => "Terms of use",
	'ideas:terms' => "<li class='elgg-divide-bottom'>The Ideas is for buying or selling used itemts among members.</li>
			<li class='elgg-divide-bottom'>Only one Ideas post is allowed pr. item.</li>

			<li class='elgg-divide-bottom'>A Ideas post may only contain one item, unless it's part of a matching set.</li>
			<li class='elgg-divide-bottom'>The Ideas is for used/home made items only.</li>
			<li class='elgg-divide-bottom'>The Ideas post must be deleted when it's no longer relevant.</li>
			<li class='elgg-divide-bottom'>Posts will be deleted after %s month(s).</li>
			<li class='elgg-divide-bottom'>Commercial advertising is limited to those who have signed a promotional agreement with us.</li>
			<li class='elgg-divide-bottom'>We reserve the right to delete any Ideas posts violating our terms of use.</li>
			<li class='elgg-divide-bottom'>Terms are subject to change over time.</li>
			",
	'ideas:new:post' => "New Ideas post",
	'ideas:notification' =>
'%s created a new post to the Ideas:

%s - %s
%s

View the post here:
%s
',
	// ideas widget
	'ideas:widget' => "My Ideas",
	'ideas:widget:description' => "Showcase your posts on The Ideas",
	'ideas:widget:viewall' => "View all my posts on The Ideas",
	'ideas:num_display' => "Number of posts to display",
	'ideas:icon_size' => "Icon size",
	'ideas:small' => "small",
	'ideas:tiny' => "tiny",
		
	// ideas river
	'river:create:object:ideas' => '%s posted a new ad in The Ideas %s',
	'river:update:object:ideas' => '%s updated the ad %s in The Ideas',
	'river:comment:object:ideas' => '%s commented on The Ideas ad %s',

	// Status messages
	'ideas:posted' => "Your Ideas post was successfully posted.",
	'ideas:deleted' => "Your Ideas post was successfully deleted.",
	'ideas:uploaded' => "Your image was succesfully added.",
	'ideas:image:deleted' => "Your image was succesfully deleted.",

	// Error messages	
	'ideas:save:failure' => "Your Ideas post could not be saved. Please try again.",
	'ideas:error:missing:title' => "Error: Missing title!",
	'ideas:error:missing:description' => "Error: Missing description!",
	'ideas:error:missing:category' => "Error: Missing category!",
	'ideas:error:missing:price' => "Error: Missing price!",
	'ideas:error:missing:ideas_type' => "Error: Missing type!",
	'ideas:tobig' => "Sorry; your file is bigger then 1MB, please upload a smaller file.",
	'ideas:notjpg' => "Please make sure the picture inculed is a .jpg, .png or .gif file.",
	'ideas:notuploaded' => "Sorry; your file doesn't apear to be uploaded.",
	'ideas:notfound' => "Sorry; we could not find the specified Ideas post.",
	'ideas:notdeleted' => "Sorry; we could not delete this Ideas post.",
	'ideas:image:notdeleted' => "Sorry; we could not delete this image!",
	'ideas:tomany' => "Error: Too many Ideas posts",
	'ideas:tomany:text' => "You have reached the maximum number of Ideas posts pr. user. Please delete some first!",
	'ideas:accept:terms:error' => "You must accept the terms of use!",
	'ideas:error' => "Error: Cannot save ideas post!",
	'ideas:error:cannot_write_to_container' => "Error: Cannot write to container!",

	// Settings
	'ideas:settings:status' => "Status",
	'ideas:settings:desc' => "Description",
	'ideas:max:posts' => "Max. number of ideas posts pr. user",
	'ideas:unlimited' => "Unlimited",
	'ideas:currency' => "Currency ($, €, DKK or something)",
	'ideas:allowhtml' => "Allow HTML in ideas posts",
	'ideas:numchars' => "Max. number of characters in ideas post (only valid without HTML)",
	'ideas:pmbutton' => "Enable private message button",
	'ideas:adminonly' => "Only admin can create ideas posts",
	'ideas:comments' => "Allow comments",
	'ideas:custom' => "Custom field",
	'ideas:settings:type' => 'Enable ideas post types (buy/sell/swap/free)',	
	'ideas:type:all' => "All",
	'ideas:type:buy' => "Buying",
	'ideas:type:sell' => "Selling",
	'ideas:type:swap' => "Swap",
	'ideas:type:free' => "Free",
	'ideas:expire' => "Auto delete ideas posts older than",
	'ideas:expire:month' => "month",
	'ideas:expire:months' => "months",
	'ideas:expire:subject' => "Your ideas post has expired",
	'ideas:expire:body' => "Hi %s

Your ideas post '%s' you created %s, has been deleted.

This happens automatically when ideas post are older than %s month(s).",

	// ideas categories
	'ideas:categories' => 'Ideas categories',
	'ideas:categories:choose' => 'Choose category',
	'ideas:categories:settings' => 'Ideas Categories:',	
	'ideas:categories:explanation' => 'Set some predefined categories for posting to the ideas.<br>Categories could be "clothes, footwear, furniture etc...", seperate each category with commas - remember not to use special characters in categories and put them in your language files as ideas:category:<i>categoryname</i>',	
	'ideas:categories:save:success' => 'Site ideas categories were successfully saved.',
	'ideas:categories:settings:categories' => 'Ideas Categories',
	'ideas:category:all' => "All",
	'ideas:category' => "Category",
	'ideas:category:title' => "Category: %s",

	// Categories
	'ideas:category:clothes' => "Clothes/shoes",
	'ideas:category:furniture' => "Furniture",

	// Custom select
	'ideas:custom:select' => "Item condition",
	'ideas:custom:text' => "Condition",
	'ideas:custom:activate' => "Enable Custom Select:",
	'ideas:custom:settings' => "Custom Select Choices",
	'ideas:custom:choices' => "Set some predefined choices for the custom select dropdown box.<br>Choices could be \"ideas:custom:new,ideas:custom:used...etc\", seperate each choice with commas - remember to put them in your language files",

	// Custom choises
	 'ideas:custom:na' => "No information",
	 'ideas:custom:new' => "New",
	 'ideas:custom:unused' => "Unused",
	 'ideas:custom:used' => "Used",
	 'ideas:custom:good' => "Good",
	 'ideas:custom:fair' => "Fair",
	 'ideas:custom:poor' => "Poor",
);
					
add_translation("en",$english);

