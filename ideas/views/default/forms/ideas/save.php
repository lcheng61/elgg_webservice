<?php
/**
 * Elgg Ideas Plugin
 * @package ideas
 */

elgg_load_js('lightbox');
elgg_load_css('lightbox');

$post = get_entity($vars['guid']);
if ($post) {
	$tu = $post->time_updated;
	$images = unserialize($post->images);
}

// Get plugin settings
$allowhtml = elgg_get_plugin_setting('ideas_allowhtml', 'ideas');
$currency = elgg_get_plugin_setting('ideas_currency', 'ideas');
$numchars = elgg_get_plugin_setting('ideas_numchars', 'ideas');
$ideascategories = string_to_tag_array(elgg_get_plugin_setting('ideas_categories', 'ideas'));
$custom_choices = string_to_tag_array(elgg_get_plugin_setting('ideas_custom_choices', 'ideas'));

if($numchars == ''){
	$numchars = '250';
}

echo "<div><label>";
echo elgg_echo('title') . "<span class='elgg-subtext mlm'>" . elgg_echo("ideas:title:help") . "</span>";
echo elgg_view("input/text", array(
				"name" => "title",
				"value" => $vars['title'],
				'autofocus' => true,
				'required' => true,
				));
echo "</label></div>";

if (!empty($ideascategories)) {
	$options = array();
	$options[''] = elgg_echo("ideas:choose");
	foreach ($ideascategories as $option) {
		$options[$option] = elgg_echo("ideas:category:{$option}");
	}		
	unset($options['all']);
	echo "<div><label>";
	echo elgg_view('input/dropdown',array(
						'options_values' => $options,
						'value' => $vars['ideascategory'],
						'name' => 'ideascategory',
						'required' => true,
						));
	echo elgg_echo('ideas:categories:choose') . "</label></div>";
}

$types = array('buy', 'sell', 'swap', 'free');
$options = array();
$options[''] = elgg_echo("ideas:choose");
foreach ($types as $type) {
	$options[$type] = elgg_echo("ideas:type:{$type}");
}		
echo "<div><label>";
echo elgg_view('input/dropdown',array(
				'options_values' => $options,
				'value' => $vars['ideas_type'],
				'name' => 'ideas_type',
				'required' => true,
				));
echo elgg_echo('ideas:type:choose') . "</label></div>";

if (elgg_get_plugin_setting('ideas_custom', 'ideas') == 'yes' && !empty($custom_choices)) {
	$custom_options = array();
	$custom_options[''] = elgg_echo("ideas:choose");
	foreach ($custom_choices as $custom_choice) {
		$custom_options[$custom_choice] = elgg_echo("ideas:custom:{$custom_choice}");
	}		
	
	echo "<div><label>";
	echo elgg_view('input/dropdown',array(
						'options_values' => $custom_options,
						'value' => $vars['custom'],
						'name' => 'custom',
						'required' => true,
						));
	echo elgg_echo('ideas:custom:select') . "</label></div>";
}

echo "<div><label>" . elgg_echo("ideas:text");
if ($allowhtml != 'yes') {
	echo "<span class='elgg-subtext mlm'>" . elgg_echo("ideas:text:help", array($numchars)) . "</span>";
	echo "<textarea name='description' class='mceNoEditor' rows='5' cols='40' onKeyDown='textCounter(document.ideasForm.description,document.ideasForm.remLen1,{$numchars}' onKeyUp='textCounter(document.ideasForm.description,document.ideasForm.remLen1,{$numchars})'>{$vars['description']}</textarea><br />";
	echo "<div class='ideas_characters_remaining'><input readonly type='text' name='remLen1' size='3' maxlength='3' value='{$numchars}' class='ideas_charleft'>" . elgg_echo("ideas:charleft") . "</div>";
} else {
	echo elgg_view("input/longtext", array(
					'name' => 'description',
					'value' => $vars['description'],
					'required' => true,
					));
}
echo "</label></div>";

echo "<div><label>" . elgg_echo("ideas:price");
echo "<span class='elgg-subtext mlm'>" . elgg_echo("ideas:price:help", array($currency)) . "</span>";
echo elgg_view("input/text", array(
				"name" => "price",
				"value" => $vars['price'],
				'required' => true,
				));
			
echo "</label></div>";

echo "<div><label>" . elgg_echo("ideas:tags");
echo "<span class='elgg-subtext mlm'>" . elgg_echo("ideas:tags:help") . "</span>";
echo elgg_view("input/tags", array(
				"name" => "tags",
				"value" => $vars['tags'],
				));
echo "</label></div>";

echo "<div><label>" . elgg_echo("ideas:uploadimages");
echo "<span class='elgg-subtext mlm'>" . elgg_echo("ideas:imagelimitation") . "</span>";
echo "</label></div>";

$image1 = elgg_view('ideas/thumbnail', array(
			'guid' => $post->guid,
			'imagenum' => 1,
			'size' => 'medium',
			'class' => 'ideas-form-image',
			'tu' => $tu
			));
