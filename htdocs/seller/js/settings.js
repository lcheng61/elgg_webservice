// Sets the min-height of #page-wrapper to window size
$(function() {
	getSettings();


	$(document).on('click', '.panel-body > .btn-block', function() {
		showUrlInputDialog($(this));
	});

	function showUrlInputDialog(button) {
		BootstrapDialog.show({
			title: 'Input URL',
			//cssClass: "modal-dialog",
			message: '<form class="form-horizontal"> ' +
				'<input id="url" name="url" type="text" placeholder="input url here" class="form-control input-md"> ' +
				'</form>',
			buttons: [{
				label: 'Save',
				cssClass: 'btn-primary',
				action: function(dialogItself) {
					var url = $('#url').val();
					console.log("url=" + url);

					$(button).prevAll().each(function() {
						if ($(this).is("img")) {
							console.log("It is img tag.");
							$(this).attr("src", url);
						}
					});


					dialogItself.close();
				}
			}, {
				label: 'Cancel',
				action: function(dialogItself) {
					dialogItself.close();
				}
			}]
		});
	}


	$('#thesameaddress').change(function() {
		setTheSameAddress($(this).is(":checked"));
	});

	function setTheSameAddress(theSameAddress) {
		$('#billing_address1').prop("disabled", theSameAddress);
		$('#billing_address2').prop("disabled", theSameAddress);
		$('#billing_city').prop("disabled", theSameAddress);
		$('#billing_state').prop("disabled", theSameAddress);
		$('#billing_name').prop("disabled", theSameAddress);
		$('#billing_country').prop("disabled", theSameAddress);
		$('#billing_zip').prop("disabled", theSameAddress);
		$('#billing_phone').prop("disabled", theSameAddress);
	}

	function getSettings() {
		var get_url = server + user_get_settings + '&api_key=' + api_key + "&username=" + getCookie("username");
		console.log(get_url);

		$.getJSON(get_url, function(data) {
			console.log(JSON.stringify(data));


			//read settings successfully.
			if (data.status == 0 && data.result != undefined) {

				var jsonResult = JSON.parse(data.result);
				//Update company address.

				$("#img1").attr("src", jsonResult.logo);

				if (jsonResult.company != undefined) {
					$('#company_name').val(jsonResult.company.name);
					$('#company_address1').val(jsonResult.company.address_1);
					$('#company_address2').val(jsonResult.company.address_2);
					$('#company_city').val(jsonResult.company.city);
					$('#company_state').val(jsonResult.company.state);
					$('#company_country').val(jsonResult.company.country);
					$('#company_zip').val(jsonResult.company.zipcode);
					$('#company_phone').val(jsonResult.company.phone);
				}

				if (jsonResult.bill) {
					$('#billing_name').val(jsonResult.bill.name);
					$('#billing_address1').val(jsonResult.bill.address_1);
					$('#billing_address2').val(jsonResult.bill.address_2);
					$('#billing_city').val(jsonResult.bill.city);
					$('#billing_state').val(jsonResult.bill.state);
					$('#billing_country').val(jsonResult.bill.country);
					$('#billing_zip').val(jsonResult.bill.zipcode);
					$('#billing_phone').val(jsonResult.bill.phone);
				}

				if (jsonResult.shipping_policy) {
					$('#free_shipping_quantity_limit').val(jsonResult.shipping_policy.free_shipping_quantity_limit);
					$('#free_shipping_cost_limit').val(jsonResult.shipping_policy.free_shipping_cost_limit);
					$('#shipping_fee').val(jsonResult.shipping_policy.shipping_fee);
				}
				$('#currency').val(jsonResult.currency);
				$('#message').val(jsonResult.customized_text);
				$('#return_policy').val(jsonResult.return_policy);
			}
		});
	}

	$.validate({
		form: '#settings_form',
		modules: 'location, date, security, file',
		onModulesLoaded: function() {
			//$('#country').suggestCountry();
		},
		onError: function() {},
		onSuccess: function() {
			submit_form();
			return false; // Will stop the submission of the form
		}

	});


	function submit_form() {
		var formUrl = server + user_set_settings + '&api_key=' + api_key + '&auth_token=' + getCookie('token');
		console.log(formUrl);



		var image_url = $("#img1").attr("src");
		if (image_url != undefined && image_url.indexOf("data:image") >= 0) {
			image_url = "";
		}

		var message = {
			"logo": image_url,
			"company": {
				"name": $('#company_name').val(),
				"address_1": $('#company_address1').val(),
				"address_2": $('#company_address2').val(),
				"city": $('#company_city').val(),
				"state": $('#company_state').val(),
				"zipcode": $('#company_zip').val(),
				"country": $('#company_country').val(),
				"phone": $('#company_phone').val()
			},
			"bill": {
				"name": $('#billing_name').val(),
				"address_1": $('#billing_address1').val(),
				"address_2": $('#billing_address2').val(),
				"city": $('#billing_city').val(),
				"state": $('#billing_state').val(),
				"zipcode": $('#billing_zip').val(),
				"country": $('#billing_country').val(),
				"phone": $('#billing_phone').val()
			},
			"shipping_policy": {
				"free_shipping_quantity_limit": $('#free_shipping_quantity_limit').val(),
				"free_shipping_cost_limit": $('#free_shipping_cost_limit').val(),
				"shipping_fee": $('#shipping_fee').val()
			},
			"currency": $('#min_free_shipping_limit').val(),
			"customized_text": $('#message').val(),
			"return_policy": $('#return_policy').val()
		}

		if ($("#thesameaddress").prop("checked", true)) {
			message.bill = message.company;
		}

		var messageStr = JSON.stringify(message);
		console.log(messageStr);

		var formData = new FormData();
		formData.append("message", messageStr);


		$.ajax({
			url: formUrl,
			type: "POST",
			//data: 'message:' + message,
			data: formData,
			processData: false,
			contentType: false,
			crossDomain: true,
			success: function(data, textStatus, jqXHR) {
				//data: return data from server
				console.log('read result from server: ' + JSON.stringify(data));
				if (data.status == -20) {
					BootstrapDialog.alert('You have signed out. Please sign in first.');
				} else if (data.status == 0) {
					idea_id = data.result.idea_id;
					$('#idea_id').val(idea_id);
					BootstrapDialog.alert('Data is saved.');
				} else {
					BootstrapDialog.alert(data.message);
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				//console.log(textStatus);
				//console.log(jqXHR);
				BootstrapDialog.alert(textStatus);
			}
		});
	}


	$('#deleteImage1').click(function() { //delete the uploaded1 image
		delete_image(1);
	});


	$("#upload1").change(function() {
		readURL(this, "img1");
	});


	function readURL(input, imageId) {
		if (input.files && input.files[0]) {
			console.log(input.files[0]);

			//Clear current image url.
			$('#img' + id).attr("src", 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==');


			//Upload to server.
			var url = window.URL.createObjectURL(input.files[0]);
			
			//display the uploaded logo image.
			$('#' + imageId).attr('src', url);
		}
	}

	function delete_image(id) {
		var src = $('#img' + id).attr("src");
		console.log("image src=" + src);

		$("#upload" + id).val("");
		if (src == undefined || src.length < 1) {
			return;
		}

		$('#img' + id).attr("src", 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==');
	}

})