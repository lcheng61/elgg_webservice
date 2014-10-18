<?php

include 'ElggApiClient.php';

   $client = new ElggApiClient("http://social.routzi.com", "badb0afa36f54d2159e599a348886a7178b98533");
   $result = $client->obtainAuthToken('leo123', 'password123');
   if (!$result) {
       echo "Error in getting auth token!\n";
   }

   $params = array('entity_guid' => 274);
   $result = $client->post('likes.add', $params);

   if (!$result) {
       echo "Error in adding like!\n";
   }

   $likes_count = $client->get('likes.count', $params);
   echo ("likes_count = ".$likes_count."\n");
?>

