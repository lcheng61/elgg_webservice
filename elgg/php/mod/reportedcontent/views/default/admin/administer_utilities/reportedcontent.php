<?php
/**
 * Elgg Reported content admin page
 *
 * @package ElggReportedContent
 */

$list = elgg_list_entities(array('types' => 'object', 'subtypes' => 'reported_content'));
if (!$list) {
	$list = '<p class="mtm">' . elgg_echo('reportedcontent:none') . '</p>';
}

echo $list;