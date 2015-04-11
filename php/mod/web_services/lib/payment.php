<?php
/*
 * Payment using Stripe gateway
 *
 *
 * @package Webservice
 * @author Liang Cheng
 */

/*
 * Save card info
 */

function stripe_card_add($token, $msg)
{
   $card_test = 0;
   $card_info = array();
   if (strlen($msg) > 0) {
       // it should be an update of the logged in user
       $card_test = 1;
       $json = json_decode($msg, true);
       $card_info['number'] = $json['number'];
       $card_info['brand'] = $json['brand'];
       $card_info['exp_month'] = $json['exp_month'];
       $card_info['exp_year'] = $json['exp_year'];
       $card_info['cvc'] = $json['cvc'];
       $card_info['name'] = $json['name'];
       $card_info['address_line1'] = $json['address_line1'];
       $card_info['address_line2'] = $json['address_line2'];
       $card_info['address_city'] = $json['address_city'];
       $card_info['address_zip'] = $json['address_zip'];
       $card_info['address_state'] = $json['address_state'];
       $card_info['address_country'] = $json['address_country'];
   }

    $user = elgg_get_logged_in_user_entity();
    if (!$user) {
        throw new InvalidParameterException('registration:usernamenotvalid');
    }

    $stripe = new StripeClient();
    if ($card_test == 1) {
        $card = $stripe->createCard($user->guid, $card_info);
        $return['name'] = $card->name;
    } else {
        $card = $stripe->createCard($user->guid, $token);
        $return['token'] = $token;
    }

    if ($card) { // card is added successfully
	system_message(elgg_echo('stripe:cards:add:success'));

	// set the new card as default card
	$set_default = $stripe->setDefaultCard($user->guid, $card->id);

	if (!strlen($set_default)) {
	    register_error(elgg_echo('stripe:cards:make_default:error'));
            $return['error'] = $stripe->showErrors();
            throw new InvalidParameterException('stripe:cards:make_default:error');
        }

        $return['message'] = elgg_echo('stripe:cards:add:success');
	$return['id'] = $card->id;
        $return['label'] = "{$card->brand}-{$card->last4} ({$card->exp_month} / {$card->exp_year})";
        $return['last4'] = $card->last4;

/* delete all cards, for debugging
        $cards = $stripe->getCards($user->guid, 100);
        foreach ($cards['data'] as $key => $value) {
	    $return["delete_card"][] = $stripe->deleteCard($user->guid, $value['id']);
        }
*/

        $cards = $stripe->getCards($user->guid, 100);
        foreach ($cards['data'] as $key => $value) {
            $return["cards"][] = json_decode($value, true);
        }

        $return['user'] = $user->username;

    } else {
	register_error(elgg_echo('stripe:cards:add:error'));
	$return['error_msg'] = $stripe->showErrors();
	$return['card_info'] = $card_info;

        throw new InvalidParameterException('stripe:cards:add:error');
    }
    return $return;
}

expose_function('payment.stripe.card_add',
                "stripe_card_add",
                array(
                      'token' => array ('type' => 'string', 'required' => true, 'default' => ''),
                      'msg' => array ('type' => 'string', 'required' => false, 'default' => null),
                    ),
                "add a card for a user",
                'POST',
                true,
                true);

/*
 * Remove card info
 */

function stripe_card_remove($card_id)
{
    $user = elgg_get_logged_in_user_entity();
    if (!$user) {
        throw new InvalidParameterException('registration:usernamenotvalid');
    }
    if (!elgg_instanceof($user) || !$user->canEdit()) {
        register_error(elgg_echo('stripe:access_error'));
        throw new InvalidParameterException('registration:cannot_edit');
    }

    $stripe = new StripeClient();
    if ($stripe->deleteCard($user->guid, $card_id)) {
        $return["result"] = elgg_echo('stripe:cards:remove:success');
    } else {
        $stripe->showErrors();
        throw new InvalidParameterException('stripe:cards:remove:error');
    }
    $cards = $stripe->getCards($user->guid, 100);
    foreach ($cards['data'] as $key => $value) {
        $return["cards"][] = json_decode($value, true);
    }

    return $return;
}

expose_function('payment.stripe.card_remove',
                "stripe_card_remove",
                array(
                      'card_id' => array ('type' => 'string', 'required' => true, 'default' => ""),
                    ),
                "remove a card for the current user",
                'POST',
                true,
                true);

function stripe_card_set_default($card_id)
{
    $user = elgg_get_logged_in_user_entity();
    if (!$user) {
        throw new InvalidParameterException('registration:usernamenotvalid');
    }
    if (!elgg_instanceof($user) || !$user->canEdit()) {
        register_error(elgg_echo('stripe:access_error'));
        throw new InvalidParameterException('registration:cannot_edit');
    }
    $return['user'] = $user->username;

    $stripe = new StripeClient();
    if ($stripe->setDefaultCard($user->guid, $card_id)) {
        $return = elgg_echo('stripe:cards:make_default:success');
    } else {
        register_error(elgg_echo('stripe:cards:make_default:error'));
        $return['error'] = $stripe->showErrors();
//        throw new InvalidParameterException('stripe:cards:make_default:error');
    }
    return $return;
}
expose_function('payment.stripe.card_set_default',
                "stripe_card_set_default",
                array(
                      'card_id' => array ('type' => 'string', 'required' => true, 'default' => ""),
                    ),
                "set a card as default",
                'POST',
                true,
                true);

function stripe_card_get_all($limit = 10)
{
    $user = elgg_get_logged_in_user_entity();
    if (!$user) {
        throw new InvalidParameterException('registration:usernamenotvalid');
    }
    if (!elgg_instanceof($user) || !$user->canEdit()) {
        register_error(elgg_echo('stripe:access_error'));
        throw new InvalidParameterException('registration:cannot_edit');
    }

    $stripe = new StripeClient();

    $cards = $stripe->getCards($user->guid, $limit);
    foreach ($cards['data'] as $key => $value) {
        $return["cards"][] = json_decode($value, true);
    }

    $return['user'] = $user->username;
    return $return;

    if (!$return) {
        register_error(elgg_echo('stripe:cards:get_all:error'));
        $return = $stripe->showErrors();
        throw new InvalidParameterException('stripe:cards:get_all:error');
    }
    return $return;
}
expose_function('payment.stripe.card_get_all',
                "stripe_card_get_all",
                array(
                      'limit' => array ('type' => 'int', 'required' => false, 'default' => 10),
                    ),
                "get all cards of the current logged in user",
                'GET',
                true,
                true);

