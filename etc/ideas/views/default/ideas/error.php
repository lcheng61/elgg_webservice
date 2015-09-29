<?php
/**
 * Elgg Ideas Plugin
 * @package ideas
 */

// Display an error

echo "<div>";
echo "<p>";
echo elgg_echo('ideas:tomany:text');
echo "</p>";
echo "<p>";
echo elgg_view('output/url', array(
			'href' => "mod/ideas/terms.php",
			'text' => elgg_echo('ideas:terms:title'),
			'class' => "elgg-lightbox",
			));
echo "</p>";
echo "</div>";
