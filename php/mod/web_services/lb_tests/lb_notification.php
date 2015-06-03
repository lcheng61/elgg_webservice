<?php

include 'ElggApiClient.php';

function lb_assert($condition, $message)
{
    if ($condition == true) {
        echo "Success: ".$message."\n";
    } else {
        echo "Failed: ".$message."\n";
        exit -1;
    }
}
function test_title($title)
{
    echo "***********************************************************\n";
    echo "*                    ".$title."                           *\n";
    echo "***********************************************************\n";
}

    $client = new ElggApiClient("http://social.routzi.com", "badb0afa36f54d2159e599a348886a7178b98533");
//    $client = new ElggApiClient("http://m.lovebeauty.me", "902a5f73385c0310936358c4d7d58b403fe2ce93");

    // user 1
    $username1 = "lbpush1";
    $password1 = "lbpush1";
    $email1 = "lbpush1@xxx.com";
    $name1 = "lbpush1";

    // user 2
    $username1b = "lbpush2";
    $password1b = "lbpush2";
    $email1b = "lbpush2@xxx.com";
    $name1b = "lbpush2";

// clean up 
    // login as a seller
    test_title("login as user 1");
    $result = $client->obtainAuthToken($username1, $password1);
    $params = array('username' => $username1);
    $result = $client->post('user.delete', $params);

    test_title("login as a user 2");
    $result = $client->obtainAuthToken($username1b, $password1b);
    $params = array('username' => $username1b);
    $result = $client->post('user.delete', $params);

//~

    // register user 1
    test_title("register a new user 1");
    $params = array('username' => $username1,
                   'password' => $password1,
                   'email' => $email1,
                   'name' => $name1,
                   'is_seller' => true,
                  );
    $result = $client->post('user.register', $params);
    $user_id_1 = $result->guid;
    lb_assert($result->success == true, "user 1 register");

    test_title("register a new user 2");
    $params = array('username' => $username1b,
                   'password' => $password1b,
                   'email' => $email1b,
                   'name' => $name1b,
                   'is_seller' => true,
                  );
    $result = $client->post('user.register', $params);
    $user_id_2 = $result->guid;
    lb_assert($result->success == true, "user 2 register b");

    // login as user 1
    test_title("login as a user 1");
    $result = $client->obtainAuthToken($username1, $password1);
    lb_assert($result, "login as a user 1");

    // post a product
    test_title("Post product - first seller");
    $options = '[{"key":"size","values": ["small","medium","large"]},{"key":"color","values": ["black","red","pink"]}]';
    // 1st seller -> 1st product
    $params = array('category' => 'lb_rewards test',
                   'title' => 'test reward product 1-1',
                   'quantity' => 100,
                   'price' => 10,
                   'description' => 'This is to test rewards product 1-1',
                   'is_affiliate' => 0,
                   'affiliate_product_id' => 0,
                   'affiliate_product_url' => '',
                   'is_archived' => 0,
                   'affiliate_syncon' => 0,
                   'options' => $options,
                  );
    $result = $client->post('product.post', $params);
    $product_id = $result->product_id;
    lb_assert($product_id, "Post a product and returns a product id");


/////////
    // post an idea
    test_title("post an idea");

   $message = '{   "tip_title": "Dermablend Professional- Tattoo Cover Up Makeup: Go Beyond The Cover",   "tip_thumbnail_image_url": "http://www.woman.at/_storage/asset/4150236/storage/womanat:key-visual/file/52817065/31266684.jpg",   "tip_pages": [     {       "tip_image_url": "http://cdn.maedchen.de/bilder/make-up-und-beauty-produkte-zum-schminken-fuer-maedchen-557x313-151005.jpg",       "tip_image_caption": "Step 1"     },     {       "tip_video_url": " http://youtu.be/Jwngbzv0gbY"     },     {       "tip_text": "wertyuiopxcvbnm, sdfghjkldfghjk"     },     {       "tip_image_local": "false" }   ],   "tip_notes": "Excepteur adipisicing tempor cupidatat exercitation nostrud aliquip enim cupidatat Lorem aute elit laboris enim magna. Ut incididunt ad anim aute ad officia deserunt sunt esse tempor ea qui magna quis. Duis aliqua duis incididunt voluptate incididunt esse consequat consectetur sit tempor. Nisi quis velit minim quis.\r\n",   "tip_tags": [     "makeup",     "eye",     "fashion"   ],   "category": "fashion" }';

    $params = array('message' => $message,
                  );
    $result = $client->post('ideas.post_tip', $params);
    $tip_id = $result->idea_id;
    lb_assert($result->tip_title, "user 1 posts an idea");


    for ($i = 0; $i < 10; $i ++) {
        sleep(3);
        echo $i." ";
    }
    printf("\n");

    // user 2 follows user 1
    test_title("login as a user 2");
    $result = $client->obtainAuthToken($username1b, $password1b);
    lb_assert($result, "login as a user 2");
//echo "\n".$result."\n";

    $params = array('friend' => $username1,
                  );
    $result = $client->post('user.friend.follow', $params);

//echo "\n".$result['result']."\n";
//echo "\n".$result->message."\n";
//echo lb_assert($result, "user 2 follows user 1");



    // user 2 comments user 1's product
    $params = array('guid' => $product_id,
                    'rate' => "4",
                    'text' => "good idea",
                    'type' => 1,
                  );
    $result = $client->post('blog.post_comment', $params);
echo "\n".$result."\n";

    // user 2 reviews user 1's idea
    $params = array('guid' => $tip_id,
                    'rate' => "4",
                    'text' => "good product",
                    'type' => 2,
                  );
    $result = $client->post('blog.post_comment', $params);
echo "\n".$result."\n";

    // user 2 sends message to user 1

    $params = array('receiver_id' => $user_id_1,
                    'subject' => "Test subject",
                    'body' => "Test body",
                    'send_to' => $username1
                  );
    $result = $client->post('message.send', $params);
echo "\n".$result."\n";

// Delete 2 users
/*
    // login as user 1
    test_title("login as a user 1");
    $result = $client->obtainAuthToken($username1, $password1);
    lb_assert($result, "login as a user 1");

    $params = array('username' => $username1);
    $result = $client->post('user.delete', $params);

    // login as user 2

    test_title("login as a user 2");
    $result = $client->obtainAuthToken($username1b, $password1b);
    lb_assert($result, "login as a user 2");

    $params = array('username' => $username1b);
    $result = $client->post('user.delete', $params);

    echo $result."\n";
*/

?>
