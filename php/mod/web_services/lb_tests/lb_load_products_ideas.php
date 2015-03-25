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

    $username1 = "fashionist2015";
    $password1 = "fashionist2015";
    $email1 = "fashionist2015@gmail.com";
    $name1 =  "fashionist2015";

    $username2 = "beautythinker2015";
    $password2 = "beautythinker2015";
    $email2 = "beautythinker2015@lovebeauty.me";
    $name2 = "beautythinker2015";

    // register a new seller
    test_title("register a new seller");
    $params = array('username' => $username1,
                   'password' => $password1,
                   'email' => $email1,
                   'name' => $name1,
                   'is_seller' => true,
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
   
    // post an affiliate product
    test_title("Post product");
    $params = array('category' => 'nails',
                   'title' => 'Bonita Essentials',
                   'quantity' => 1000,
                   'price' => 1.99,
                   'description' => "
With 100 shades to choose from, our 15ml size nail polish bottles have the most trendy and fun colors available for you! Not only do we offer a wide color selection, this is a great quality polish that goes on thick and is long-lasting. Fun Fact: This polish is chip resistant and durable. Also, it does not contain toluente, formaldehyde, or DBP.
",
                   'delivery_time' => "standard U.S. domestic shipping time",
                   'images' => array("","","","",""),
                   'is_affiliate' => 1,
                   'affiliate_product_id' => 900001,
                   'affiliate_product_url' => 'http://www.bonitacolors.com/product.php?id_product=13',
                   'affiliate_image' => 'http://www.bonitacolors.com/img/p/13-390-thickbox.jpg',
                   'is_archived' => 0,
                   'affiliate_syncon' => 0,
                  );
    $result = $client->post('product.post', $params);
    $product_id = $result->product_id;
    lb_assert($product_id, "Post a product and returns a product id");
    echo "product_id: ".$product_id."\n";


////////////

    // register a new thinker
    test_title("register a new thinker");
    $params = array('username' => $username2,
                   'password' => $password2,
                   'email' => $email2,
                   'name' => $name2,
                   'is_seller' => false,
                  );
    $result = $client->post('user.register', $params);
    $seller_id = $result->guid;
    lb_assert($result->success == true, "thinker register");

    // login as a thinker
    test_title("login as a thinker");
    $result = $client->obtainAuthToken($username1, $password1);
    lb_assert($result, "login as a thinker");

    // post an idea
    test_title("post an idea");

   $message = '{   "tip_title": "Bonita color nail polish",
                   "tip_thumbnail_image_url": "http://1.bp.blogspot.com/-zOHBR3dA1nA/U_mpVXE9wZI/AAAAAAAAOnA/l5via7OdyuY/s1600/wondrously-polished-bonita-colors-you-mint-a-lot-to-me-summer-nail-polish-collection-swatches-review-nail-art.jpg",
                   "tip_pages": [
                       {       
                           "tip_video_url": "https://www.youtube.com/watch?v=9ah0QqZLa84"
                       },     
                       {       
                           "tip_video_url": "https://www.youtube.com/watch?v=cQ8gca4ZmVY"
                       },     

                       {       
                           "tip_image_url": "http://www.sugarvampsonline.com/wp-content/uploads/2015/01/image12-e1421715152219.jpg",       
                           "tip_image_caption": "Available at Rite Aid for only less than 2 dollars (wow!), we were able to find a dozen color choices."
                       },     
                       {       
                           "tip_text": "We purchased this soft, creamy lilac from the I Flippin Love It collection called Caitlyns Kisses as well as two from the Greetings Earthlings collection called Penny For Your Thoughts and a gold glitter named I Could Care Less."
                       },     
                       {
                           "tip_image_local": "false"
                       }
                   ],
                   "tip_notes": "Penny is a metallic copper with hints of a silver swirl. The collections are great because they contain a variety of finishes and textures that allows you to be playful and mix n match. The brush glides on smooth, the bottle is full size at .04 oz and the price point is incredible for the quality. Only 1 coat was needed for the metallic, two for the cream (which is typical) and the glitter is completely up to you. I only did one coat in the pics shown.",
                   "tip_tags": [     "makeup",     "nails",     "fashion"   ],
                   "category": "nails",
                   "products_id": [    ""   ] 
               }';

$message = trim(preg_replace('/\s+/', ' ', $message));

echo $message."\n";

    $json = json_decode($message, true);
    $json['products_id'][0] = "$product_id";
    $message = json_encode($json);
echo "\n= idea message =\n";
echo $message;
echo "\n===\n";
    $params = array('message' => $message,
                  );
    $result = $client->post('ideas.post_tip', $params);

    $thinker_idea_id = $result->idea_id;
echo "idea_id = ".$thinker_idea_id."\n";
echo json_encode($result);
echo "\n----\n";

// Delete 2 users
/*
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
*/

?>
