// Sets the min-height of #page-wrapper to window size
$(function() {
	var product;
	var product_id = getUrlParameter("product_id");
	console.log("prodcut_id=" + product_id);
	if (product_id != undefined && product_id != null) { //edit data for teh prodcut. get the prodcut detail first of all.
		$('#page_title').text("Edit Product");
		$('#product_id').val(product_id);
		getProductDetail();
	} //otehrwise, create a new prodcut.


	console.log("height=" + $(".panel").height());

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

	function getProductDetail() {
		var get_producturl = server + product_get + "&product_id=" + product_id +
			'&api_key=' + api_key + '&auth_token=' + getCookie('token');
		console.log(get_producturl);

		$.getJSON(get_producturl, function(data) {
			console.log(JSON.stringify(data));
			if (data.status == 0) { //read prodcut detail successfully.
				console.log('product title=' + data.result.product_name);
				$('#title').val(data.result.product_name);
				$('#category').val(data.result.category);
				$('#description').val(data.result.product_description);
				$('#price').val(data.result.product_price);
				$('#quantity').val(data.result.quantity);
				if (data.result.tags != undefined) {
					$('#tags').val(data.result.tags);
				}
				$('#delivery').val(data.result.delivery_time);
				$('#affiliate_product_url').val(data.result.affiliate.affiliate_product_url);
				
				if (data.result.affiliate.is_affiliate == 1) {
					$('#is_affiliate').prop('checked', true);
				} else {
					$('#is_affiliate').prop('checked', false);
				}
				
				if (data.result.affiliate.is_archived == 1) {
					$('#is_archived').prop('checked', true);
				} else {
					$('#is_archived').prop('checked', false);
				}
				
				if (data.result.images != undefined) {
					$.each(data.result.images, function(n, url) {
						$('#img' + (n + 1)).attr("src", url + "?" + 100000 * Math.random());
					});
				}


			}
		});
	}

	$.validate({
		form: '#edit_form',
		modules: 'location, date, security, file',
		onModulesLoaded: function() {
			//$('#country').suggestCountry();
		},
		onError: function() {
			//alert('Validation failed');
		},
		onSuccess: function() {
			submit_form();
			return false; // Will stop the submission of the form
		}

	});


	function submit_form() {
		var formUrl = server + product_post + '&api_key=' + api_key + '&auth_token=' + getCookie('token');
		console.log(formUrl);


		var options = {
			//beforeSubmit:  showRequest,  // pre-submit callback 
			success: onSubmitSuccess, // post-submit callback 
			error: onError,

			// other available options: 
			url: formUrl
				//type:      type        // 'get' or 'post', override for form's 'method' attribute 
				//dataType:  null        // 'xml', 'script', or 'json' (expected server response type) 
				//clearForm: true        // clear all form fields after successful submit 
				//resetForm: true        // reset the form after successful submit 

			// $.ajax options can be used here too, for example: 
			//timeout:   3000 
		};

		$('#edit_form').ajaxSubmit(options);
		//console.log('token=' + getCookie('token'));
	}

	function onSubmitSuccess(data, statusText, jqXHR) {
		console.log(data);
		if (data.status == 0) {
			console.log('read result from server: ' + data.result);
			BootstrapDialog.alert('The prodcut is added/updated.');
		} else {
			BootstrapDialog.alert('There is some error during submit the product, error message =' +
				data.message);
		}
	}

	function onError(jqXHR, textStatus, errorThrown) {
		BootstrapDialog.alert('There is some error during submit the product, error=' + textStatus);
	}


	$('#deleteImage1').click(function() { //delete the uploaded1 image
		delete_image(1);
	});

	$('#deleteImage2').click(function() { //delete the uploaded2 image
		delete_image(2);
	});

	$('#deleteImage3').click(function() { //delete the uploaded3 image
		delete_image(3);
	});

	$('#deleteImage4').click(function() { //delete the uploaded4 image
		delete_image(4);
	});


	$("#upload1").change(function() {
		readURL(this, "img1");
	});

	$("#upload2").change(function() {
		readURL(this, "img2");
	});

	$("#upload3").change(function() {
		readURL(this, "img3");
	});

	$("#upload4").change(function() {
		readURL(this, "img4");
	});

	function readURL(input, imageId) {
		if (input.files && input.files[0]) {
			console.log(input.files[0]);

			var url = window.URL.createObjectURL(input.files[0]);
			$('#' + imageId).attr('src', url);
		}
	}

	function delete_image(id) {
		var src = $('#img' + id).attr("src");
		console.log("image src=" + src);
		if (src.indexOf("http%3A//localhost") >= 0 || src.indexOf("blob") == 0) {
			$('#img' + id).attr("src", "");
		} else {

			var formUrl = server + product_image_delete + '&api_key=' + api_key + '&auth_token=' + getCookie('token');
			console.log(formUrl);

			//console.log('token=' + getCookie('token'));

			$.ajax({
				url: formUrl,
				type: "POST",
				data: 'image_id=' + id + '&product_id=' + product_id,
				crossDomain: true,
				success: function(data, textStatus, jqXHR) {
					//data: return data from server
					console.log(data);
					if (data.status == 0) {
						console.log('read result from server: ' + data.result);
						$('#img' + id).attr("src", "");
					} else {
						BootstrapDialog.alert('Could not delete image, error message =' +
							data.message);
					}
				},
				error: function(jqXHR, textStatus, errorThrown) {
					console.log(textStatus);
					console.log(jqXHR);
					BootstrapDialog.alert('There is some error during delete image, error=' + textStatus);
				}
			});
		}
	}

})