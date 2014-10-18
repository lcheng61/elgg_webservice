<?php
/**
 * Elgg Ideas Plugin
 * @package ideas
 */

// Load Elgg engine
require_once(elgg_get_root_path() . "/engine/start.php");

// How long does a classifieds live
$ideas_expire = elgg_get_plugin_setting('ideas_expire', 'ideas');

echo "<br><h3>" . elgg_echo('ideas:terms:title') . "</h3>";
echo "<ul>" . elgg_echo('ideas:terms', array($ideas_expire)) . "</ul>";
echo "<br>";
?>

