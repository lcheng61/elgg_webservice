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

//    $client = new ElggApiClient("http://social.routzi.com", "badb0afa36f54d2159e599a348886a7178b98533");
    $client = new ElggApiClient("http://m.lovebeauty.me", "902a5f73385c0310936358c4d7d58b403fe2ce93");

    $username = "lbtest2";
    $password = "lbtest2";
    $email = "lbtest2@xxx.com";
    $name = "lbtest2";

    // register at IOS

    // register a new user
    test_title("Register a new user");
    $params = array('username' => $username,
                   'password' => $password,
                   'email' => $email,
                   'name' => $name,
                   'is_seller' => false,
                  );
    $result = $client->post('user.register', $params);
    echo lb_assert(($result && $result->success == 1), "IOS new register");

    // register on an existing user with same username and same email
    test_title("Register an existing user");
    $params = array('username' => $username,
                   'password' => $password,
                   'email' => $email,
                   'name' => $name,
                   'is_seller' => false,
                  );
    $result = $client->post('user.register', $params);
    echo lb_assert($result == false, "IOS existing register, Check Email");

    // register on an existing user with a different username and same email
    test_title("Register an existing user");
    $params = array('username' => $username."aaa",
                   'password' => $password,
                   'email' => $email,
                   'name' => $name,
                   'is_seller' => false,
                  );
    $result = $client->post('user.register', $params);
    echo lb_assert($result == false, "IOS existing register, Check Email");

    // seller portal check
    test_title("Seller portal sign in check");
    $params = array('username' => $username,
                   'password' => $password,
                   );
    $result = $client->post('auth.gettoken2', $params);
    echo lb_assert($result->is_seller == false, "Can't sign into seller portal");

    // ios sign in
    test_title("IOS sign in");
    $result = $client->obtainAuthToken($username, $password);
    echo lb_assert($result != false, "Get auth token (Login)");

    // email sign up using the same email address
    test_title("Email sign up using the registered email address");
    $params = array('email' => $email);
    $result = $client->post('user.register.email', $params);
    echo lb_assert($result->success == true, "");

    // delete user
    test_title("Delete user");
    $params = array('username' => $username);
    $result = $client->post('user.delete', $params);
    echo $result."\n";

/////////////////////////////////////////////////////
    // Seller Portal
    // register a new user
    test_title("Register a new user");
    $params = array('username' => $username,
                   'password' => $password,
                   'email' => $email,
                   'name' => $name,
                   'is_seller' => true,
                  );
    $result = $client->post('user.register', $params);
    echo lb_assert(($result && $result->success == 1), "Seller new register, Check Email");

    // seller portal check
    test_title("Seller portal sign in check");
    $params = array('username' => $username,
                   'password' => $password,
                   );
    $result = $client->post('auth.gettoken2', $params);
    echo lb_assert($result->is_seller == false, "Can't sign into seller portal");


    // ios sign in
    test_title("IOS sign in");
    $result = $client->obtainAuthToken($username, $password);
    echo lb_assert($result != false, "Get auth token (Login)");

    // Approve seller
    test_title("Approve seller");
    $params = array('username' => $username,
                  );
    $result = $client->post('user.set_seller', $params);
    echo lb_assert($result->seller_email_sent == true, "Approve seller");


    // Seller portal check
    test_title("Seller portal sign in check");
    $params = array('username' => $username,
                   'password' => $password,
                   );
    $result = $client->post('auth.gettoken2', $params);
    echo lb_assert($result->is_seller == true, "Now I can sign into seller portal");


    // Seller sign in
    test_title("Seller sign in");
    $result = $client->obtainAuthToken($username, $password);
    echo lb_assert($result != false, "Get auth token (Login)");

    // delete user
    test_title("Delete user");
    $params = array('username' => $username);
    $result = $client->post('user.delete', $params);
    echo $result."\n";

////////////////////////

/////////////////////////////////////////////////////
    echo "====================================";
    echo "Email subscription and then register";

/*
    // email sign up using the same email address
    test_title("Email sign up using the registered email address");
    $params = array('email' => $email);
    $result = $client->post('user.register.email', $params);
    echo lb_assert($result->success == true, "Email subscription");

    // contact-us message using the same email address
    test_title("Contact us using the registered email address");
    $params = array('email' => $email,
                    'msg' => "hello world",
                    'name' => "Name");
    $result = $client->post('user.register.email', $params);
    echo lb_assert($result->success == true, "contact-us message");

    // register a new user

    test_title("Register a new user");
    $params = array('username' => $username."abc",
                   'password' => $password,
                   'email' => $email,
                   'name' => $name,
                   'is_seller' => true,
                  );
    $result = $client->post('user.register', $params);
    echo lb_assert(($result == false), "Seller new register, Check Email");

    test_title("IOS sign in");
    $result = $client->obtainAuthToken($username."abc", $password);
    echo lb_assert($result != false, "Get auth token (Login)");

    // delete user
    test_title("Delete user");
    $params = array('username' => $email);
    $result = $client->post('user.delete', $params);
    echo $result."\n";
*/
    echo "============ALL PASS==============\n\n";
?>
