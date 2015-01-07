
$(function() {
	$('#reset').click(function() {

		var formUrl = server + reset_password + '&api_key=' + api_key + '&auth_token=' + getCookie('token');
		console.log(formUrl);
		

		$.ajax({
			url: formUrl,
			type: "POST",
			//data: 'username=leo123&password=password123',
			//data: 'username=robin&password=robin123',
			data: $('#reset_form').serialize(),
			crossDomain: true,
			success: function(data, textStatus, jqXHR) {
				//data: return data from server
				console.log(data);
				if (data.status == 0) {
					//console.log('read token from server: ' + data.result)
					$("#message").html("Password has been reset. ");
				} else {
					$("#message").html("There is an error during reset password. " + data.message);
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				console.log(textStatus);
				console.log(jqXHR);
			}
		});
	});
})