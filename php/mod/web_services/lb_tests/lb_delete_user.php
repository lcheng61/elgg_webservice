<?php

include 'ElggApiClient.php';

function lb_assert($condition, $message)
{
    if ($condition == true) {
        echo "Success: ".$message."\n";
    } else {
        echo "Failed: ".$message."\n";
//        exit -1;
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

    $username3 = "lbtest3";
    $password3 = "lbtest3";
    $email3 = "lbtest3@xxx.com";
    $name3 = "lbtest3";


// Delete 3 users

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

    // login as user_c
    test_title("login as a user_c");
    $result = $client->obtainAuthToken($username3, $password3);
    lb_assert($result, "login as user_c");

    $params = array('username' => $username3);
    $result = $client->post('user.delete', $params);

    echo $result."\n";

?>
