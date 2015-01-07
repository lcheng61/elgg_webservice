
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

		var formUrl = server + "services/api/rest/json/?method=auth.gettoken2&api_key=" + api_key;

		$.ajax({
			url: formUrl,
			type: "POST",
			data: 'username=leo123&password=password123',
			//data: 'username=robin&password=robin123',
			//data: $('#login_form').serialize(),
			crossDomain: true,
			success: function(data, textStatus, jqXHR) {
				//data: return data from server
				console.log(data);
				//alert(data.result);
				if (data.status == 0) {
					//console.log('read token from server: ' + data.result)
					//alert($('#remember').is(':checked') );
					if ($('#remember').is(':checked')) {
						setCookie('username', $('#username').val(), 1000);
						setCookie('username', 'leo123', 1000);
					} else {
						setCookie('username', "", 1000);
					}
					token = data.result;
					setCookie('token', data.result, 1000);
					window.location.href = 'index.html';
				} else {
					alert('Username or password is invalid.');
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				console.log(textStatus);
				console.log(jqXHR);
			}
		});
	});


})