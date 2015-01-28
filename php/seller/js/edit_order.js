// Sets the min-height of #page-wrapper to window size
$(function() {
	var product;
	var order_id = getUrlParameter("order_id");
	console.log("order_id=" + order_id);
	if (order_id != undefined && order_id != null) { //edit data for teh prodcut. get the prodcut detail first of all.
		$('#page_title').text("Detail of order #" + order_id);
		$('#order_number').append("#" + order_id);
		getOrderDetail();
	} //otehrwise, create a new prodcut.



	function getUrlParameter(sParam) {
		var sPageURL = window.location.search.substring(1);
		var sURLVariables = sPageURL.split('&');
		for (var i = 0; i < sURLVariables.length; i++) {
			var sParameterName = sURLVariables[i].split('=');
			if (sParameterName[0] == sParam) {
				return sParameterName[1];
			}
		}
	}

	function getOrderDetail() {
		var get_orderurl = server + order_detail + "&order_id=" + order_id +
			'&api_key=' + api_key + '&auth_token=' + getCookie('token');
		console.log(get_orderurl);

		$.getJSON(get_orderurl, function(data) {
			console.log(JSON.stringify(data));
			if (data.status == 0) { //read prodcut detail successfully.
				$('#order_date').append(data.result.purchased_time_friendly);
				$('#order_status').val(data.result.status.toLowerCase());

				var subtotal = 0;

				var product_price = data.result.product_price;
				var product_quantity = data.result.product_quantity;
				var product_price = product_price * product_quantity;
				subtotal += product_price;

				var item = '<div class="row"><div class="col-lg-11"><big>' + data.result.product_name +
					'</big></div><div class="col-lg-1"><big>$' + product_price +
					'</big></div></div><div class="row"><div class="col-lg-4">quantity: ' + product_quantity +
					'</div><div class="col-lg-4">price: ' + product_price + '</div></div><br /><br />';
				$('#items').append(item);


				$('#shipping_vendor').val(data.result.shipping_vendor);
				$('#shipping_tracknumber').val(data.result.tracking_number);
				$('#shipping_speed').val(data.result.shipping_speed);

				$('#summary_subtotal').append("$" + subtotal);

				var coupon = data.result.coupon_discount;
				$('#summary_coupon').append("-$" + coupon);

				var shippment = data.result.shipping_cost;
				$('#summary_shipment').append("$" + shippment);

				var total_before_tax = subtotal + shippment - coupon;
				var total = total_before_tax + 0; //tax is included in the price.
				//$('#summary_total-before_tax').append(total_before_tax);
				$('#summary_grand_total').append("$" + total);

				$('#billing_method').append(data.result.payment_method);
				$('#billing_cardnumber').append(data.result.charge_card_name);

				var shippment_addr = '<address>' + data.result.shipping_address.addressline1 +
					'<br/> ' + data.result.shipping_address.city + ', ' + data.result.shipping_address.state +
					', ' + data.result.shipping_address.zipcode + '</adress>'

				$('#shipping_address').append(shippment_addr);

			}
		});
	}


	//	$('#submit').click(function() {
	//
	//		var formUrl = server + order_post + '&id=' + order_id + '&status=' + $('#order_status').val() +
	//			'&api_key=' + api_key + '&auth_token=' + getCookie('token');
	//		console.log(formUrl);
	//		
	//		$.ajax({
	//			url: formUrl,
	//			type: "POST",
	//			processData: false,
	//			contentType: false,
	//			crossDomain: true,
	//			success: function(data, textStatus, jqXHR) {
	//				if (data.status == 0) {
	//					//console.log('read result from server: ' + data.result);
	//					BootstrapDialog.alert('The order is changed.');
	//				} else {
	//					BootstrapDialog.alert('There is some error during change the order, error message =' +
	//						data.message);
	//				}
	//			},
	//			error: function(jqXHR, textStatus, errorThrown) {
	//				console.log(textStatus);
	//				console.log(jqXHR);
	//				BootstrapDialog.alert('There is some error during change the order, error=' + textStatus);
	//			}
	//		});
	//		
	//	});




	$('#submit').click(function() {
		var formUrl = server + order_post + '&api_key=' + api_key + '&auth_token=' + getCookie('token');
		console.log(formUrl);
//		var formData = 'order_id=' + order_id + '&status=' + $('#order_status').val() +
//			'&shipping_vendor=' + $('#shipping_vendor').val() +
//			'&track_number=' + $('#shipping_tracknumber').val() +
//			'&shipping_speed=' + $('#shipping_speed').val();
		
		var formData = new FormData();
		formData.append("order_id", order_id);
		formData.append("status", $('#order_status').val());
		formData.append("shipping_vendor", $('#shipping_vendor').val());
		formData.append("track_number", $('#shipping_tracknumber').val() );
		formData.append("shipping_speed", $('#shipping_speed').val());
			
		console.log(formData);

		$.ajax({
			url: formUrl,
			type: "POST",
			data: formData,
			processData: false,
			contentType: false,
			crossDomain: true,
			success: function(data, textStatus, jqXHR) {
				//console.log(JSON.stringify(data));
				if (data.status == 0) {
					//console.log('read result from server: ' + data.result);
					BootstrapDialog.alert('The order is changed.');
				} else {
					BootstrapDialog.alert('There is some error during change the order, error message =' +
						JSON.stringify(data));
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				console.log(textStatus);
				console.log(jqXHR);
				console.log(errorThrown.toString());

				if (textStatus != undefined) {
					BootstrapDialog.alert('There is some error during change the order.  error=' + textStatus);
				} else if (errorThrown != undefined) {
					BootstrapDialog.alert('There is some error during change the order.  error=' + errorThrown.toString());
				} else {
					BootstrapDialog.alert('There is some error during change the order. ');
				}
			}
		});

	});

})