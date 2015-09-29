<?php
/**
 * Blog river view.
 */
   $object = $vars['item']->getObjectEntity();
    $num_chars = 250;
    $excerpt = $object->description;
    $string_length = elgg_strlen($excerpt);
    if ($string_length >= $num_chars) {
    $text = trim(elgg_strip_tags($excerpt)); 
    if ($string_length == elgg_strlen($text)) {
    $excerpt = elgg_substr($text, 0, $num_chars);
    $space = elgg_strrpos($excerpt, ' ', 0);
    if ($space === FALSE) {
        $space = $num_chars;
    }
    $excerpt = trim(elgg_substr($excerpt, 0, $space));
    if ($string_length != elgg_strlen($excerpt)) {
        $excerpt .= '...';
        }
    }
 }

echo elgg_view('river/elements/layout', array(
    'item' => $vars['item'],
    'message' => $excerpt,
));
