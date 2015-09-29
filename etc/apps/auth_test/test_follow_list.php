<?php

include 'ElggApiClient.php';

   $client = new ElggApiClient("http://social.routzi.com", "badb0afa36f54d2159e599a348886a7178b98533");
   $result = $client->obtainAuthToken('leo123', 'password123');
   if (!$result) {
       echo "Error in getting auth token!\n";
   }

   $params = array('object_guid' => 33);
   $result = $client->get('follow.list', $params);

   echo "result = $result\n";

   if (!$result) {
       echo "Error in listing follow!\n";
   }

?>

