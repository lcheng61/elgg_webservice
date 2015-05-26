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

    // seller 1
    $username1 = "lbtest1";
    $password1 = "lbtest1";
    $email1 = "lbtest1@xxx.com";
    $name1 = "lbtest1";

    // seller b
    $username1b = "lbtest1b";
    $password1b = "lbtest1b";
    $email1b = "lbtest1b@xxx.com";
    $name1b = "lbtest1b";

    // thinker
    $username2 = "lbtest2";
    $password2 = "lbtest2";
    $email2 = "lbtest2@xxx.com";
    $name2 = "lbtest2";

    // buyer
    $username3 = "lbtest3";
    $password3 = "lbtest3";
    $email3 = "lbtest3@xxx.com";
    $name3 = "lbtest3";

// clean up 
    // login as a seller
    test_title("login as a seller");
    $result = $client->obtainAuthToken($username1, $password1);
    $params = array('username' => $username1);
    $result = $client->post('user.delete', $params);

    test_title("login as a seller b");
    $result = $client->obtainAuthToken($username1b, $password1b);
    $params = array('username' => $username1b);
    $result = $client->post('user.delete', $params);

    // login as a thinker
    test_title("login as a thinker");
    $result = $client->obtainAuthToken($username2, $password2);
    $params = array('username' => $username2);
    $result = $client->post('user.delete', $params);

    // login as a buyer
    test_title("login as a buyer");
    $result = $client->obtainAuthToken($username3, $password3);
    $params = array('username' => $username3);
    $result = $client->post('user.delete', $params);

