<?php
/**
 * Elgg Ideas Plugin
 * @package ideas
 */

// Translations
$yes = elgg_echo('option:yes');
$no = elgg_echo('option:no');

// Get settings
$customchoices = $vars['entity']->ideas_custom_choices;
$ideascategories = $vars['entity']->ideas_categories;

echo "<hr>";
echo '<table class="elgg-table-alt">';
echo '<tr><th>' . elgg_echo('ideas:settings:status') . '</th>';
echo '<th>' . elgg_echo('ideas:settings:desc') . '</th></tr>';
echo "<tr><td>";
echo elgg_view('input/dropdown', array(
                        'name' => 'params[ideas_max]',
                        'value' => $vars['entity']->ideas_max,
                        'options_values' => array(
						'0' => elgg_echo('ideas:unlimited'),
						'1' => '1',
						'2' => '2',
						'3' => '3',
						'4' => '4',
						'5' => '5',
						'10' => '10',
						'20' => '20',
						'30' => '30',
						),
			));

echo "</td><td>" . elgg_echo('ideas:max:posts') . "</td></tr>";
/*
echo "<tr><td>";
echo elgg_view('input/dropdown', array(
			'name' => 'params[ideas_type]',
			'value' => $vars['entity']->ideas_type,
			'options_values' => array(
						'no' => $no,
						'yes' => $yes
						)
			));
echo "</td><td>" . elgg_echo('ideas:type') . "</td></tr>";
*/
echo "<tr><td>";
echo elgg_view('input/dropdown', array(
			'name' => 'params[ideas_adminonly]',
			'value' => $vars['entity']->ideas_adminonly,
			'options_values' => array(
						'no' => $no,
						'yes' => $yes
						)
			));
echo "</td><td>" . elgg_echo('ideas:adminonly') . "</td></tr>";

echo "<tr><td>";
echo elgg_view('input/text', array(
			'name' => 'params[ideas_currency]',
			'class' => 'ideas-admin-input',
			'value' => $vars['entity']->ideas_currency,
			));
echo "</td><td>" . elgg_echo('ideas:currency') . "</td></tr>";

echo "<tr><td>";
echo elgg_view('input/dropdown', array(
			'name' => 'params[ideas_allowhtml]',
			'value' => $vars['entity']->ideas_allowhtml,
			'options_values' => array(
						'no' => $no,
						'yes' => $yes
						)
			));
echo "</td><td>" . elgg_echo('ideas:allowhtml') . "</td></tr>";

echo "<tr><td>";
echo elgg_view('input/text', array(
			'name' => 'params[ideas_numchars]',
			'class' => 'ideas-admin-input',
			'value' => $vars['entity']->ideas_numchars,
			));
echo "</td><td>" . elgg_echo('ideas:numchars') . "</td></tr>";

echo "<tr><td>";
echo elgg_view('input/dropdown', array(
			'name' => 'params[ideas_pmbutton]',
			'value' => $vars['entity']->ideas_pmbutton,
			'options_values' => array(
						'no' => $no,
						'yes' => $yes
						)
			));
echo "</td><td>" . elgg_echo('ideas:pmbutton') . "</td></tr>";

echo "<tr><td>";
echo elgg_view('input/dropdown', array(
			'name' => 'params[ideas_comments]',
			'value' => $vars['entity']->ideas_comments,
			'options_values' => array(
						'no' => $no,
						'yes' => $yes
						)
			));
echo "</td><td>" . elgg_echo('ideas:comments') . "</td></tr>";

echo "<tr><td>";
$month = elgg_echo('ideas:expire:month');
$months = elgg_echo('ideas:expire:months');
echo elgg_view('input/dropdown', array(
			'name' => 'params[ideas_expire]',
			'value' => $vars['entity']->ideas_expire,
                        'options_values' => array(
						'0' => elgg_echo('ideas:unlimited'),
						'1' => "1 $month",
						'2' => "2 $months",
						'3' => "3 $months",
						'4' => "4 $months",
						'5' => "5 $months",
						'10' => "10 $months",
						'12' => "12 $months",
						),
			));
echo "</td><td>" . elgg_echo('ideas:expire') . "</td></tr>";

echo "</table>";

echo "<br><br>";

echo "<h3>" . elgg_echo('ideas:categories') . "</h3><hr>";

	echo elgg_echo('ideas:categories:explanation');
	echo "<br><br>";
	echo elgg_echo('ideas:categories:settings:categories');
	echo elgg_view('input/tags',array('value' => $ideascategories, 'name' => 'params[ideas_categories]'));

echo "<br><br>";

echo "<h3>" . elgg_echo('ideas:custom') . "</h3><hr>";

echo elgg_echo('ideas:custom:activate');
echo elgg_view('input/dropdown', array(
			'name' => 'params[ideas_custom]',
			'value' => $vars['entity']->ideas_custom,
			'options_values' => array(
						'no' => $no,
						'yes' => $yes
						)
			));
echo "<br><br>";
echo elgg_echo('ideas:custom:choices');
echo "<br><br>";
echo elgg_echo('ideas:custom:settings');
echo elgg_view('input/tags',array('value' => $customchoices, 'name' => 'params[ideas_custom_choices]'));

