<?php

include 'ElggApiClient.php';

function lb_assert($condition, $message)
{
    if ($condition == true) {
        return "Success: ".$message."\n";
    } else {
        return "Failed: ".$message." condition = $condition"."\n";
        exit(-1);
    }
}
function test_title($title)
{
    echo "***********************************************************\n";
    echo "*                    ".$title."                           *\n";
    echo "***********************************************************\n";
}

   $client = new ElggApiClient("http://social.routzi.com", "badb0afa36f54d2159e599a348886a7178b98533");

   $username = "lbtest2";
   $password = "lbtest2";
   $email = "lbtest2@xxx.com";
   $name = "lbtest2";

   // register a new user
   test_title("Register");
   $params = array('username' => $username,
                   'password' => $password,
                   'email' => $email,
                   'name' => $name,
                   'is_seller' => false,
                  );
   try {
       $result = $client->post('user.register', $params);
   } catch (Exception $e) {
       echo 'Error: Caught exception: ', $e->getMessage(), "\n";
       exit(-1);
   }
   if ($result && ($result->success == 1)) {
       echo "PASS: user register. Success = ".$result->success."\n";
   } else {
       echo "Failed: user register. Exit"."\n";
       exit(-1);
   }

   $result = $client->obtainAuthToken($username, $password);
   if (!$result) {
       echo "Error in getting auth token!\n";
   }

   // post a product
   test_title("Post product");
   $params = array('category' => 'hot',
                   'title' => 'test recommend product',
                   'quantity' => 100,
                   'price' => 19.99,
                   'description' => 'This is to test recommend product',
                  );
   $result = $client->post('product.post', $params);
   $product_id = $result->product_id;
   echo "product_id: ".$product_id."\n";

   // get product detail
   test_title("Product detail");
   $params = array('product_id' => $product_id);
   $result = $client->get('product.get_detail', $params);

   echo lb_assert($result->category == 'hot', "category");
   echo lb_assert($result->product_name == 'test recommend product', "title");
   echo lb_assert($result->product_description == 'This is to test recommend product', "description");
   echo lb_assert($result->quantity == 100, "quantity");
   echo lb_assert($result->product_price == 19.99, "price");

   // set admin flag for the user
   $params = array('username' => "lbtest2");
   $result = $client->post('user.set_admin', $params);

   // Set recommendation flag
   test_title("Set recommend flag");
   $params = array('product_id' => $product_id, /*'is_recommend' => 1*/);
   $result = $client->post('product.recommend_set', $params);
   echo lb_assert($result->is_recommend == 1, "is_recommended");

   // List recommended product

   test_title("List recommended product");
   $params = array('limit' => 0, 'category' => "hot");
   $result = $client->get('product.recommend_list', $params);
   echo lb_assert($result->total_number == 1, "list recommend of one category and check total number");

   $params = array('limit' => 0, 'category' => "all");
   $result = $client->get('product.recommend_list', $params);
   echo lb_assert($result->products[0]->is_recommend == 1, "list recommend of all category and check is_recommend flag");

   // Reset recommendation flag
   test_title("Reset recommend flag");
   $params = array('product_id' => $product_id, 'is_recommend' => 0);
   $result = $client->post('product.recommend_set', $params);
   echo lb_assert($result->is_recommend == 0, "is_recommended");

   $params = array('limit' => 0, 'category' => "all");
   $result = $client->get('product.recommend_list', $params);
   echo lb_assert($result->total_number == 0, "list recommend of one category and check total number should be zero");

   test_title("Delete user");
   $params = array('username' => $username);
   $result = $client->post('user.delete', $params);
   echo $result."\n";

   echo "============ALL PASS==============\n\n";
?>
