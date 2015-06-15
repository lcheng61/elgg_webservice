<?php

include 'ElggApiClient.php';


   $client = new ElggApiClient("http://social.routzi.com", "badb0afa36f54d2159e599a348886a7178b98533");

   $result = $client->obtainAuthToken('leo123', 'password123');
   if (!$result) {
       echo "Error in getting auth token!\n";
   }

   $blog['title'] = 'Blog Title';
   $blog['text'] = 'text of test blog post';
   $blog['excerpt'] = 'excerpt of test blog post';
   $blog['tags'] = 'tags1, tags1';
   $blog['access'] = 2;

   $params = array('username' => "admin",
                                   'title' => $blog['title'],
                                   'text' => $blog['text'],
                                   'excerpt' => $blog['excerpt'],
                                   'tags' => $blog['tags'],
                                   'access' => $blog['access'],
                                  );
   $result = $client->post('blog.save_post', $params);

   if (!$result) {
       echo "Error in saving post!\n";
   }
?>