//~

    // register a new seller
    test_title("register a new seller");
    $params = array('username' => $username1,
                   'password' => $password1,
                   'email' => $email1,
                   'name' => $name1,
                   'is_seller' => false,
                  );
    $result = $client->post('user.register', $params);
    $seller_id_a = $result->guid;
    lb_assert($result->success == true, "seller register");

    test_title("register a new seller");
    $params = array('username' => $username1b,
                   'password' => $password1b,
                   'email' => $email1b,
                   'name' => $name1b,
                   'is_seller' => false,
                  );
    $result = $client->post('user.register', $params);
    $seller_id_b = $result->guid;
    lb_assert($result->success == true, "seller register b");

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

    $message = '
{
  "logo": "http://www.google.com/logo.jpg",
  "company": {
      "address_1": "890 xxx xxx",
      "address_2": "xxx",
      "city": "San Jose",
      "state": "CA",
      "zipcode": "98111",
      "phone": "1-800-xxx-xxxx"
  },
  "bill": {
       "address_1": "890 xxx xxx",
       "address_2": "xxx",
       "city": "San Jose",
       "state": "CA",
       "zipcode": "98111",
       "phone": "1-800-xxx-xxxx"
  },
  "shipping_policy": {
    "free_shipping_quantity_limit": 10,
    "free_shipping_cost_limit": 30,
    "shipping_fee": 5
  },
  "currency": "USD",
  "customized_text": "Thank you.",
  "notes": "please note.",
  "return_policy": "No return, no refund."
}';

    test_title("set seller setting for user1");
    $params = array('message' => $message,
                  );
    $result = $client->post('user.set_seller_setting', $params);
    lb_assert($result, "success");

    test_title("get seller setting for user1");
    $params = array();
    $result = $client->get('user.get_seller_setting', $params);
    $json = json_decode($result, true);
    lb_assert($json["currency"], "USD");
    lb_assert(($json['shipping_policy']['shipping_fee']==5), "shipping_fee");

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
    $product_id_a1 = $result->product_id;
    lb_assert($product_id_a1, "Post a product and returns a product id");

    // 1st seller -> 2nd product
    $params = array('category' => 'lb_rewards test',
                   'title' => 'test reward product 1-2',
                   'quantity' => 100,
                   'price' => 11,
                   'description' => 'This is to test rewards product 1-2',
                   'is_affiliate' => 0,
                   'affiliate_product_id' => 0,
                   'affiliate_product_url' => '',
                   'is_archived' => 0,
                   'affiliate_syncon' => 0,
                   'options' => $options,
                  );
    $result = $client->post('product.post', $params);
    $product_id_a2 = $result->product_id;
    lb_assert($product_id_a2, "Post a product and returns a product id");

    // login as a seller b
    test_title("login as a seller b");
    $result = $client->obtainAuthToken($username1b, $password1b);
    lb_assert($result, "login as a seller b");

    test_title("set user1 as seller b");
    $params = array('username' => $username1b,
                  );
    $result = $client->post('user.set_seller', $params);
    lb_assert($result->seller_email_sent == true, "Set seller");
   

    test_title("Post product b");
    $options = '[{"key":"size","values": ["small","medium","large"]},{"key":"color","values": ["black","red","pink"]}]';
    $params = array('category' => 'lb_rewards test',
                   'title' => 'test reward product 2-1',
                   'quantity' => 100,
                   'price' => 20,
                   'description' => 'This is to test rewards product 2-1',
                   'is_affiliate' => 0,
                   'affiliate_product_id' => 0,
                   'affiliate_product_url' => '',
                   'is_archived' => 0,
                   'affiliate_syncon' => 0,
                   'options' => $options,
                  );
    $result = $client->post('product.post', $params);
    $product_id_b1 = $result->product_id;
    lb_assert($product_id_b1, "Post a product b1 and returns a product id b1");

    $params = array('category' => 'lb_rewards test',
                   'title' => 'test reward product 2-2',
                   'quantity' => 100,
                   'price' => 21,
                   'description' => 'This is to test rewards product 2-2',
                   'is_affiliate' => 0,
                   'affiliate_product_id' => 0,
                   'affiliate_product_url' => '',
                   'is_archived' => 0,
                   'affiliate_syncon' => 0,
                   'options' => $options,
                  );
    $result = $client->post('product.post', $params);
    $product_id_b2 = $result->product_id;
    lb_assert($product_id_b2, "Post a product b2 and returns a product id");

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

   $message = '{   "tip_title": "Dermablend Professional- Tattoo Cover Up Makeup: Go Beyond The Cover",   "tip_thumbnail_image_url": "http://www.woman.at/_storage/asset/4150236/storage/womanat:key-visual/file/52817065/31266684.jpg",   "tip_pages": [     {       "tip_image_url": "http://cdn.maedchen.de/bilder/make-up-und-beauty-produkte-zum-schminken-fuer-maedchen-557x313-151005.jpg",       "tip_image_caption": "Step 1"     },     {       "tip_video_url": " http://youtu.be/Jwngbzv0gbY"     },     {       "tip_text": "wertyuiopxcvbnm, sdfghjkldfghjk"     },     {       "tip_image_local": "false" }   ],   "tip_notes": "Excepteur adipisicing tempor cupidatat exercitation nostrud aliquip enim cupidatat Lorem aute elit laboris enim magna. Ut incididunt ad anim aute ad officia deserunt sunt esse tempor ea qui magna quis. Duis aliqua duis incididunt voluptate incididunt esse consequat consectetur sit tempor. Nisi quis velit minim quis.\r\n",   "tip_tags": [     "makeup",     "eye",     "fashion"   ],   "category": "fashion",   "products_id": [    "$product_id_a1"   ] }';

    $json = json_decode($message, true);
    $json['products_id'][0] = $product_id_a1;
    $json['products_id'][1] = $product_id_a2;
    $json['products_id'][2] = $product_id_b1;
    $json['products_id'][3] = $product_id_b2;
    $message = json_encode($json);
    $params = array('message' => $message,
                  );
    $result = $client->post('ideas.post_tip', $params);
    $thinker_idea_id = $result->idea_id;
    lb_assert($result->tip_title, "thinker post an idea, Check idea_id");

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

    // Check products by idea - product.get_tips_by_product&product_id=xx
    $params = array('product_id' => $product_id_a1);
    $result = $client->get('product.get_tips_by_product', $params);
    lb_assert($result->total_number == 1, "check number of tips linked to the product");
    lb_assert($result->tips[0]->tip_id == $thinker_idea_id, "check tip id");

    // Check product detail which includes the number of tips    
    $params = array('product_id' => $product_id_a1);
    $result = $client->get('product.get_detail', $params);
    lb_assert($result->tips_number == 1, "check number of tips linked to the product");
    lb_assert($result->ideas[0]->id == $thinker_idea_id, "check tip id");
    $json_options = json_decode($result->product_options, true);

    lb_assert($result->free_shipping_quantity_limit == 10, "check free shipping quantity limit");
    lb_assert($result->free_shipping_cost_limit == 30, "check free shipping cost limit");
    lb_assert($result->shipping_fee == 5, "check free shipping fee");


    // add a new card
    test_title("Add a new card");
    $msg = '{"number":"4012888888881881","exp_month":"06","exp_year":"2016","cvc":"123","name":"test_2015","brand":"visa"}';
    $params = array('msg' => $msg);
    $result = $client->post('payment.stripe.card_add', $params);

    $card_id = $result->cards[0]->id;

    lb_assert($result->name == "test_2015", "buyer register, Check card_id");

    // buy a product
    test_title("Buy a product");
    $msg = '{"amount":10000,"currency":"usd","card":"card_1594RmDzelfnJcBJZG2JOtAM","description":"this is a test","coupon":"abcd","order_info":{"total_price":100,"total_shipping_cost":0,"total_tax":0,"coupon":"abcdefg","shipping_address":{"address_id":"7363","name":"My Home","addressline1":"736 S Mary AVE","addressline2":"","city ":"Sunnyvale","state":"CA","zipcode ":"94087","phone_number ":"4082188791","is_default":true},"sellers":[{"seller_id":234,"seller_name":"leo123","seller_avatar":"http://social.routzi.com/mod/profile/icondirect.php?lastcache=1416849407&joindate=1400171622&guid=42&size=small","product_cost":100,"shipping_cost":0,"tax":0,"subtotal":100,"products":[{"product_id":1445,"thinker_id":42,"thinker_idea_id":1514,"product_name":"Nail polishing","product_image_url":"http://www.woman.at/_storage/asset/4150236/storage/womanat:key-visual/file/52817065/31266684.jpg","product_price":50,"item_number":2,"shipping_code":"70","shipping_cost":10}]}]}}';

    $json = json_decode($msg, true);
    $json['card'] = $card_id;
    $json['order_info']['sellers'][0]['seller_id'] = $seller_id_a;

    $json['order_info']['sellers'][0]['products'][0]['product_name'] = "Nail polish";
    $json['order_info']['sellers'][0]['products'][0]['product_image_url'] = "http://www.woman.at/_storage/asset/4150236/storage/womanat:key-visual/file/52817065/31266684.jpg";
    $json['order_info']['sellers'][0]['products'][0]['product_price'] = 10;
    $json['order_info']['sellers'][0]['products'][0]['item_number'] = 2;
    $json['order_info']['sellers'][0]['products'][0]['shipping_code'] = "70";
    $json['order_info']['sellers'][0]['products'][0]['shipping_cost'] = 10;
    $json['order_info']['sellers'][0]['products'][0]['product_id'] = $product_id_a1;
    $json['order_info']['sellers'][0]['products'][0]['thinker_id'] = $thinker_id;
    $json['order_info']['sellers'][0]['products'][0]['thinker_idea_id'] = $thinker_idea_id;
    $json['order_info']['sellers'][0]['products'][0]['product_options'] = '[{"key":"size","value":"medium"},{"key":"color","value": "red"}]';

    $json['order_info']['sellers'][0]['products'][1]['product_name'] = "Nail polish";
    $json['order_info']['sellers'][0]['products'][1]['product_image_url'] = "http://www.woman.at/_storage/asset/4150236/storage/womanat:key-visual/file/52817065/31266684.jpg";
    $json['order_info']['sellers'][0]['products'][1]['product_price'] = 11;
    $json['order_info']['sellers'][0]['products'][1]['item_number'] = 2;
    $json['order_info']['sellers'][0]['products'][1]['shipping_code'] = "70";
    $json['order_info']['sellers'][0]['products'][1]['shipping_cost'] = 10;

    $json['order_info']['sellers'][0]['products'][1]['product_id'] = $product_id_a2;
    $json['order_info']['sellers'][0]['products'][1]['thinker_id'] = $thinker_id;
    $json['order_info']['sellers'][0]['products'][1]['thinker_idea_id'] = $thinker_idea_id;
    $json['order_info']['sellers'][0]['products'][1]['product_options'] = '[{"key":"size","value":"small"},{"key":"color","value": "red"}]';

    $json['order_info']['sellers'][1]['seller_name'] = "leo123";
    $json['order_info']['sellers'][1]['seller_avatar'] = "http://social.routzi.com/mod/profile/icondirect.php?lastcache=1416849407&joindate=1400171622&guid=42&size=small";
    $json['order_info']['sellers'][1]['product_cost'] = 100;
    $json['order_info']['sellers'][1]['shipping_cost'] = 0;
    $json['order_info']['sellers'][1]['tax'] = 0;
    $json['order_info']['sellers'][1]['subtotal'] = 100;
    $json['order_info']['sellers'][1]['seller_id'] = $seller_id_b;
    $json['order_info']['sellers'][1]['products'][0]['product_id'] = $product_id_b1;
    $json['order_info']['sellers'][1]['products'][0]['thinker_id'] = $thinker_id;
    $json['order_info']['sellers'][1]['products'][0]['thinker_idea_id'] = $thinker_idea_id;
    $json['order_info']['sellers'][1]['products'][0]['product_options'] = '[{"key":"size","value":"medium"},{"key":"color","value": "red"}]';
    $json['order_info']['sellers'][1]['products'][0]['product_name'] = "Nail polish";
    $json['order_info']['sellers'][1]['products'][0]['product_image_url'] = "http://www.woman.at/_storage/asset/4150236/storage/womanat:key-visual/file/52817065/31266684.jpg";
    $json['order_info']['sellers'][1]['products'][0]['product_price'] = 20;
    $json['order_info']['sellers'][1]['products'][0]['item_number'] = 2;
    $json['order_info']['sellers'][1]['products'][0]['shipping_code'] = "70";
    $json['order_info']['sellers'][1]['products'][0]['shipping_cost'] = 10;

    $json['order_info']['sellers'][1]['products'][1]['product_id'] = $product_id_b2;
    $json['order_info']['sellers'][1]['products'][1]['thinker_id'] = $thinker_id;
    $json['order_info']['sellers'][1]['products'][1]['thinker_idea_id'] = $thinker_idea_id;
    $json['order_info']['sellers'][1]['products'][1]['product_options'] = '[{"key":"size","value":"small"},{"key":"color","value": "red"}]';
    $json['order_info']['sellers'][1]['products'][1]['product_name'] = "Nail polish";
    $json['order_info']['sellers'][1]['products'][1]['product_image_url'] = "http://www.woman.at/_storage/asset/4150236/storage/womanat:key-visual/file/52817065/31266684.jpg";
    $json['order_info']['sellers'][1]['products'][1]['product_price'] = 21;
    $json['order_info']['sellers'][1]['products'][1]['item_number'] = 2;
    $json['order_info']['sellers'][1]['products'][1]['shipping_code'] = "70";
    $json['order_info']['sellers'][1]['products'][1]['shipping_cost'] = 10;

    $msg = json_encode($json);
    
    $params = array('msg' => $msg);
    $result = $client->post('payment.checkout_direct', $params);

