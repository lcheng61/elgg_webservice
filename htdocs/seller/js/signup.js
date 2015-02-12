// Sets the min-height of #page-wrapper to window size
$(function() {

	var is_user_exists = false;
	var is_user_email_exists = false;


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
			if (is_user_exists != false) {
				BootstrapDialog.alert('Username already exists. Please change to another one.');

			} else 	if (is_user_email_exists != false) {
				BootstrapDialog.alert('Email already exists. Please change to another one.');
			} else {
				submit_form();
			}
			return false; // Will stop the submission of the form
		}

	});


	function submit_form() {
		var formUrl = server + user_register;
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
	}





	function onSubmitSuccess(data, statusText, jqXHR) {
		console.log(data);
		if (data.status == 0) {
			console.log('read result from server: ' + JSON.stringify(data.result));
			console.log("token = " + data.result.token);

			setCookie('username', $('#name').val(), 1000);
			setCookie('token', data.result.token, 1000);
			window.location.href = 'index.html';

		} else {
			BootstrapDialog.alert('There is some error during save profile, error message =' +
				data.message);
		}
	}

	function onError(jqXHR, textStatus, errorThrown) {
		BootstrapDialog.alert('There is some error during save profile, error=' + textStatus);
	}


	$("#name").blur(function() {
		checkUserAvailability();
	});

	$("#email").blur(function() {
		checkUserEmailAvailability();
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

	function checkUserEmailAvailability() {
		var user_email_availability_url = server + check_user_email_availability + "&email=" + $("#email").val();
		console.log(user_email_availability_url);

		$.getJSON(user_email_availability_url, function(data) {
			console.log(JSON.stringify(data));
			if (data.status == 0 && data.result != undefined) {
				//get return for the user availability successfully.
				if (data.result == 0) { //user email exists.
					is_user_email_exists = true;
					$("#email_status_button").attr("class", "btn btn-warning");
					$("#email_status_icon").removeClass("glyphicon glyphicon-ok");
					$("#email_status_icon").addClass("glyphicon glyphicon-ban-circle");
				} else { //user email does not exist.
					is_user_email_exists = false;
					$("#email_status_button").attr("class", "btn btn-success");
					$("#email_status_icon").removeClass("glyphicon glyphicon-ban-circle");
					$("#email_status_icon").addClass("glyphicon glyphicon-ok");
				}

			} else {
				is_user_email_exists = true;
				//BootstrapDialog.alert('Could not check user name from server.');
			}
		});
	}


});