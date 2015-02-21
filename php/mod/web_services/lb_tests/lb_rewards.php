<?php

include 'ElggApiClient.php';

   $client = new ElggApiClient("http://social.routzi.com", "badb0afa36f54d2159e599a348886a7178b98533");

   $username = "lbtest2";
   $password = "lbtest2";
   $email = "lbtest2@xxx.com";
   $name = "lbtest2";

   // register a new user
   $params = array('username' => $username,
                   'password' => $password,
                   'email' => $email,
                   'name' => $name,
                   'is_seller' => false,
                  );
   $result = $client->post('user.register', $params);
   echo $result;   

   $result = $client->obtainAuthToken($username, $password);
   if (!$result) {
       echo "Error in getting auth token!\n";
   }

   // get profile
   $params = array();
   $result = $client->get('user.get_profile', $params);
   echo $result->points."\n";

   if ($result->points != 0) {
       echo "Error to get 0 signup points.\n";
   }  else {
       echo "Pass 0 signup points.\n";
   }

   // post a tip and earn 0 points

   $message = '{   "tip_title": "Dermablend Professional- Tattoo Cover Up Makeup: Go Beyond The Cover",   "tip_thumbnail_image_url": "http://www.woman.at/_storage/asset/4150236/storage/womanat:key-visual/file/52817065/31266684.jpg",   "tip_pages": [     {       "tip_image_url": "http://cdn.maedchen.de/bilder/make-up-und-beauty-produkte-zum-schminken-fuer-maedchen-557x313-151005.jpg",       "tip_image_caption": "Step 1"     },     {       "tip_video_url": " http://youtu.be/Jwngbzv0gbY"     },     {       "tip_text": "wertyuiopxcvbnm, sdfghjkldfghjk"     },     {       "tip_image_local": "true" }   ],   "tip_notes": "Excepteur adipisicing tempor cupidatat exercitation nostrud aliquip enim cupidatat Lorem aute elit laboris enim magna. Ut incididunt ad anim aute ad officia deserunt sunt esse tempor ea qui magna quis. Duis aliqua duis incididunt voluptate incididunt esse consequat consectetur sit tempor. Nisi quis velit minim quis.\r\n",   "tip_tags": [     "makeup",     "eye",     "fashion"   ],   "category": "fashion",   "products_id": [     "2997",     "2589"   ] }';

   $params = array('message' => $message,
                  );
   $result = $client->post('ideas.post_tip', $params);
   echo $result->tip_title."\n";

   // get profile
   $params = array();
   $result = $client->get('user.get_profile', $params);
   echo $result->points."\n";

   if ($result->points != 0) {
       echo "Error to get 0 signup points.\n";
   }  else {
       echo "Pass 0 signup points.\n";
   }

   $params = array('username' => $username,
                  );
   $result = $client->post('user.delete', $params);
   echo $result."\n";
?>
