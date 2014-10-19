$body = elgg_list_entities(array(
    'type' => 'object',
    'subtype' => 'my_blog',
));

$body = elgg_view_layout('one_column', array('content' => $body));
echo elgg_view_page("All Site Blogs", $body);

function my_blog_page_handler($segments) {
    switch ($segments[0]) {
        case 'add':
           include elgg_get_plugins_path() . 'my_blog/pages/my_blog/add.php';
           break;

        case 'all':
        default:
           include elgg_get_plugins_path() . 'my_blog/pages/my_blog/all.php';
           break;
    }

    return true;
}

echo elgg_list_entities(array(
    'type' => 'object',
    'subtype' => 'my_blog',
    'owner_guid' => elgg_get_logged_in_user_guid()
));