function stripe_card_get_default()
{
    $user = elgg_get_logged_in_user_entity();
    if (!$user) {
        throw new InvalidParameterException('registration:usernamenotvalid');
    }
    if (!elgg_instanceof($user) || !$user->canEdit()) {
        register_error(elgg_echo('stripe:access_error'));
        throw new InvalidParameterException('registration:cannot_edit');
    }

    $stripe = new StripeClient();
//    $return = $stripe->getDefaultCard($user->guid);
//    $return = serialize($stripe->getDefaultCard($user->guid));
    if (!$return) {
        register_error(elgg_echo('stripe:cards:get_default:error'));
        $return = $stripe->showErrors();
        throw new InvalidParameterException('stripe:cards:get_default:error');
    }
    return $return;
}
expose_function('payment.stripe.card_get_default',
                "stripe_card_get_default",
                array(
                    ),
                "get default card of the current logged in user",
                'POST',
                true,
                true);

function pay_get_shipping_address()
{
    $user = elgg_get_logged_in_user_entity();
    if (!$user) {
        throw new InvalidParameterException('registration:usernamenotvalid');
    }

    $return = json_decode($user->shipping_address, true);
    return $return;
}
expose_function('payment.get_shipping_address',
                "pay_get_shipping_address",
                array(
                    ),
                "get the latest shipping address of logged in user",
                'GET',
                true,
                true);

/*
 * Directly check out the order and save the order.
 * It doesn't use "shopping basket"
 *
 * It will generate "order" object after the checkout is successful
 */

