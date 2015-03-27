// Sets the min-height of #page-wrapper to window size
$(function() {

	//var formUrl = "http://social.routzi.com/services/api/rest/json/?method=user.register.email"
//	var formUrl = "http://www.lovebeauty.me/services/api/rest/json/?method=user.register.email"
	var formUrl = "http://m.lovebeauty.me/services/api/rest/json/?method=user.register.email"

	$('#submit-signup').click(function() {
		console.log("sign up form, submit button is clicked");
		var options = {
			//beforeSubmit:  showRequest,  // pre-submit callback 
			success: function(data, statusText, jqXHR) {
				console.log(data);
				if (data.status == 0) {
					console.log('read result from server: ' + data.result);
					BootstrapDialog.alert('You have signed up successfully, we will contact you shortly.');
				} else {
					BootstrapDialog.alert('Could not sign up, error message =' +
						data.message);
				}
			},
			error: onError,

			// other available options: 
			url: formUrl,
			type: "post"             // 'get' or 'post', override for form's 'method' attribute 
			//dataType:  null        // 'xml', 'script', or 'json' (expected server response type) 
			//clearForm: true        // clear all form fields after successful submit 
			//resetForm: true        // reset the form after successful submit 

			// $.ajax options can be used here too, for example: 
			//timeout:   3000 
		};

		$('#form-signup').ajaxSubmit(options);

	});



	$('#submit-contactus').click(function() {
		console.log("contact us form, submit button is clicked");
		var options = {
			//beforeSubmit:  showRequest,  // pre-submit callback 
			success: function(data, statusText, jqXHR) {
				console.log(data);
				if (data.status == 0) {
					console.log('read result from server: ' + data.result);
					BootstrapDialog.alert('Done, we will contact you shortly.');
				} else {
					BootstrapDialog.alert('Could not send message, error message =' +
						data.message);
				}
			},
			error: onError,

			// other available options: 
			url: formUrl,
			type: "post"             // 'get' or 'post', override for form's 'method' attribute 
			//dataType:  null        // 'xml', 'script', or 'json' (expected server response type) 
			//clearForm: true        // clear all form fields after successful submit 
			//resetForm: true        // reset the form after successful submit 

			// $.ajax options can be used here too, for example: 
			//timeout:   3000 
		};

		$('#form-contactus').ajaxSubmit(options);

	});



	function onError(jqXHR, textStatus, errorThrown) {
		BootstrapDialog.alert('There is some error on the page, error=' + textStatus);
	}


})
