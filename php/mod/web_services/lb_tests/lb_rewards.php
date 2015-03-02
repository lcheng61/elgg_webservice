<?php

include 'ElggApiClient.php';

function lb_assert($condition, $message)
{
    if ($condition == true) {
        return "Success: ".$message."\n";
    } else {
        return "Failed: ".$message."\n";
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

    $username1 = "lbtest1";
    $password1 = "lbtest1";
    $email1 = "lbtest1@xxx.com";
    $name1 = "lbtest1";

    $username2 = "lbtest2";
    $password2 = "lbtest2";
    $email2 = "lbtest2@xxx.com";
    $name2 = "lbtest2";

    $username3 = "lbtest3";
    $password3 = "lbtest3";
    $email3 = "lbtest3@xxx.com";
    $name3 = "lbtest3";

    // register a new seller
    test_title("register a new seller");
    $params = array('username' => $username1,
                   'password' => $password1,
                   'email' => $email1,
                   'name' => $name1,
                   'is_seller' => false,
                  );
    $result = $client->post('user.register', $params);
    $seller_id = $result->guid;
    lb_assert($result->success == true, "seller register");

    // login as a seller
    test_title("login as a seller");
    $result = $client->obtainAuthToken($username1, $password1);
    lb_assert($result, "login as a seller");

    // set user1 as a seller
    test_title("set user1 as seller");
    $params = array('username' => $username1,
                  );
    $result = $client->post('user.set_seller', $params);
    lb_assert($result->seller_email_sent == true, "Set seller");
   
    // post a product
    test_title("Post product");
    $params = array('category' => 'lb_rewards test',
                   'title' => 'test reward product',
                   'quantity' => 100,
                   'price' => 100,
                   'description' => 'This is to test rewards product',
                   'is_affiliate' => 0,
                   'affiliate_product_id' => 0,
                   'affiliate_product_url' => '',
                   'is_archived' => 0,
                   'affiliate_syncon' => 0,
                  );
    $result = $client->post('product.post', $params);
    $product_id = $result->product_id;
    lb_assert($product_id, "Post a product and returns a product id");
    echo "product_id: ".$product_id."\n";

    // register a new thinker
    test_title("register a new thinker");
    $params = array('username' => $username2,
                   'password' => $password2,
                   'email' => $email2,
                   'name' => $name2,
                   'is_seller' => false,
                  );
    $result = $client->post('user.register', $params);
    $thinker_id = $result->guid;
    lb_assert($result->success == true, "thinker register");

    // login as a thinker
    test_title("login as a thinker");
    $result = $client->obtainAuthToken($username2, $password2);
    lb_assert($result, "login as a thinker");

    // get profile of thinker and check points
    $params = array();
    $result = $client->get('user.get_profile', $params);
    echo "thinker points: ".$result->points."\n";
    lb_assert($result->points == 0, "check thinker's initial commission points");

    // post an idea
    test_title("post an idea");

   $message = '{   "tip_title": "Dermablend Professional- Tattoo Cover Up Makeup: Go Beyond The Cover",   "tip_thumbnail_image_url": "http://www.woman.at/_storage/asset/4150236/storage/womanat:key-visual/file/52817065/31266684.jpg",   "tip_pages": [     {       "tip_image_url": "http://cdn.maedchen.de/bilder/make-up-und-beauty-produkte-zum-schminken-fuer-maedchen-557x313-151005.jpg",       "tip_image_caption": "Step 1"     },     {       "tip_video_url": " http://youtu.be/Jwngbzv0gbY"     },     {       "tip_text": "wertyuiopxcvbnm, sdfghjkldfghjk"     },     {       "tip_image_local": "true" }   ],   "tip_notes": "Excepteur adipisicing tempor cupidatat exercitation nostrud aliquip enim cupidatat Lorem aute elit laboris enim magna. Ut incididunt ad anim aute ad officia deserunt sunt esse tempor ea qui magna quis. Duis aliqua duis incididunt voluptate incididunt esse consequat consectetur sit tempor. Nisi quis velit minim quis.\r\n",   "tip_tags": [     "makeup",     "eye",     "fashion"   ],   "category": "fashion",   "products_id": [    "$product_id"   ] }';

    $json = json_decode($message, true);
    $json['products_id'] = $product_id;
    $message = json_encode($json);

    $params = array('message' => $message,
                  );
    $result = $client->post('ideas.post_tip', $params);
    $thinker_idea_id = $result->idea_id;
    lb_assert($result->tip_title, "thinker post an idea, Check idea_id");
    echo "idea_id = ".$thinker_idea_id."\n";

    // register a new buyer
    test_title("register a new buyer");
    $params = array('username' => $username3,
                   'password' => $password3,
                   'email' => $email3,
                   'name' => $name3,
                   'is_seller' => false,
                  );
    $result = $client->post('user.register', $params);
    $buyer_id = $result->guid;
    lb_assert($result->success == true, "buyer register");

    // login as a buyer
    test_title("login as a buyer");
    $result = $client->obtainAuthToken($username3, $password3);
    lb_assert($result, "login as a buyer");

    // add a new card
    test_title("Add a new card");
    $msg = '{"number":"4012888888881881","exp_month":"06","exp_year":"2016","cvc":"123","name":"test_2015","brand":"visa"}';
    $params = array('msg' => $msg);
    $result = $client->post('payment.stripe.card_add', $params);
    $card_id = $result->cards[0]->id;
    echo "card id: ".$card_id."\n";
    lb_assert($result->name == "test_2015", "buyer register, Check card_id");


    // buy a product
    $msg = '{"amount":10000,"currency":"usd","card":"card_1594RmDzelfnJcBJZG2JOtAM","description":"this is a test","coupon":"abcd","order_info":{"total_price":100,"total_shipping_cost":0,"total_tax":0,"coupon":"abcdefg","shipping_address":{"address_id":"7363","name":"My Home","addressline1":"736 S Mary AVE","addressline2":"","city ":"Sunnyvale","state":"CA","zipcode ":"94087","phone_number ":"4082188791","is_default":true},"sellers":[{"seller_id":234,"seller_name":"leo123","seller_avatar":"http://social.routzi.com/mod/profile/icondirect.php?lastcache=1416849407&joindate=1400171622&guid=42&size=small","product_cost":100,"shipping_cost":0,"tax":0,"subtotal":100,"products":[{"product_id":1445,"thinker_id":42,"thinker_idea_id":1514,"product_name":"Nail polishing","product_image_url":"http://www.woman.at/_storage/asset/4150236/storage/womanat:key-visual/file/52817065/31266684.jpg","product_price":50,"item_number":2,"shipping_code":"70","shipping_cost":10}]}]}}';

echo "seller_id = ".$seller_id."\n";
    $json = json_decode($msg, true);
    $json['card'] = $card_id;
    $json['order_info']['sellers'][0]['seller_id'] = $seller_id;
    $json['order_info']['sellers'][0]['products'][0]['product_id'] = $product_id;
    $json['order_info']['sellers'][0]['products'][0]['thinker_id'] = $thinker_id;
    $json['order_info']['sellers'][0]['products'][0]['thinker_idea_id'] = $thinker_idea_id;
    $msg = json_encode($json);
    echo $msg."\n";
    
    $params = array('msg' => $msg);
    $result = $client->post('payment.checkout_direct', $params);
    lb_assert($result->charged_user == $username3, "buyer direct checkout, check email");
            
    // login as a thinker
    test_title("login as a thinker");
    $result = $client->obtainAuthToken($username2, $password2);
    lb_assert($result, "login as a seller");

    // get profile of thinker and check points
    $params = array();
    $result = $client->get('user.get_profile', $params);
    echo "thinker points: ".$result->points."\n";
    lb_assert(($result->points == 100), "check thinker's 1% commission points");

    // check thinker order
    $params = array();
    $result = $client->get('payment.list.thinker_order', $params);
    echo "thinker points: ".$result['thinker'][0]->points."\n";


// Delete 3 users

    // login as a seller
    test_title("login as a seller");
    $result = $client->obtainAuthToken($username1, $password1);
    lb_assert($result, "login as a seller");

    $params = array('username' => $username1);
    $result = $client->post('user.delete', $params);

    // login as a thinker
    test_title("login as a thinker");
    $result = $client->obtainAuthToken($username2, $password2);
    lb_assert($result, "login as a thinker");

    $params = array('username' => $username2);
    $result = $client->post('user.delete', $params);

    // login as a buyer
    test_title("login as a buyer");
    $result = $client->obtainAuthToken($username3, $password3);
    lb_assert($result, "login as a buyer");

    $params = array('username' => $username3);
    $result = $client->post('user.delete', $params);

    echo $result."\n";

?>