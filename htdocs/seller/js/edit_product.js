// Sets the min-height of #page-wrapper to window size
$(function() {
	var product;

	$("#description").ckeditor();


	// Add custom validation rule for product name.
	$.formUtils.addValidator({
		name: 'without_double_quota',
		validatorFunction: function(value, $el, config, language, $form) {
			console.log("value=" + value);
			return value.indexOf('"') < 0; 
		},
		errorMessage: 'The product name could not contain " character.'
	});

	// Setup form validation
	$.validate();



	var product_id = getUrlParameter("product_id");
	console.log("prodcut_id=" + product_id);
	if (product_id != undefined && product_id != null) { //edit data for teh prodcut. get the prodcut detail first of all.
		$('#page_title').text("Edit Product");
		$('#product_id').val(product_id);
		getProductDetail();
	} //otehrwise, create a new prodcut.


	console.log("is admin user: " + getCookie('is_admin'));
	if (getCookie('is_admin') !== 'true') {
		$("#is_affiliate_div").hide();
		$("#affiliate_url_div").hide();
		$("#affiliate_image_url_div").hide();
	}

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
				$('#delivery_time').val(data.result.delivery_time);
				$('#shipping_fee').val(data.result.shipping_fee);
				$('#affiliate_product_url').val(data.result.affiliate.affiliate_product_url);
				$('#affiliate_image_url').val(data.result.affiliate.affiliate_image);

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

				var options = data.result.product_options;
				displayOptionsItems(options);
				displayOptionsPreview(options);
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

		//handle delivery time before submit.
		handleDeliveryTime();

		//clear the delivery time before submit when it is affiliate product.
		if ($("#is_affiliate").is(':checked')) {
			$("#delivery_time").val("");
		}

		//update options field.
		$("#options").val(JSON.stringify(getOptionsArray()));

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
			//timeout:   10000 
		};

		$('#edit_form').ajaxSubmit(options);
		//console.log('token=' + getCookie('token'));
	}

	function onSubmitSuccess(data, statusText, jqXHR) {
		if (data.status == 0) {
			//console.log('read result from server: ' + JSON.stringify(data));
			product_id = data.result.product_id;
			$('#product_id').val(product_id);
			BootstrapDialog.alert('The prodcut is added/updated.');
		} else {
			BootstrapDialog.alert(data.message);
		}
	}

	function onError(jqXHR, textStatus, errorThrown) {
		BootstrapDialog.alert(textStatus);
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

		$("#upload" + id).val("");
		if (src == undefined || src.length < 1) {
			return;
		}

		if (src.indexOf("http%3A//localhost") >= 0 || src.indexOf("blob") == 0) {
			$('#img' + id).attr("src", 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==');

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
						BootstrapDialog.alert(data.message);
					}
				},
				error: function(jqXHR, textStatus, errorThrown) {
					console.log(textStatus);
					console.log(jqXHR);
					BootstrapDialog.alert(textStatus);
				}
			});
		}
	}


	$("#delivery_time").blur(function() {
		handleDeliveryTime();
	});

	$('#is_affiliate').change(function() {

		//When isAffiliate is checked, ignore the delivery time if it is null. 
		if ($("#is_affiliate").is(':checked')) {

			if ($("#delivery_time").val() == undefined || $("#delivery_time").val() == "") {
				$("#delivery_time").val("-1");
			}

			console.log("delivery time : " + $("#delivery_time").val());
		}

	});

	// add button click
	$("#add").button().click(function() {
		addOptionsItem("Name", "Value1, Value2");
	});


	//The delete button in the options table.
	$('#table_body').on('click', 'button', function(el) {
		$(this).parent().parent().remove()
	});


	// preview button click
	$("#preview").button().click(function() {
		var options = getOptionsArray();

		displayOptionsPreview(options);
	});


	function addOptionsItem(name, values) {
		$("#table_body").append('<tr><td><input type="text" class="form-control op_name" value="' + name +
			'"></td><td><input type="text" class="form-control op_values" value="' + values +
			'"></td><td><button type="button" class="btn btn-warning">X</button></td></tr>');
	}


	function displayOptionsItems(options) {
		//Clear previous preview result.
		$("#table_body").empty();

		//Display preview result.
		for (i = 0; i < options.length; i++) {
			op = options[i];

			addOptionsItem(op.key, op.values);
		}
	}

	function displayOptionsPreview(options) {
		//Clear previous preview result.
		$("#preview_result").empty();

		//Display preview result.
		for (i = 0; i < options.length; i++) {
			op = options[i];

			$("#preview_result").append('<div class="row"><label for="select1">' + op.key +
				'</label>   <select>' + getOptionString(op.values) + '</select></div>');
		}
	}

	function getOptionString(values) {
		var retstr = "";
		for (j = 0; j < values.length; j++) {
			retstr += '<option value="' + (j + 1) + '">' + values[j] + '</option>';
		}

		return retstr;
	}


	function getOptionsArray() {

		var options = new Array()
		$('#table_body > tr').each(function(key, row) {
			/*console.log(row);*/

			var name = $(row).find(".op_name").val();
			var values = $(row).find(".op_values").val();

			var obj = {
				"key": name,
				"values": values.split(",")
			}

			options.push(obj);
		});

		return options;
	}

	//判断是否为数字
	function IsNum(s) {
		if (s != null && s != "") {
			return !isNaN(s);
		}
		return false;
	}

	function handleDeliveryTime() {
		var dtime = $("#delivery_time").val();
		//console.log("delivery time :" + dtime);
		if (IsNum(dtime)) {
			if (dtime > 1) {
				$("#delivery_time").val(dtime + " business days");
			} else {
				$("#delivery_time").val(dtime + " business day");
			}
		}
		//console.log("delivery time :" + dtime);	
	}

})