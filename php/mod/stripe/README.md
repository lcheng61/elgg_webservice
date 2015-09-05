Stripe
======

Stripe.com API Layer for Elgg

The plugin is intended primarily for developers, who are implementing a payments
flow.

## Intro

This plugin implements an API Layer for interfacing with Elgg. It implements
most common methods, including creating and updating customers, creating charges,
adding and removing cards etc.

The goal is to provide a uniform API that works with Elgg's entity architecture,
this includes maintaining references between, for example, Elgg users and Stripe
customers.

The plugin also provides a UI for users to manage their payment methods, view
their transaction history, etc.

Architecture is such as to avoid storing data in Elgg, where possible, so your
site stays PCI compliant, while entertaining broad e-commerce possibilities.

The plugin provides API for creating/updating/deleting/viewing:
* Customers
* Cards
* Charges
* Plans
* Subscriptions
* Invoices

Additional features, such as Stripe Connect or transfers, can/might be implemented in
separate plugins.


## Security Considerations

It is the responsibility of the site owner to enable SSL encryption, as well as
taking additional precautions to protect users from unauthorized access to their
personal data (even though it is not stored in the Elgg database, some information
will made available to viewers in real-time) as well as from unauthorized payments
for goods on the site (in case the credit card is stored with the customer entity).

It is perhaps a good idea to ensure that user sessions time out regularly, or that
stripe-related pages are password protected.


## Webhooks

To ensure that Elgg receives some crucial updates, please set up your Stripe
webhooks as follows:

**Testing**
https://YOUR-SITE/services/api/rest/json?method=stripe.webhooks&environment=sandbox

**Live**
https://YOUR-SITE/services/api/rest/json?method=stripe.webhooks&environment=production


Once you have set up the webhooks, you can add handlers for ```$stripe_event_type, 'stripe.events'```
plugin hook in Elgg to implement additional logic. Your callback function will
receive a Stripe event object and an environment descriptor.

A list of Stripe events can be found here:
https://stripe.com/docs/api#event_types


## Customers

Customers are created automagically, whenever you pass an email, user guid or
user entity to the API.

Whenever a user is registered with an email has a Stripe customer account,
Stripe customer will be mapped to that user and the transaction history will be
available to the user.


### Example: adding a card

```php
$token = get_input('token'); // this would have been generated by stripe.js
$user = elgg_get_logged_in_user_entity();

$client = new StripeClient();
$client->createCard($user, $token);
```


### Example: creating a charge

```php

$user_guid = elgg_get_logged_in_user_guid();

$charge = array(
	'amount' => 12545, // amount in cents
	'currency' => 'USD',
	'card' => $card_id, // optional if the customer has a card on file
	'metadata' => array(
		'cart_guid' => $cart_guid,
		'shop_guid' => $shop_guid,
	),
);

$client = new StripeClient();
$client->createCharge($user_guid, $charge);
```