function pay_checkout_direct($msg)
{
    if (strlen($msg) == 0) {
        throw new InvalidParameterException('payment:charge:message:wrong');
    }
    $date = date_create();
    $time_friendly = date_format($date, 'Y-m-d H:i:s');
    $timestamp = date_format($date, 'U');

    $json = json_decode($msg, true);
    $order_info['amount'] = $json['amount'];
    $order_info['currency'] = $json['currency'];
    $order_info['card'] = $json['card'];
    $order_info['description'] = $json['description'];
    $order_info['shipping_address'] = $json['order_info']['shipping_address'];
  
    $order_info['shipping_method'] = $json['order_info']['shipping_method'];
    $order_info['coupon'] = $json['order_info']['coupon'];

    $sellers = $json['order_info']['sellers'];

    $user = elgg_get_logged_in_user_entity();
    if (!$user) {
        throw new InvalidParameterException('registration:usernamenotvalid');
    }

    $points = intval($json['points']);
    if ($user->points < $points) {
        throw new InvalidParameterException('User points not sufficient');
    }
/* We don't support using points to buy
    if ($points > 0) {
        $user->points = $user->points - $points;
        if ($user->points < 0) {
            $user->points = 0;
        }
    }
*/
    $user->save();

    // saving shipping address to user profile
    $my_address = json_encode($json['order_info']['shipping_address']);
    $user->shipping_address = $my_address;
    if (!$user->save()) {
        $return['address_copied'] = false;
    } else {
        $return['address_copied'] = true;
    }
    $stripe = new StripeClient();
    $customer = new StripeCustomer($user->guid);

    if (strlen($order_info['card']) == 0) {
        // Get the customer info
	if (!$customer->getCustomerId()) {
            throw new InvalidParameterException('registration:stripe_no_card');
	}
    } else {
        // check if the card has been added.
        $cards = $stripe->getCards($user->guid, 100);
  	$found = 0;
        foreach ($cards['data'] as $key => $value) {
            $card_json = json_decode($value, true);
            if ($card_json['id'] == $order_info['card']) {
	        $found = 1;
	    }
        }
	if (!$found) {
            $card = $stripe->createCard($user->guid, $order_info['card']);
            if (!$card) {
                throw new InvalidParameterException('stripe:cards:add:error');
            }
     	    $set_default = $stripe->setDefaultCard($user->guid, $card->id);
	    if (!strlen($set_default)) {
	        register_error(elgg_echo('stripe:cards:make_default:error'));
                $return['error'] = $stripe->showErrors();
                throw new InvalidParameterException('stripe:cards:make_default:error');
            }
        }
    }
    // check if card_id was actually a token. If yes then we need to change it to the default card_id

    $order_info['card'] = $card->id;

    // Now charge to the $customer->getCustomerId();
    $charge = $stripe->createCharge($customer->getCustomerId(), $order_info);
    if (!$charge) {
        throw new InvalidParameterException('stripe:cards:charge:error');
    }
    
    $return['charged_user'] = $user->username;
    $return['charged_amount'] = $order_info['amount'];

    // Create the buyer pay order object
    $item = new ElggObject();
    $item->type = 'object';
    $item->subtype = 'buyer_order';

    $item->object_guid = $charge['id'];
    $card = $charge['card'];
//    $item->charge_card_name = "{$user->username}-{$card['brand']}-{$card['last4']}-{$card['exp_month']}-{$card['exp_year']}";
    $item->charge_card_name = $card['last4'];

    $item->time_friendly = $time_friendly;
    $item->timestamp = $timestamp;

    $item->msg = $msg;
    $item->status = "Paid";
    $item->shipping_vendor = "None";
    $item->tracking_number = "None";
    $item->shipping_speed = "None";

    if($item->save()){
        $return['card_name'] = $item->charge_card_name;
	$return['order_id'] = $charge['id']; //$item->guid;
	$return['order_id_lb'] = $item->guid;
        $return['content'] = elgg_echo("pay:charge:order:saved");
	$return['buyer_email'] = $user->email;
        // send email, format it later

	foreach ($sellers as $key => $value) {
            $products = $value['products'];
            $seller = get_user_by_username($value['seller_name']);
            $seller_info['seller_name'] = $seller->username;
            $seller_info['seller_email'] = $seller->email;
     	    foreach ($products as $product_key => $product_value) {

	        $product = get_entity($product_value['product_id']);
                if (!$product) {
		    continue;
                }
                // decrease the product quantity
                $product->quantity -= $product_value['item_number'];
                if ($product->quantity < 0) {
                    throw new InvalidParameterException(elgg_echo("pay:charge:product:outofstock"));
                }
                $product->sold_count += $product_value['item_number'];
                $product->save();

                // create a seller product selling order object
		$seller_order = new ElggObject();
                $seller_order->type = 'object';
                $seller_order->subtype = "seller_order";
                $seller_order->access_id = ACCESS_LOGGED_IN;
                $seller_order->seller_guid = $value['seller_id'];
                $seller_order->product_guid = $product_value['product_id'];
		$seller_order->coupon = $order_info['coupon'];

		$seller_order->product_name = $product_value['product_name'];
		$seller_order->product_image_url = $product_value['product_image_url'];
		$seller_order->product_price = $product_value['product_price'];
		$seller_order->product_quantity = $product_value['item_number'];
		$seller_order->shipping_code = $product_value['shipping_code'];
		$seller_order->shipping_cost = $product_value['shipping_cost'];
		$seller_order->shipping_address = json_encode($order_info['shipping_address']);

		$seller_order->charge_card_name = $item->charge_card_name;

// newly added on 1/16
                $seller_order->buyer_order_id = $item->guid;
                $seller_order->charge_card_info = 
                        "{$user->username}-{$card['brand']}-{$card['last4']}-{$card['exp_month']}-{$card['exp_year']}";

                $seller_order->status = "Paid";
                $seller_order->shipping_vendor = "None";
                $seller_order->tracking_number = "None";
                $seller_order->shipping_speed = "None";

// ~

		$seller_order->time_friendly = $time_friendly;
		$seller_order->timestamp = $timestamp;

		$seller_order->status = "paid";

                $seller_item = "";

                if ($seller_order->save()) {
       	            $seller_item['order_id'] = $seller_order->guid;

                    // send seller email

                    $body = "
Hello $seller->username,

You have a new order  order (#$seller_order->guid). Please check your seller portal.

Please contact us (team@lovebeauty.me) should you have any questions.

Yours truly,
Lovebeauty Team
";
                    $email_sent = elgg_send_email ("team@lovebeauty.com", $seller->email, "[Lovebeauty] New order $seller_order->guid was made", $body);
      	            $seller_item['email_sent'] = $email_sent;
                    $seller_item['time_friendly'] = $time_friendly;
                    $seller_item['timestamp'] = $timestamp;

                    $seller_item['product_name'] = $product_value['product_name'];
                    $seller_item['product_image_url'] = $product_value['product_image_url'];
                    $seller_item['product_price'] = $product_value['product_price'];
  		    $seller_item['product_quantity'] = $product_value['item_number'];
  		    $seller_item['product_sold_count'] = $product->sold_count;
                    $seller_item['avatar_url'] = get_entity_icon_url($seller, 'small');
   		    $seller_item['shipping_address'] = $seller_order->shipping_address;

                    $seller_item['left_product_quantity'] = intval($product->quantity);

                } else {
                    throw new InvalidParameterException(elgg_echo("pay:charge:seller_order:saveerror"));
		}
                $person_info['seller_orders'][] = $seller_item;

                // find the tip owner (if exist) and create the tip owner's credit object, XXX

                if ($product_value['thinker_id'] && $product_value['thinker_idea_id']) {

		    $thinker_order = new ElggObject();
                    $thinker_order->access_id = ACCESS_LOGGED_IN;
                    $thinker_order->type = 'object';
                    $thinker_order->subtype = "thinker_order";
		    $thinker_order->seller_guid = $seller_order->seller_guid;
		    $thinker_order->seller_order_guid = $seller_order->guid;
		    $thinker_order->buyer_order_guid = $item->guid;
                    $thinker_order->buyer_guid = $user->guid;
                    $thinker_order->product_guid = $product_value['product_id'];
		    $thinker_order->product_name = $product_value['product_name'];
		    $thinker_order->product_image_url = $product_value['product_image_url'];
                    $thinker_order->thinker_guid = $product_value['thinker_id'];
                    $thinker_order->thinker_idea_guid = $product_value['thinker_idea_id'];
		    $thinker_order->time_friendly = $time_friendly;
		    $thinker_order->timestamp = $timestamp;
		    $thinker_order->status = "paid";
                    $thinker_order->product_price = $product_value['product_price'];
                    $thinker_order->product_quantity = $product_value['item_number'];

                    $thinker = get_user($product_value['thinker_id']);

                    $points_earned = ($product_value['product_price'] * $product_value['item_number'] * 1); // 1% of the product price
                    $dollar_earned = intval($points_earned) / 100;
                    $thinker->points += intval($points_earned);
                    $thinker_order->points = intval($points_earned);

                    $thinker->save();
                
                    $thinker_item = "";
                    if ($thinker_order->save()) {
       	                $thinker_item['order_id'] = $thinker_order->guid;

                        // send email, format it later
                    $body = "
Hello $thinker->username,

Thank you for your great idea. You just received a commission (\$$dollar_earned) for your great work. Please check  \"idea contribution\" in your APP.

Please contact us (team@lovebeauty.me) should you have any questions.

Yours truly,
Lovebeauty Team
";

                        $email_sent = elgg_send_email ("team@lovebeauty.com", $thinker->email, "[Lovebeauty] Thinker contribution ($thinker_order->guid) was  made", $body);
      	                $thinker_item['email_sent'] = $thinker->email;
                        $thinker_item['time_friendly'] = $time_friendly;
                        $thinker_item['timestamp'] = $timestamp;
                        $thinker_item['product_id'] = $product_value['product_id'];
                        $thinker_item['product_name'] = $product_value['product_name'];
                        $thinker_item['product_image_url'] = $product_value['product_image_url'];
                        $thinker_item['product_price'] = $product_value['product_price'];
                        $thinker_item['product_quantity'] = $product_value['item_number'];
                        $thinker_item['avatar_url'] = get_entity_icon_url($thinker, 'small');
                        $thinker_item['thinker_idea_id'] = $product_value['thinker_idea_id'];
                        $thinker_item['points'] = intval($thinker->points);
                    } else {
                        throw new InvalidParameterException(elgg_echo("pay:thinker_order:save"));
                    }
                    $person_info['thinker_info'][] = $thinker_item;
                }
            } // products loop
            $seller_info['products'][] = $person_info;
            $person_info = "";
 	} // seller loop
        $return['seller_info'] = $seller_info;
        $seller_info = "";
    } else {
        register_error(elgg_echo("pay:charge:order:error"));
        throw new InvalidParameterException(elgg_echo("pay:charge:order:error"));
    }

    // send email to buyer

    $product_msg = "";
    foreach ($sellers as $key => $value) {
        $products = $value['products'];
        $product_msg = $product_msg."Seller name: ".$value['seller_name']."\n";
        foreach ($products as $product_key => $product_value) {
            $product_price = $product_value['product_price'];
            $shipping_cost = $product_value['shipping_cost'];
        
            $product_msg = $product_msg."    name: ".$product_value['product_name']."\n";
            $product_msg = $product_msg."    price: ".'$'.$product_price." x ".$product_value['item_number']."\n";
        }
        $product_msg = $product_msg."Shipping cost: ".$product_value['shipping_cost']."\n";
        $product_msg = $product_msg."\n";
    }
    $product_msg = $product_msg."--- --- ---\n";
    $total_amount = $order_info['amount'] / 100;
    $product_msg = $product_msg."Total cost: ".'$'.$total_amount."\n";
    $product_msg = $product_msg."Currency: ".$order_info['currency']."\n";
    $product_msg = $product_msg."Card last 4 digit: ".$card['last4']."\n";
    $product_msg = $product_msg."Shipping address: ".$order_info['shipping_address']."\n";
    $product_msg = $product_msg."Delivery method: ".$order_info['shipping_method']."\n";
    $body = "
Hello $user->username,

Thank you for shopping with us. Your order (#$item->guid) is listed here:

$product_msg

Please contact us (team@lovebeauty.me) should you have any questions.

Yours truly,
Lovebeauty Team
";
    $email_sent = elgg_send_email ("team@lovebeauty.com", $user->email, "[Lovebeauty] Thank you for your shopping.", $body);
    $return['email_sent'] = $user->email;
    $return['time_friendly'] = $time_friendly;
    $return['timestamp'] = $timestamp;


//$item->seller_info = $return['seller_info'];
//$item->thinker_info = $return['thinker_info'];
//$item->save();

    return $return;
}
expose_function('payment.checkout_direct',
                "pay_checkout_direct",
                array(
                      'msg' => array ('type' => 'string', 'required' => false, 'default' => null),
                    ),
                "checkout directly without using shopping cart",
                'POST',
                true,
                true);


function pay_checkout()
{
    elgg_load_library('elgg:pay');

    $user = elgg_get_logged_in_user_entity();
    if (!$user) {
        throw new InvalidParameterException('registration:usernamenotvalid');
    }

    $basket = elgg_get_entities(array(
        'type' => 'object',
        'subtype' => 'pay_basket',
        'owner_guid' => $user->guid,
    ));

    $amount = pay_basket_total();
    $recurring = false;

    //We create a new order object
    $order = new ElggObject();
    $order->subtype = 'pay';

    $order->order = true;

    //temp variables
    $order->seller_guid = $basket[0]->seller_guid;
    $order->object_guid = $basket[0]->object_guid;
    foreach($basket as $item){
        $a->title = $item->title;
        $a->description = $item->description;
        $a->price = $item->price;
        $a->quantity = $item->quantity;
        $a->object_guid = $item->object_guid;
        $a->seller_guid = $item->seller_guid;
        if ($item->recurring == 'y') // TODO: Currently we have to set whole basket to recurring if one item repeats. Not ideal.
            $recurring = true;
        $items[] = $a;
        $item->delete();
    }
    $order->items = serialize($items);

    $order->amount = $amount;
    $order->status = 'created';

    // Flag as recurring
    if ($recurring)
        $order->recurring = true;

    $order->access_id = 1;

    $order->payment_method = 'stripe';
    if($order->save()){
        notification_create(array($order->seller_guid, $order->getOwnerGUID()), 0, $order->getGuid(), array('notification_view'=>'pay_order'));

        $stripe = new StripeClient();
        return $stripe->createCharge($user->guid, 
            array(  'amount' => $order->amount,
                    'currency' => "usd",
                    'card' => $stripe->getCard($user->guid),
                    'description' => "test charge",
                    'metadata' => "",
  	            'capture' => "",
                    'statement_description' => "",
                    'application_fee' => "",
            ));
    } else {
        register_error(elgg_echo("pay:checkout:failed"));
        throw new InvalidParameterException(elgg_echo("pay:checkout:failed"));
    }
}

expose_function('payment.checkout',
                "pay_checkout",
                array(
                    ),
                "checkout the shopping cart",
                'POST',
                true,
                true);

function pay_add_to_basket($product_id, $quantity, $idea_id)
{
    $blog = get_entity($product_id);

    if (!elgg_instanceof($blog, 'object', 'market')) {
        throw new InvalidParameterException(elgg_echo('market:error:product_not_found'));
    }

    // Get variables
    $type = get_entity($product_id);

    $title = htmlspecialchars($blog->title);
    $desc = $blog->description;
    $price = floatval($blog->price);
    $quantity = intval($blog->quantity);

    $user_guid = (int) elgg_get_logged_in_user_guid();

    $seller = get_entity($blog->owner_guid);
    $seller_guid = $seller->guid;
    $recurring = false;

    $item = new ElggObject();
    $item->type = 'object';
    $item->subtype = 'pay_basket';

    $item->object_guid = $product_id;
    $item->title = $title;
    $item->description = $desc;
    $item->quantity = $quantity;
    $item->price = $price * $quantity;
    $item->seller_guid = $seller_guid;
    $item->owner_guid = $user_guid;
    $item->idea_id = $idea_id;
    $item->access_id = 1;

    if($item->save()){
        $item->recurring = $recurring;
	$return['id'] = $item->guid;
        $return['content'] = elgg_echo("pay:bakset:item:add:success");
	return $return;
    } else {
        register_error(elgg_echo("pay:basket:item:add:failed"));
        throw new InvalidParameterException(elgg_echo("pay:basket:item:add:failed"));
    }
}
expose_function('payment.add_to_basket',
                "pay_add_to_basket",
                array(
                      'product_id' => array ('type' => 'int', 'required' => true, 'default' => 0),
                      'quantity' => array ('type' => 'int', 'required' => true, 'default' => 0),
                      'idea_id' => array ('type' => 'int', 'required' => true, 'default' => 0),
                    ),
                "add product to the shopping cart",
                'POST',
                true,
                true);

function pay_remove_from_basket($product_id)
{
// check the object type before remove
    $item = get_entity($product_id);

    $order = "";
    if (elgg_instanceof($item, 'object', 'pay_basket') && $item->canEdit()) {
        $order = $item;
    } else {
        register_error(elgg_echo('ideas:error:post_not_found'));
        throw new InvalidParameterException("ideas:error:post_not_found");
    }

    if($order->delete()){
        $return = elgg_echo("pay:bakset:item:remove:success");
    } else {
        register_error(elgg_echo("pay:basket:item:remove:failed"));
        throw new InvalidParameterException(elgg_echo("pay:basket:item:remove:failed"));
    }

    return $return;

}
expose_function('payment.remove_from_basket',
                "pay_remove_from_basket",
                array(
                      'product_id' => array ('type' => 'int', 'required' => true, 'default' => 0),
                    ),
                "remove product from the shopping cart",
                'POST',
                true,
                true);


function pay_update_quantity($product_id, $quantity)
{
    $item = get_entity($product_id);

    if($item->original_price){
        $price = $item->original_price * $quantity;
    } else {
        $price = $item->price * $quantity;
        $item->original_price = $item->price;
    }

    $item->quantity = $quantity;

    //keep a log of the original price

    $item->price = $price;

    if($item->save()){
        return elgg_echo("pay:bakset:item:update:success");
    } else {
        throw new InvalidParameterException(elgg_echo("pay:basket:item:update:failed"));
    }
}

expose_function('payment.update_quantity',
                "pay_update_quantity",
                array(
                      'product_guid' => array ('type' => 'int', 'required' => true, 'default' => 0),
                      'quantity' => array ('type' => 'int', 'required' => true, 'default' => 0),
                    ),
                "update quantity of a product in the shopping cart.",
                'POST',
                true,
                true);

function pay_list_basket($limit, $offset)
{
    $user = elgg_get_logged_in_user_entity();
    if (!$user) {
        throw new InvalidParameterException('registration:usernamenotvalid');
    }

    $params = array(
        'types' => 'object',
        'subtypes' => 'pay_basket',
        'owner_guid' => $user->guid,
        'limit' => $limit,
        'full_view' => FALSE,
        'offset' => $offset,
    );
    $latest_blogs = elgg_get_entities($params);
    if($latest_blogs) {
        $return['category'] = $category;
        $return['offset'] = $offset;

        $display_product_number = 0;
        foreach($latest_blogs as $single ) {
            $item['product_id'] = $single->object_guid;
            $item['title'] = $single->title;
            $item['description'] = $$single->description;
            $item['quantity'] = $single->quantity;
            $item['price'] = $single->price;

            $owner = get_entity($single->seller_guid);
	    if ($owner) {
	        $item['product_seller']['user_id'] = $owner->guid;
                $item['product_seller']['user_name'] = $owner->username;
                $item['product_seller']['user_avatar_url'] = get_entity_icon_url($owner,'small');
                $item['product_seller']['is_seller'] = $owner->is_seller;
            }
	    $display_product_number ++;
	    $return['products'][] = $item;
        }
        $return['total_number'] = $display_product_number;
    }
    else {
        $msg = elgg_echo('payment_basket:none');
        throw new InvalidParameterException($msg);
    }

    return $return;
}
expose_function('payment.list_basket',
                "pay_list_basket",
                array(
                      'limit' => array ('type' => 'int', 'required' => false, 'default' => 10),
                      'offset' => array ('type' => 'int', 'required' => false, 'default' => 0),
                    ),
                "list items from the shopping cart",
                'GET',
                true,
                true);

// This is buyer's shopping history
function pay_list_buyer_order($context, $username, $limit, $offset)
{
    if (!$username) {
        $user = get_loggedin_user();
        if (!$user) {
            throw new InvalidParameterException('pay_list_buyer_order:loginusernamenotvalid');
        }
    } else {
        $user = get_user_by_username($username);
        if (!$user) {
            throw new InvalidParameterException('pay_list_buyer_order:usernamenotvalid');
        }
    }

    if($context == "all"){
        $params = array(
            'types' => 'object',
            'subtypes' => 'buyer_order',
            'limit' => $limit,
            'full_view' => FALSE,
            'offset' => $offset,
            );
    } else if(($context == "mine") || ($context ==  "user")){
        $params = array(
            'types' => 'object',
            'subtypes' => 'buyer_order',
            'owner_guid' => $user->guid,
            'limit' => $limit,
            'full_view' => FALSE,
            'offset' => $offset,
        );
    } else {
        throw new InvalidParameterException('pay_list_buyer_order:contextnotvalid');
    }
    $latest_blogs = elgg_get_entities($params);


    if($latest_blogs) {
        $return['offset'] = $offset;
        $display_number = 0;

        foreach($latest_blogs as $single ) {
/*
  	    $return['guid'][] = $single->guid;
            // Delete the market post
            $rowsaffected = $single->delete();
            if ($rowsaffected > 0) {
                $return['delete'][] = true;
            } else {
                $return['delete'][] = false;
            }
*/

            $json = json_decode(html_entity_decode($single->msg), true);

            $json['order_info']['charge_card_name'] = $single->charge_card_name;
            $json['order_info']['time_friendly'] = $single->time_friendly;
            $json['order_info']['timestamp'] = $single->timestamp;
            $json['order_info']['order_guid'] = $single->object_guid;

            $json['order_info']['status'] = $single->status;
            $json['order_info']['shipping_vendor'] = $single->shipping_vendor;
            $json['order_info']['tracking_number'] = $single->tracking_number;
            $json['order_info']['shipping_speed'] = $single->shipping_speed;

//            $json['seller_info'] = $single->seller_info;
//            $json['thinker_info'] = $single->thinker_info;

            $return['msg'][] = $json;
            $display_number ++;
        }
        $return['total_number'] = $display_number;
    }
    else {
        $msg = elgg_echo('payment_basket:none');
        throw new InvalidParameterException($msg);
    }

    return $return;
}

expose_function('payment.list.buyer_order',
                "pay_list_buyer_order",
                array(
                      'context' => array ('type' => 'string', 'required' => false, 'default' => "all"),
                      'username' => array ('type' => 'string', 'required' => false, 'default' => ""),
                      'limit' => array ('type' => 'int', 'required' => false, 'default' => 10),
                      'offset' => array ('type' => 'int', 'required' => false, 'default' => 0),
                    ),
                "list items of buyer order",
                'GET',
                true,
                true);


// This is seller's transaction history
function pay_list_seller_order($context, $username, $limit, $offset, $time_start, $time_end)
{
    if (!$username) {
        $user = get_loggedin_user();
        if (!$user) {
            throw new InvalidParameterException('pay_list_seller_order:loginusernamenotvalid');
        }
    } else {
        $user = get_user_by_username($username);
        if (!$user) {
            throw new InvalidParameterException('pay_list_seller_order:usernamenotvalid');
        }
    }

    if(($context == "all") && $user->is_admin){
        $params = array(
            'types' => 'object',
            'subtypes' => 'seller_order',
            'limit' => $limit,
            'full_view' => FALSE,
            'offset' => $offset,
            );
        $latest_blogs = elgg_get_entities($params);
    } else if(($context == "mine") || ($context ==  "user")){
        $params = array(
            'types' => 'object',
            'subtypes' => 'seller_order',
            'limit' => $limit,
            'offset' => $offset,
            'metadata_name_value_pairs'=>array(
                array('name' => 'seller_guid', 
                      'value' => $user->guid, 
                      'operand' => '=' )));
        $latest_blogs = elgg_get_entities_from_metadata($params);
    } else {
        throw new InvalidParameterException('pay_list_seller_order:contextnotvalid');
    }
    if($latest_blogs) {
        $return['offset'] = $offset;
        $display_number = 0;

        foreach($latest_blogs as $single ) {
/*
  	    $return['guid'][] = $single->guid;
            // Delete the market post
            $rowsaffected = $single->delete();
            if ($rowsaffected > 0) {
                $return['delete'][] = true;
            } else {
                $return['delete'][] = false;
            }
*/

            // XXX: should be part of the filter
            if (($item['timestamp'] < $time_start) || ($item['timestamp'] > $time_end)) {
	        continue;
	    }
            $item['order_guid'] = $single->guid;
            $item['product_guid'] = $single->product_guid;
            $item['coupon_code'] = $single->coupon;
            $item['coupon_discount'] = 0;
            $item['product_name'] = $single->product_name;
            $item['product_image_url'] = $single->product_image_url;
            $item['product_price'] = $single->product_price;
            $item['product_quantity'] = $single->product_quantity;
            $item['shipping_code'] = $single->shipping_code;
            $item['shipping_cost'] = $single->shipping_cost;

	    if ($single->shipping_address) {
                $item['shipping_address'] = json_decode($single->shipping_address, true);
            }
// XXX, credit_card is the only payment
            $item['payment_method'] = "credit"; //$single->payment_method;
            $item['charge_card_name'] = $single->charge_card_name;
            $item['charge_card_info'] = $single->charge_card_info;

            $item['purchased_time_friendly'] = $single->time_friendly;
            $item['purchased_timestamp'] = $single->timestamp;
	    $item['status'] = $single->status;
            $item['shipping_vendor'] = $single->shipping_vendor;
            $item['tracking_number'] = $single->tracking_number;
            $item['shipping_speed'] = $single->shipping_speed;

            $seller = get_user($single->seller_guid);
            if (!$seller) {
                $item['seller']['seller_name'] = "";
                $item['seller']['seller_email'] = "";
                $item['seller']['seller_avatar'] = "";
	    } else {
                $item['seller']['seller_guid'] = $single->seller_guid;
                $item['seller']['seller_name'] = $seller->username;
                $item['seller']['seller_email'] = $seller->email;
                $item['seller']['seller_avatar'] = get_entity_icon_url($seller, 'small');
	    }

            $display_number ++;
            $return['product'][] = $item;
        }
	
        $return['total_number'] = $display_number;
    }
    else {
        $return['total_number'] = 0;
        $return['product'] = "";
    }

    return $return;
}
expose_function('payment.list.seller_order',
                "pay_list_seller_order",
                array(
                      'context' => array ('type' => 'string', 'required' => false, 'default' => "mine"),
                      'username' => array ('type' => 'string', 'required' => false, 'default' => ""),
                      'limit' => array ('type' => 'int', 'required' => false, 'default' => 10),
                      'offset' => array ('type' => 'int', 'required' => false, 'default' => 0),
                      'start_time' => array ('type' => 'int', 'required' => false, 'default' => 0),
                      'end_time' => array ('type' => 'int', 'required' => false, 'default' => 5555555555),
                    ),
                "list items of seller order",
                'GET',
                true,
                true);

///// analytical
// This is seller's transaction history
function pay_analyze_seller_order($time_start, $time_end)
{
    $user = get_loggedin_user();
    
    $lb_commission_rate = 0.01;
    if ($user->lb_commission_rate) {
        $lb_commission_rate = $user->lb_commission_rate;
    }

    if (!$user) {
        throw new InvalidParameterException('pay_list_seller_order:loginusernamenotvalid');
    }
    $params = array(
        'types' => 'object',
        'subtypes' => 'seller_order',
        'limit' => 0,
        'offset' => 0,
        'metadata_name_value_pairs'=>array(
            array('name' => 'seller_guid', 
                  'value' => $user->guid, 
                  'operand' => '=' )));
    $latest_blogs = elgg_get_entities_from_metadata($params);

    if (!$latest_blogs) {
        $msg = elgg_echo('payment_seller_order:none');
        throw new InvalidParameterException($msg);
    }
    $display_number = 0;
    foreach($latest_blogs as $single ) {
        if (($item['timestamp'] < $time_start) || ($item['timestamp'] > $time_end)) {
            continue;
        }
        $time_array = getdate($single->timestamp);
        $year = $time_array['year'];
        $month = $time_array['mon'];

        $revenue = ($single->product_price + $single->shipping_cost);
        $cost = ($single->product_price + $single->shipping_cost) * 
                $lb_commission_rate;
        $profit = $revenue - $cost;

        $total_revenue += $revenue;
        $total_cost += $cost;

        $seller_revenue[$year][$month] += $revenue;
        $seller_cost[$year][$month] += $cost;
        $seller_profit[$year][$month] += $profit;

        $display_number ++;
    }
    $return['revenue'][] = $seller_revenue;
    $return['cost'][] = $seller_cost;
    $return['profit'][] = $seller_profit;

    $return['total_revenue'] = $total_revenue;
    $return['total_cost'] = $total_cost;
    $return['total_profit'] = $total_revenue - $total_cost;
    $return['total_orders'] = $display_number;
    $return['lb_commission_rate'] = $lb_commission_rate;

    return $return;
}
expose_function('payment.analyze.seller_order',
                "pay_analyze_seller_order",
                array(
                      'start_time' => array ('type' => 'int', 'required' => false, 'default' => 0),
                      'end_time' => array ('type' => 'int', 'required' => false, 'default' => 5555555555),
                    ),
                "Get revenue by month",
                'GET',
                true,
                true);
// ~


// This is seller's transaction history
function pay_detail_seller_order($order_id)
{
    $return = array();
    $single = get_entity($order_id);
    if (!elgg_instanceof($single, 'object', 'seller_order')) {
        $return['content'] = elgg_echo('seller_order:error:post_not_found');
        return $return;
    }
    $item['order_guid'] = $single->guid;
    $item['product_guid'] = $single->product_guid;
    $item['coupon_code'] = $single->coupon;
    $item['coupon_discount'] = 0;
    $item['product_name'] = $single->product_name;
    $item['product_image_url'] = $single->product_image_url;
    $item['product_price'] = $single->product_price;
    $item['product_quantity'] = $single->product_quantity;
    $item['shipping_code'] = $single->shipping_code;
    $item['shipping_cost'] = $single->shipping_cost;

    $item['status'] = $single->status;
    $item['shipping_vendor'] = $single->shipping_vendor;
    $item['tracking_number'] = $single->tracking_number;
    $item['shipping_speed'] = $single->shipping_speed;

    if ($single->shipping_address) {
        $item['shipping_address'] = json_decode($single->shipping_address, true);
    }
// XXX, credit_card is the only payment
    $item['payment_method'] = "credit_card"; //$single->payment_method;
    $item['charge_card_name'] = $single->charge_card_name;
    $item['charge_card_info'] = $single->charge_card_info;
    $item['purchased_time_friendly'] = $single->time_friendly;
    $item['purchased_timestamp'] = $single->timestamp;


    $seller = get_user($single->seller_guid);
    if (!$seller) {
        $item['seller']['seller_name'] = "";
        $item['seller']['seller_email'] = "";
        $item['seller']['seller_avatar'] = "";
    } else {
        $item['seller']['seller_guid'] = $single->seller_guid;
        $item['seller']['seller_name'] = $seller->username;
        $item['seller']['seller_email'] = $seller->email;
        $item['seller']['seller_avatar'] = get_entity_icon_url($seller, 'small');
    }
    return $item;
}

expose_function('payment.detail.seller_order',
                "pay_detail_seller_order",
                array(
                      'order_id' => array ('type' => 'int', 'required' => true, 'default' => 0),
                    ),
                "detail of seller order item",
                'GET',
                true,
                true);

// This is thinker's commission/credit transaction history
function pay_list_thinker_order($context, $username, $limit, $offset, $time_start, $time_end)
{
    if (!$username) {
        $user = get_loggedin_user();
        if (!$user) {
            throw new InvalidParameterException('pay_list_thinker_order:loginusernamenotvalid');
        }
    } else {
        $user = get_user_by_username($username);
        if (!$user) {
            throw new InvalidParameterException('pay_list_thinker_order:usernamenotvalid');
        }
    }
    if($context == "all" && $user->is_admin){
        $params = array(
            'types' => 'object',
            'subtypes' => 'thinker_order',
            'limit' => $limit,
            'full_view' => FALSE,
            'offset' => $offset,
            );
        $latest_blogs = elgg_get_entities($params);
    } else if(($context == "mine") || ($context ==  "user")){
        $params = array(
            'types' => 'object',
            'subtypes' => 'thinker_order',
            'limit' => $limit,
            'offset' => $offset,
            'metadata_name_value_pairs'=>array(
                array('name' => 'thinker_guid', 
                      'value' => $user->guid, 
                      'operand' => '=' ))
            );
        $latest_blogs = elgg_get_entities_from_metadata($params);
    } else {
        throw new InvalidParameterException('pay_list_thinker_order:contextnotvalid');
    }
    if($latest_blogs) {
        $return['offset'] = $offset;
        $display_number = 0;

        foreach($latest_blogs as $single ) {
/* for debugging, delete all the thinker order
  	    $return['guid'][] = $single->guid;
            // Delete the market post
            $rowsaffected = $single->delete();
            if ($rowsaffected > 0) {
                $return['delete'][] = true;
            } else {
                $return['delete'][] = false;
            }
*/

            // XXX: should be part of the filter
            if (($item['timestamp'] < $time_start) || ($item['timestamp'] > $time_end)) {
	        continue;
	    }

	    $seller = get_user($single->seller_guid);
	    $buyer = get_user($single->buyer_guid);
	    $idea = get_entity($single->thinker_idea_guid);
            $thinker = get_user($single->thinker_guid);

            if (!$thinker) {
                $item['thinker']['thinker_name'] = "";
                $item['thinker']['thinker_email'] = "";
                $item['thinker']['thinker_avatar'] = "";
	    } else {
                $item['thinker']['thinker_guid'] = $single->thinker_guid;
                $item['thinker']['thinker_name'] = $thinker->username;
                $item['thinker']['thinker_avatar'] = get_entity_icon_url($thinker, 'small');
	    }
	    if (!$seller) {
	        $item['seller']['guid'] = "";
	        $item['seller']['name'] = "";
	        $item['seller']['avatar'] = "";
            } else {
	        $item['seller']['guid'] = $seller->guid;
	        $item['seller']['name'] = $seller->username;
	        $item['seller']['avatar'] = get_entity_icon_url($seller, 'small');
            }
	    if (!$buyer) {
	        $item['buyer']['guid'] = "";
	        $item['buyer']['name'] = "";
	        $item['buyer']['avatar'] = "";
            } else {
	        $item['buyer']['guid'] = $buyer->guid;
	        $item['buyer']['name'] = $buyer->username;
	        $item['buyer']['avatar'] = get_entity_icon_url($buyer, 'small');
            }

	    if (!$idea) {
	        $item['idea']['guid'] = "";
	        $item['idea']['name'] = "";
                $item['idea']['tip_thumbnail_image_url'] = "";
            } else {
	        $item['idea']['guid'] = $idea->guid;
	        $item['idea']['name'] = $idea->title;
                 if (!$idea->tip_thumbnail_image_url) {
                     $item['idea']['tip_thumbnail_image_url'] = elgg_get_config('cdn_link').'/ideas/image/'.$idea->guid."/"."0"."/"."large/";
                 } else {
                     $item['idea']['tip_thumbnail_image_url'] = $idea->tip_thumbnail_image_url;
                 }
            }

            $item['order']['time_friendly'] = $single->time_friendly;
            $item['order']['timestamp'] = $single->timestamp;

            $item['product']['id'] = $single->product_guid;
            $item['product']['name'] = $single->product_name;
            $item['product']['image_url'] = $single->product_image_url;
            $item['product']['price'] = $single->product_price;
            $item['product']['quantity'] = $single->product_quantity;

            $item['thinker_order_guid'] = $single->guid;
            $item['points'] = intval($single->points);
            $display_number ++;
            $return['thinker'][] = $item;

        }
	
        $return['total_number'] = $display_number;
    }
    else {
        $msg = elgg_echo('payment_thinker_order:none');
        throw new InvalidParameterException($msg);
    }

    return $return;
}
expose_function('payment.list.thinker_order',
                "pay_list_thinker_order",
                array(
                      'context' => array ('type' => 'string', 'required' => false, 'default' => "all"),
                      'username' => array ('type' => 'string', 'required' => false, 'default' => ""),
                      'limit' => array ('type' => 'int', 'required' => false, 'default' => 10),
                      'offset' => array ('type' => 'int', 'required' => false, 'default' => 0),
                      'start_time' => array ('type' => 'int', 'required' => false, 'default' => 0),
                      'end_time' => array ('type' => 'int', 'required' => false, 'default' => 5555555555),
                    ),
                "list items of thinker order",
                'GET',
                true,
                true);

// This is thinker's commission/credit transaction history
function pay_detail_thinker_order($id)
{
    $single = get_entity($id);

    if (!$single) {
        throw new InvalidParameterException("pay:thinker_order:detail:nonexist");
    }

    $seller = get_user($single->seller_guid);
    $buyer = get_user($single->buyer_guid);
    $idea = get_entity($single->thinker_idea_guid);
    $thinker = get_user($single->thinker_guid);

    if (!$thinker) {
        $item['thinker']['thinker_name'] = "";
        $item['thinker']['thinker_email'] = "";
        $item['thinker']['thinker_avatar'] = "";
    } else {
        $item['thinker']['thinker_guid'] = $single->thinker_guid;
        $item['thinker']['thinker_name'] = $thinker->username;
        $item['thinker']['thinker_avatar'] = get_entity_icon_url($thinker, 'small');
    }
    if (!seller) {
        $item['seller']['guid'] = "";
        $item['seller']['name'] = "";
        $item['seller']['avatar'] = "";
    } else {
        $item['seller']['guid'] = $seller->guid;
        $item['seller']['name'] = $seller->username;
        $item['seller']['avatar'] = get_entity_icon_url($seller, 'small');
    }
    if (!buyer) {
        $item['buyer']['guid'] = "";
        $item['buyer']['name'] = "";
        $item['buyer']['avatar'] = "";
    } else {
        $item['buyer']['guid'] = $buyer->guid;
        $item['buyer']['name'] = $buyer->username;
        $item['buyer']['avatar'] = get_entity_icon_url($buyer, 'small');
    }

    if (!idea) {
        $item['idea']['guid'] = "";
        $item['idea']['name'] = "";
        $item['idea']['tip_thumbnail_image_url'] = "";
    } else {
        $item['idea']['guid'] = $idea->guid;
        $item['idea']['name'] = $idea->title;
	$item['idea']['tip_thumbnail_image_url'] = $idea->tip_thumbnail_image_url;
    }

    $item['product']['name'] = $single->product_name;
    $item['product']['image_url'] = $single->product_image_url;
    $item['product']['price'] = $single->product_price;

    $item['idea']['name'] = $idea->title;
    $item['thinker_order_guid'] = $single->guid;
    $display_number ++;
    $return['thinker'][] = $item;

    return $return;
}

expose_function('payment.detail.thinker_order',
                "pay_detail_thinker_order",
                array(
                      'id' => array ('type' => 'int', 'required' => true, 'default' => 0),
                    ),
                "detail of a particular thinker order",
                'GET',
                true,
                true);

// seller order update: paid -> shipped -> delivered
/*
function pay_order_update($id, $status)
{
    $seller_order = get_entity($id);
    $seller_order->status = $status;
    $seller_order->save();
    if (!$seller_order) {
        return false;
    } else {
        return true;
    }
}

expose_function('payment.order_update',
                "pay_order_update",
                array(
                      'id' => array ('type' => 'int', 'required' => true, 'default' => 0),
                      'status' => array ('type' => 'string', 'required' => true, 'default' => ""),
                    ),
                "detail of a particular thinker order",
                'POST',
                true,
                true);
*/
// seller order update:         status:          paid -> shipped -> delivered
//                              shipping_vendor: fedex/ups/
//                              tracking_number: xxx
//                              shipping_speed:  3-5 days

function pay_order_shipping_update($order_id, $shipping_vendor, 
        $tracking_number, $shipping_speed, $status)
{
    $return['seller_order_id'] = $order_id;
    $seller_order = get_entity($order_id);
    $seller_order->status = $status;
    $seller_order->shipping_vendor = $shipping_vendor;
    $seller_order->tracking_number = $tracking_number;
    $seller_order->shipping_speed = $shipping_speed;

    $seller_save_result = $seller_order->save();

    if (!$seller_save_result) {
        throw new InvalidParameterException("seller_order is not saved properly");
    }
    // update the buyer order status
    if ($seller_order->buyer_order_id == 0) {
        throw new InvalidParameterException("seller_order doesn't have associated buyer order");
    }
    $buyer_order = get_entity($seller_order->buyer_order_id);
    $buyer_order->status = $status;
    $buyer_order->shipping_vendor = $shipping_vendor;
    $buyer_order->tracking_number = $tracking_number;
    $buyer_order->shipping_speed = $shipping_speed;

    $return['buyer_id'] = $seller_order->buyer_order_id;

    $return['status'] = $status;
    $return['shipping_vendor'] = $shipping_vendor;
    $return['tracking_number'] = $tracking_number;
    $return['shipping_speed'] = $shipping_speed;

    $buyer_save_result = $buyer_order->save();
    if (!$buyer_save_result) {
        throw new InvalidParameterException("buyer_order is not saved properly");
    }

    return $return;
}

expose_function('payment.order_shipping_update',
                "pay_order_shipping_update",
                array(
                      'order_id' => array ('type' => 'int', 'required' => true, 'default' => 0),
                      'shipping_vendor' => array ('type' => 'string', 'required' => true, 'default' => ""),
                      'track_number' => array ('type' => 'string', 'required' => true, 'default' => ""),
                      'shipping_speed' => array ('type' => 'string', 'required' => true, 'default' => ""),
                      'status' => array ('type' => 'string', 'required' => true, 'default' => ""),
                    ),
                "seller update shipping information",
                'POST',
                true,
                true);

