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

/*
	$postfields = array();
	$postfields['username'] = 'admin';
	$postfields['password'] = 'iltg2000';
	$postfields['method'] = 'auth.gettoken';   
	$url = 'http://social.routzi.com/services/api/rest/json';
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 2);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_POST, 3);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $postfields);
	$result = curl_exec($curl);
        printf("result = $result\n");
	curl_close($curl);

  	$login_details = json_decode($result);
	if (isset($login_details->message)) {
   		echo $login_details->message;
	} else {
   		$token = $login_details->result;
	} 


    printf("token = $token\n");
    $test_command = 'http://social.routzi.com/services/api/rest/json/?method=get.Profiledata_login&auth_token='.$token;
    printf("test_command = $test_command");
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $test_command);
    $result = curl_exec($curl);
*/
?>