//

    lb_assert($result->charged_user == $username3, "buyer direct checkout, check email");

    $order_id = $result->order_id;

    // thinker order detail
//    $thinker_order_id = json_encode($result->seller_info->products[0]->thinker_info[0]->order_id);
    $thinker_order_id = json_encode($result->thinker_order[0]);

    // check buyer's get_shipping address
    test_title("Check buyer's shipping address");
    $params = array();
    $result = $client->get('payment.get_shipping_address', $params);
//    echo "\n====\n";
//    echo json_encode($result);
//    echo "\n====\n";

    // check buyer order history
    test_title("Check buyer's order history");
    $params = array();
    $result = $client->get('payment.list.buyer_order', $params);

//echo $order_id;
//echo json_encode($result);

    lb_assert($result->msg[0]->order_info->order_guid == $order_id, "transaction id matches order id");
//    echo "\n====\n";
//    echo json_encode($result);
//    echo "\n====\n";

    // login as a thinker
    test_title("login as a thinker");
    $result = $client->obtainAuthToken($username2, $password2);
    lb_assert($result, "login as a thinker");

    // get profile of thinker and check points
    $params = array();
    $result = $client->get('user.get_profile', $params);
    echo "thinker points: ".$result->points."\n";
    lb_assert(($result->points == 124), "check thinker's 1% commission points");

    // check thinker order
    $params = array();
    $result = $client->get('payment.list.thinker_order', $params);

