<?php

include 'ElggApiClient.php';

   $client = new ElggApiClient("http://social.routzi.com", "badb0afa36f54d2159e599a348886a7178b98533");
   $result = $client->obtainAuthToken('leo123', 'password123');
   if (!$result) {
       echo "Error in getting auth token!\n";
   }
   $params = array('subject' => 'test from api 3', 'body' => 'send message test', 'send_to' => 'admin');
//   $params = array();
   $result = $client->post('message.send', $params);

   if (!$result) {
       echo "Error in sending message!\n";
   }

?>

