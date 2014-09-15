<?php
/**
 * Elgg Ideas Plugin
 * @package ideas
 */

//the page owner
$owner = get_user($vars['entity']->owner_guid);

//the number of files to display
$num = (int) $vars['entity']->num_display;
if (!$num) {
	$num = 4;
}		
		
$posts = elgg_get_entities(array('type'=>'object','subtype'=>'ideas', 'owner_guid' => $owner->guid, 'limit'=>$num));

echo '<ul class="elgg-list">';		
// display the posts, if there are any
if (is_array($posts) && sizeof($posts) > 0) {

	if (!$size || $size == 1){
		foreach($posts as $post) {
			echo "<li class=\"pvs\">";
			$category = "<b>" . elgg_echo('ideas:category') . ":</b> " . elgg_echo('ideas:category:' . $post->ideascategory);
			$type = "<b>" . elgg_echo('ideas:type') . ":</b> " . elgg_echo("ideas:type:{$post->ideas_type}");
			$price = "<b>" . elgg_echo('ideas:price') . ":</b> {$post->price}";
			$comments_count = $post->countComments();
			$text = elgg_echo("comments") . " ($comments_count)";
			$comments_link = elgg_view('output/url', array(
						'href' => $post->getURL() . '#ideas-comments',
						'text' => $text,
						));
			$img = elgg_view('output/img', array(
						'src' => "ideas/image/{$post->guid}/1/small/{$tu}",
						));
			$ideas_img = elgg_view('output/url', array(
						'href' => $post->getURL(),
						'text' => $img,
						));
			$subtitle = "$category<br>$type<br>$price";
			$subtitle .= "<br>{$author_text} {$date} {$comments_link}";
			$params = array(
				'entity' => $post,
				'metadata' => $metadata,
				'subtitle' => $subtitle,
				'tags' => $tags,
				'content' => $excerpt,
			);
			$params = $params + $vars;
			$list_body = elgg_view('object/elements/summary', $params);
			echo elgg_view_image_block($ideas_img, $list_body);
			echo "</li>";
		}
			
	}
	echo "</ul>";
	echo "<div class=\"contentWrapper\"><a href=\"" . $CONFIG->wwwroot . "pg/ideas/" . $owner->username . "\">" . elgg_echo("ideas:widget:viewall") . "</a></div>";

}

