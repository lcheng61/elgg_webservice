// Sets the min-height of #page-wrapper to window size
$(function() {

	var is_user_exists = false;
	var original_username;
	getProfile();


	function getProfile() {
		var get_url = server + user_get_profile + '&api_key=' + api_key + '&auth_token=' + getCookie('token');
		//console.log(get_url);

		$.getJSON(get_url, function(data) {
			//console.log(JSON.stringify(data));
			if (data.status == 0) { //read prodcut detail successfully.
				original_username = data.result.username;
				$('#username').val(original_username);
				$('#nick_name').val(data.result.name);


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
			if (is_user_exists == false) {
				submit_form();
			} else {
				BootstrapDialog.alert('Username already exists. Please change to another one.');
			}
			return false; // Will stop the submission of the form
		}

	});


	function submit_form() {
		var formUrl = server + user_edit_profile + '&api_key=' + api_key + '&auth_token=' + getCookie('token');
		console.log(formUrl);

		profile = {
			"name": $('#nick_name').val(),
			"username": $('#username').val(),
			"password": $('#password1').val()

		}

		var formData = new FormData();
		formData.append("profile", JSON.stringify(profile));
		console.log("profile: " + JSON.stringify(profile));

		$.ajax({
			url: formUrl,
			type: "POST",
			//data: 'message:' + message,
			data: formData,
			//data: 'profile:' + JSON.stringify(profile),
			processData: false,
			contentType: false,
			crossDomain: true,
			success: onSubmitSuccess,
			error: onError
		});
	}




	function onSubmitSuccess(data, statusText, jqXHR) {
		console.log(data);
		if (data.status == 0) {
			console.log('read result from server: ' + JSON.stringify(data.result));
			BootstrapDialog.alert('Profile is updated.');
		} else {
			BootstrapDialog.alert('There is some error during save profile, error message =' +
				data.message);
		}
	}

	function onError(jqXHR, textStatus, errorThrown) {
		BootstrapDialog.alert('There is some error during save profile, error=' + textStatus);
	}


	$("#name").blur(function() {
		if (original_username != $('#name').val()) {
			checkUserAvailability();
		}
	});

	function checkUserAvailability() {
		var user_availability_url = server + check_user_availability + "&username=" + $("#name").val();
		console.log(user_availability_url);

		$.getJSON(user_availability_url, function(data) {
			console.log(JSON.stringify(data));
			if (data.status == 0 && data.result != undefined) {
				//get return for the user availability successfully.
				if (data.result == 0) { //user exists.
					is_user_exists = true;
					$("#name_status_button").attr("class", "btn btn-warning");
					$("#name_status_icon").removeClass("glyphicon glyphicon-ok");
					$("#name_status_icon").addClass("glyphicon glyphicon-ban-circle");
				} else { //user does not exist.
					is_user_exists = false;
					$("#name_status_button").attr("class", "btn btn-success");
					$("#name_status_icon").removeClass("glyphicon glyphicon-ban-circle");
					$("#name_status_icon").addClass("glyphicon glyphicon-ok");
				}

			} else {
				is_user_exists = true;
				//BootstrapDialog.alert('Could not check user name from server.');
			}
		});
	}

})