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

    $username1 = "lbtest1";
    $password1 = "lbtest1";
    $email1 = "lbtest1@xxx.com";
    $name1 = "lbtest1";

// First clean up
    // login as a seller
    test_title("login as a seller");
    $result = $client->obtainAuthToken($username1, $password1);
    $params = array('username' => $username1);
    $result = $client->post('user.delete', $params);


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
    "min_free_shipping_limit": 30,
    "flat_cost": 5
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
    lb_assert($result->currency, "USD");

    $message = '
{
  "logo": "",
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
    "min_free_shipping_limit": 30,
    "flat_cost": 5
  },
  "currency": "USD",
  "customized_text": "Thank you.",
  "notes": "please note.",
  "return_policy": "No return, no refund."
}';

    test_title("set seller setting for user1 with null logo");
    $params = array('message' => $message,
                  );
    $result = $client->post('user.set_seller_setting', $params);
    lb_assert($result, "success");

    test_title("get seller setting for user1");
    $params = array();
    $result = $client->get('user.get_seller_setting', $params);
    lb_assert($result->currency, "USD");

    echo "$result->logo\n";

// Delete 1 user

    // login as a seller
    test_title("login as a seller");
    $result = $client->obtainAuthToken($username1, $password1);
    lb_assert($result, "login as a seller");

    $params = array('username' => $username1);
    $result = $client->post('user.delete', $params);

    echo $result."\n";

?>
