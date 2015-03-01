$(function() {

	//callback handler for form submit
	//	$("#login_form").submit(function(e) {
	//		var postData = $(this).serializeArray();
	//		var formURL = $(this).attr("action");
	//		$.ajax({
	//			url: formURL,
	//			type: "POST",
	//			data: postData,
	//			crossDomain: true,
	//			success: function(data, textStatus, jqXHR) {
	//				//data: return data from server
	//			},
	//			error: function(jqXHR, textStatus, errorThrown) {
	//				//if fails      
	//			}
	//		});
	//		e.preventDefault(); //STOP default action
	//		e.unbind(); //unbind. to stop multiple form submit.
	//	});

	$('#login').click(function() {

		submitForm();
	});

	$('#password').on("keypress", function(e) {

		if (e.keyCode == 13) {

			// Cancel the default action on keypress event
			e.preventDefault();

			submitForm();
		}
	});

	function submitForm() {
		var formUrl = server + get_token + "&api_key=" + api_key;

		var username = $('#username').val();

		$.ajax({
			url: formUrl,
			type: "POST",
			//data: 'username=robin123&password=robin123',
			data: 'username=' + username + '&password=' + $('#password').val(),
			//data: $('#login_form').serialize(),
			crossDomain: true,
			success: function(data, textStatus, jqXHR) {
				//data: return data from server
				console.log(JSON.stringify(data));
				//alert(data.result);
				if (data.status == 0) {
					if (data.result.is_seller) {
						setCookie('username', username, 1000);
						//setCookie('username', 'test123', 1000);
						token = data.result.token;
						setCookie('token', data.result.token, 1000);
						window.location.href = 'index.html';
					} else {
						alert('Unfortunately you are not a seller. Please contact business developement department.');
					}
				} else {
					alert('Username or password is invalid.');
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				console.log(textStatus);
				console.log(jqXHR);
			}
		});
	}

})