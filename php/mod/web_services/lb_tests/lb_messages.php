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

    $username2 = "lbtest2";
    $password2 = "lbtest2";
    $email2 = "lbtest2@xxx.com";
    $name2 = "lbtest2";

    // register user_a
    test_title("register user_a");
    $params = array('username' => $username1,
                   'password' => $password1,
                   'email' => $email1,
                   'name' => $name1,
                   'is_seller' => false,
                  );
    $result = $client->post('user.register', $params);
    $user_a_id = $result->guid;
    lb_assert($result->success == true, "user_a register");

    // login as user_a
    test_title("login as user_a");
    $result = $client->obtainAuthToken($username1, $password1);
    lb_assert($result, "login as user_a");


    // register user_b
    test_title("register user_b");
    $params = array('username' => $username2,
                   'password' => $password2,
                   'email' => $email2,
                   'name' => $name2,
                   'is_seller' => false,
                  );
    $result = $client->post('user.register', $params);
    $user_b_id = $result->guid;
    lb_assert($result->success == true, "user_b register");

    // login as user_b
    test_title("login as user_b");
    $result = $client->obtainAuthToken($username2, $password2);
    lb_assert($result, "login as user_b");

    // login as user_a
    test_title("login as user_a");
    $result = $client->obtainAuthToken($username1, $password1);
    lb_assert($result, "login as user_a");

    // user_a sends messages to user_b
    test_title("user_a sends 3 messages to user_b");
    for ($i = 0; $i < 3; $i ++) {
        $params = array('subject' => "hello subject $i",
                   'body' => "hello subject $i",
                   'send_to' => $username2,
                   'reply' => 0,
                  );
        $result = $client->post('message.send', $params);
        lb_assert($result, "user_a send message");
//        echo json_encode($result);
    }

    // login as user_b
    test_title("login as user_b");
    $result = $client->obtainAuthToken($username2, $password2);
    lb_assert($result, "login as user_b");

    // read messages
    $limit = 2;
    $offset = 0;
    test_title("user_b reads $limit messages");
    $params = array('limit' => $limit,
                   'offset' => $offset,
                  );
    $result = $client->get('messages.inbox', $params);
//    echo json_encode($result);
    lb_assert($result->total_number == $limit, "user_b read $limit messages");

    $offset += $limit;
    test_title("user_b reads leftover messages");
    $params = array('limit' => $limit,
                   'offset' => $offset,
                  );
    $result = $client->get('messages.inbox', $params);
//    echo json_encode($result);
    lb_assert($result->total_number == 1, "user_b read leftover 1 messages");

// Delete two users

    // login as user_a
    test_title("login as user_a");
    $result = $client->obtainAuthToken($username1, $password1);
    lb_assert($result, "login as user_a");

    $params = array('username' => $username1);
    $result = $client->post('user.delete', $params);

    // login as user_b
    test_title("login as a user_b");
    $result = $client->obtainAuthToken($username2, $password2);
    lb_assert($result, "login as user_b");

    $params = array('username' => $username2);
    $result = $client->post('user.delete', $params);

    echo $result."\n";

?>
