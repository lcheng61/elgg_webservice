<?php

include 'ElggApiClient.php';

   $client = new ElggApiClient("dev-lovebeauty.rhcloud.com", "badb0afa36f54d2159e599a348886a7178b98533");
   $result = $client->obtainAuthToken('leo123', 'leo123');
   if (!$result) {
       echo "Error in getting auth token!\n";
   }

   $blog['title'] = 'Dermablend Professional- Tattoo Cover Up Makeup: Go Beyond The Cover';
   $blog['tip_text'] = 'Visit http://www.gobeyondthecover.com to learn more';
   $blog['tip_tags'] = 'fashion, zombie';
   $blog['access'] = 2;

   $params = array('username' => "admin",
                   'title' => $blog['title'],
                   'tip_text' => $blog['tip_text'],
                   'tip_tags' => $blog['tip_tags'],
                   'tip_category' => 'fashion',
                   'tip_thumbnail_image_url' => 'http://www.woman.at/_storage/asset/4150236/storage/womanat:key-visual/file/52817065/31266684.jpg',
                   'tip_pages' => '1',
                   'tip_video_url' => 'https://www.youtube.com/watch?v=9mIBKifOOQQ',
                   'tip_image_url' => 'http://blog.muchmusic.com/wp-content/uploads/2011/10/blog-trending-zombieboy.jpg',
                   'tip_image_caption' => 'WATCH ZOMBIE BOY TRANSFORM!',
                   'tip_notes'         => 'Now Rick has landed a new gig as the spokes model for concealer brand Dermablend! To prove just how well the concealer works.',
                   'products'          => '317,',
                   'access' => $blog['access'],
                  );
   $result = $client->post('ideas.post_tip', $params);

   if (!$result) {
       echo "Error in saving post!\n";
   }

?>

