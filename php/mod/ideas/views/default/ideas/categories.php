<?php
/**
 * Elgg Ideas Plugin
 * @package ideas (forked from webgalli's Classifieds Plugin)
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author slyhne
 * @copyright TechIsUs
 * @link www.techisus.dk
 */

$selected_category = get_input('cat', 'all');

$categories = string_to_tag_array(elgg_get_plugin_setting('ideas_categories', 'ideas'));

if (!empty($categories)) {
	if (!is_array($categories)) $categories = array($categories);
	$url = elgg_get_site_url() . 'ideas/category/';
	echo "<ul>";
	foreach ($categories as $category) {
		echo "<li>";
		$selected = '';
		if ($selected_category == $category) {
			$selected = 'selected';
		}
		echo elgg_view('output/url', array(
					'href' => $url . $category,
					'text' => elgg_echo("ideas:category:$category"),
					'class' => "ideas-category-menu-item $selected",
					'is_trusted' => true,
					));
		echo "</li>";
	}
	echo "</ul>";
}