$body1 = "<div><label>" . elgg_echo("ideas:uploadimage1");
if ($images[1]) {
	$body1 .= elgg_view('output/confirmlink', array(
			'href' => "action/ideas/delete_img?guid={$post->guid}&img=1",
			'text' => elgg_echo('delete'),
			'is_action' => true,
			'class' => 'elgg-button elgg-button-delete float-alt mrs',
			'confirm' => elgg_echo('ideas:delete:image'),
			));
}
$body1 .= elgg_view("input/file",array('name' => 'upload1'));
$body1 .= "</label></div>";

echo elgg_view_image_block($image1, $body1);

$image2 = elgg_view('ideas/thumbnail', array(
			'guid' => $post->guid,
			'imagenum' => 2,
			'size' => 'medium',
			'class' => 'ideas-form-image',
			'tu' => $tu
			));
$body2 = "<div><label>" . elgg_echo("ideas:uploadimage2");
if ($images[2]) {
	$body2 .= elgg_view('output/confirmlink', array(
			'href' => "action/ideas/delete_img?guid={$post->guid}&img=2",
			'text' => elgg_echo('delete'),
			'is_action' => true,
			'class' => 'elgg-button elgg-button-delete float-alt mrs',
			'confirm' => elgg_echo('ideas:delete:image'),
			));
}
$body2 .= elgg_view("input/file",array('name' => 'upload2'));
$body2 .= "</label></div>";

echo elgg_view_image_block($image2, $body2);

$image3 = elgg_view('ideas/thumbnail', array(
			'guid' => $post->guid,
			'imagenum' => 3,
			'size' => 'medium',
			'class' => 'ideas-form-image',
			'tu' => $tu
			));
$body3 = "<div><label>" . elgg_echo("ideas:uploadimage3");
if ($images[3]) {
	$body3 .= elgg_view('output/confirmlink', array(
			'href' => "action/ideas/delete_img?guid={$post->guid}&img=3",
			'text' => elgg_echo('delete'),
			'is_action' => true,
			'class' => 'elgg-button elgg-button-delete float-alt mrs',
			'confirm' => elgg_echo('ideas:delete:image'),
			));
}
$body3 .= elgg_view("input/file",array('name' => 'upload3'));
$body3 .= "</label></div>";

echo elgg_view_image_block($image3, $body3);

$image4 = elgg_view('ideas/thumbnail', array(
			'guid' => $post->guid,
			'imagenum' => 4,
			'size' => 'medium',
			'class' => 'ideas-form-image',
			'tu' => $tu
			));
$body4 = "<div><label>" . elgg_echo("ideas:uploadimage4");
if ($images[4]) {
	$body4 .= elgg_view('output/confirmlink', array(
			'href' => "action/ideas/delete_img?guid={$post->guid}&img=4",
			'text' => elgg_echo('delete'),
			'is_action' => true,
			'class' => 'elgg-button elgg-button-delete float-alt mrs',
			'confirm' => elgg_echo('ideas:delete:image'),
			));
}
$body4 .= elgg_view("input/file",array('name' => 'upload4'));
$body4 .= "</label></div>";

echo elgg_view_image_block($image4, $body4);

echo "<div><label>";
echo elgg_view('input/access', array('name' => 'access_id','value' => $vars['access_id']));
echo elgg_echo('access') . "<span class='elgg-subtext mlm'>" . elgg_echo("ideas:access:help") . "</span></label></div>";

echo "<div>";
// Terms checkbox and link
$termslink = elgg_view('output/url', array(
			'href' => "ideas/terms",
			'text' => elgg_echo('ideas:terms:title'),
			'class' => "elgg-lightbox",
			));
$termsaccept = elgg_echo("ideas:accept:terms", array($termslink));
echo "</div>";
echo "<input type='checkbox' name='accept_terms'><label>{$termsaccept}</label></p>";

echo "<div class='elgg-foot'>";
echo elgg_view('input/hidden', array('name' => 'container_guid', 'value' => elgg_get_page_owner_guid()));
echo elgg_view('input/hidden', array('name' => 'guid', 'value' => $vars['guid']));
echo elgg_view('input/submit', array('name' => 'submit', 'value' => elgg_echo('save')));
echo "</div>";

// Debug
//print_r(unserialize($post->images));
?>
<script type="text/javascript">
function textCounter(field,cntfield,maxlimit) {
	// if too long...trim it!
	if (field.value.length > maxlimit) {
		field.value = field.value.substring(0, maxlimit);
	} else {
		// otherwise, update 'characters left' counter
		cntfield.value = maxlimit - field.value.length;
	}
}
function acceptTerms() {
	error = 0;
	if(!(document.ideasForm.accept_terms.checked) && (error==0)) {		
		alert(elgg.echo('ideas:accept:terms:error'));
		document.ideasForm.accept_terms.focus();
		error = 1;		
	}
	if(error == 0) {
		document.ideasForm.submit();	
	}
}
</script>
