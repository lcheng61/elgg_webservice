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
   $result = $client->post('user.register', $params);
   echo $result;   

   $result = $client->obtainAuthToken($username, $password);
   if (!$result) {
       echo "Error in getting auth token!\n";
   }

   // post a product
   test_title("Post product");
   $params = array('category' => 'affiliate',
                   'title' => 'test affiliate product',
                   'quantity' => 100,
                   'price' => 19.99,
                   'description' => 'This is to test affiliate product',
                   'is_affiliate' => 1,
                   'affiliate_product_id' => 101,
                   'affiliate_product_url' => 'http://www.google.com/product_1',
                   'is_archived' => 0,
                   'affiliate_syncon' => 999998,
                  );
   $result = $client->post('product.post', $params);
   $product_id = $result->product_id;
   echo "product_id: ".$product_id."\n";

   // get product detail
   test_title("Product detail");
   $params = array('product_id' => $product_id);
   $result = $client->get('product.get_detail', $params);

   echo lb_assert($result->category == 'affiliate', "category");
   echo lb_assert($result->product_name == 'test affiliate product', "title");
   echo lb_assert($result->product_description == 'This is to test affiliate product', "description");
   echo lb_assert($result->quantity == 100, "quantity");
   echo lb_assert($result->product_price == 19.99, "price");

   echo lb_assert($result->affiliate->is_affiliate == 1, "is_affiliate");
   echo lb_assert($result->affiliate->affiliate_product_id == 101, "affiliate_product_id");
   echo lb_assert($result->affiliate->affiliate_product_url == 'http://www.google.com/product_1', "affiliate_product_url");
   echo lb_assert($result->affiliate->is_archived == 0, "is_archived");
   echo lb_assert($result->affiliate->affiliate_syncon == 999998, "affiliate_syncon");

   // list products
   test_title("List product");
   $params = array('limit' => 0);
   $result = $client->get('product.get_posts', $params);

   echo lb_assert($result->products[0]->product_category == 'affiliate', "category");
   echo lb_assert($result->products[0]->product_name == 'test affiliate product', "title");
   echo lb_assert($result->products[0]->quantity == 100, "quantity");
   echo lb_assert($result->products[0]->product_price == 19.99, "price");

   echo lb_assert($result->products[0]->affiliate->is_affiliate == 1, "is_affiliate");
   echo lb_assert($result->products[0]->affiliate->affiliate_product_id == 101, "affiliate_product_id");
   echo lb_assert($result->products[0]->affiliate->affiliate_product_url == 'http://www.google.com/product_1', "affiliate_product_url");
   echo lb_assert($result->products[0]->affiliate->is_archived == 0, "is_archived");
   echo lb_assert($result->products[0]->affiliate->affiliate_syncon == 999998, "affiliate_syncon");

   // search product
   test_title("Search product");
   $params = array('limit' => 0, 'query' => 'affiliate');
   $result = $client->get('product.search', $params);

   echo lb_assert($result->products[0]->product_category == 'affiliate', "category");
   echo lb_assert($result->products[0]->product_name == 'test affiliate product', "title");
   echo lb_assert($result->products[0]->product_price == 19.99, "price");

   echo lb_assert($result->products[0]->affiliate->is_affiliate == 1, "is_affiliate");
   echo lb_assert($result->products[0]->affiliate->affiliate_product_id == 101, "affiliate_product_id");
   echo lb_assert($result->products[0]->affiliate->affiliate_product_url == 'http://www.google.com/product_1', "affiliate_product_url");
   echo lb_assert($result->products[0]->affiliate->is_archived == 0, "is_archived");
   echo lb_assert($result->products[0]->affiliate->affiliate_syncon == 999998, "affiliate_syncon");

   // get affiliate sync time
   test_title("Get affiliate sync time");
   $params = array('product_id' => $product_id);
   $result = $client->get('product.get_affiliate_sync_time', $params);
   echo lb_assert($result->affiliate_syncon == 999998, "affiliate_syncon");

   test_title("Delete user");

   $params = array('username' => $username);
   $result = $client->post('user.delete', $params);
   echo $result."\n";

   echo "============ALL PASS==============\n\n";
?>
