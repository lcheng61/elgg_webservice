<?php
/**
 * Elgg Ideas Plugin
 * @package ideas
 */

$guid = (int) get_input('guid');
$imagenum = (int) get_input('imagenum');

$post = get_entity($guid);
if (!$post || $post->getSubtype() != "ideas") {
	exit;
}

$tu = $post->time_updated;
$img = elgg_view('output/img', array(
				'src' => "ideas/image/{$post->guid}/{$imagenum}/master/{$tu}",
				'class' => 'ideas-image-popup',
				));

echo "<div style='width: 600px; text-align: center;'>";
echo "<h3>{$post->title}</h3>";
echo $img;
echo "</div><br>";