//    echo json_encode($result);
//    echo "thinker points: ".$result['thinker'][0]->points."\n";

//echo "\n====thinker_order_id=========\n";
//echo $thinker_order_id."\n";
//echo "===============================\n";
    $params = array('id' => $thinker_order_id);
    $result = $client->get('payment.detail.thinker_order', $params);
//    echo json_encode($result)."\n";
//    echo "\n";

    // login as a seller a
    test_title("login as a seller");
    $result = $client->obtainAuthToken($username1, $password1);
    lb_assert($result, "login as a seller");

    // check seller order
    test_title("check seller order");
    $params = array();
    $result = $client->get('payment.list.seller_order', $params);

    // check seller order detail
    $seller_order_id_a = json_encode($result->seller_order[0]->order_guid);

    // option of 2 products from seller a
    $params = array('order_id' => $seller_order_id_a);
    $result = $client->get('payment.detail.seller_order', $params);

    $product_options = $result->products_msg[0]->product_options;
    $product_options_json = json_decode($product_options, true);
    echo lb_assert($product_options_json[0]['value'] == "medium", "check product option");

    $product_options = $result->products_msg[1]->product_options;
    $product_options_json = json_decode($product_options, true);
    echo lb_assert($product_options_json[0]['value'] == "small", "check product option");



    // login as a seller b
    test_title("login as a seller b");
    $result = $client->obtainAuthToken($username1b, $password1b);
    lb_assert($result, "login as a seller b");

    // check seller order
    test_title("check seller order");
    $params = array();
    $result = $client->get('payment.list.seller_order', $params);

    // check seller order detail
    $seller_order_id_b = json_encode($result->seller_order[0]->order_guid);

    // option of 2 products from seller b
    $params = array('order_id' => $seller_order_id_b);
    $result = $client->get('payment.detail.seller_order', $params);

    $product_options = $result->products_msg[0]->product_options;
    $product_options_json = json_decode($product_options, true);
    echo lb_assert($product_options_json[0]['value'] == "medium", "check product option");

    $product_options = $result->products_msg[1]->product_options;
    $product_options_json = json_decode($product_options, true);
    echo lb_assert($product_options_json[0]['value'] == "small", "check product option");

// Delete 4 users

    // login as a seller
    test_title("login as a seller");
    $result = $client->obtainAuthToken($username1, $password1);
    lb_assert($result, "login as a seller");

    $params = array('username' => $username1);
    $result = $client->post('user.delete', $params);

    // login as a seller b
    test_title("login as a seller b");
    $result = $client->obtainAuthToken($username1b, $password1b);
    lb_assert($result, "login as a seller b");

    $params = array('username' => $username1b);
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
