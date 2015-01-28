// Sets the min-height of #page-wrapper to window size
$(function() {
	$('#delete').hide();
	$('#view').hide();


	var ideas = $('#tips').DataTable({
		"ajax": {
			//            "url": server + 'services/api/rest/json/?method=product.get_posts&api_key=' +
			//				api_key + '&auth_token=' + token,
			"url": server + "services/api/rest/json/?method=ideas.get_posts&limit=0&context=mine&api_key=" +
				api_key + '&auth_token=' + getCookie('token'),
			"dataSrc": "result.tips"
		},
		"stateSave": true,
		"processing": true,
		"columns": [{
			"data": "tip_id"
		}, {
			"data": "tip_category"
		}, {
			"data": "tip_title"
		}, {
			"data": "likes_number"
		}, {
			"data": "comments_number"
		}]
	});
	$('#tips tbody').on('click', 'tr', function() {
		if ($(this).hasClass('selected')) {
			$(this).removeClass('selected');
			$('#delete').hide();
			$('#view').hide();
		} else {
			ideas.$('tr.selected').removeClass('selected');
			$(this).addClass('selected');
			$('#delete').show();
			$('#view').show();
		}
	});


	$('#tips tbody').on('dblclick', 'tr', function() {

		ideas.$('tr.selected').removeClass('selected');
		$(this).addClass('selected');
		$('#delete').show();
		$('#view').show();
		openIdea();
	});


	$(document).keypress(function(e) {
		if (e.which == 13) {
			var row = ideas.row('.selected');
			if (row.data() != undefined) {
				var pid = row.data().tip_id;
				//console.log("selected prodcut_id=" + row.data().product_id);
				if (pid != undefined) {
					openIdea();
				}
			}
		}
	});


	$('#delete').click(function() {
		BootstrapDialog.confirm('Are you sure to delete?', function(result) {
			if (result) {
				deleteIdea();
			}
		});

	});

	$('#view').click(function() {
		openIdea();
	});

	function openIdea() {
		var row = ideas.row('.selected');
		//console.log("selected prodcut_id=" + row.data().tip_id);
		window.location.href = "edit_tutorial.html?tip_id=" + row.data().tip_id;
	}

	function deleteIdea() {
		var row = ideas.row('.selected');
		//console.log("selected tip_id=" + row.data().tip_id);

		var formUrl = server + idea_delete + '&tip_id=' + row.data().tip_id + '&api_key=' + api_key + '&auth_token=' + getCookie('token');
		//console.log(formUrl);

		//console.log('token=' + getCookie('token'));

		$.ajax({
			url: formUrl,
			type: "POST",
			contentType: "application/x-www-form-urlencoded",
			dataType: "json",
			//data: 'tip_id=' + row.data().tip_id,
			success: function(data, textStatus, jqXHR) {
				//data: return data from server
				//console.log(data);
				if (data.status == 0 && data.result == true) {
					//console.log('read result from server: ' + data.result.toString())
					row.remove().draw(false);
					$('#delete').hide();
					$('#view').hide();
				} else {
					if (data.result.message != undefined) {
						BootstrapDialog.alert('Can not delet the selected idea, status=' +
							data.status + "  message=" + data.result.message);
					} else {
						BootstrapDialog.alert('Can not delet the selected idea, status=' +
							data.status);
					}
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				console.log(textStatus);
				console.log(jqXHR);
				BootstrapDialog.alert('Can not delet the selected idea, error=' + textStatus);
			}
		});

		//formUrl = "http://social.routzi.com/services/api/rest/json/?method=ideas.delete_tip&tip_id=1485&api_key=badb0afa36f54d2159e599a348886a7178b98533&auth_token=899426c9da8c4517a10d9305cee1cb8e";
		//		$.post(formUrl, function(data) {
		//			if (data.status == 0 && data.result.success) {
		//				console.log('read result from server: ' + data.result.toString())
		//				row.remove().draw(false);
		//				$('#delete').hide();
		//				$('#view').hide();
		//			} else {
		//				BootstrapDialog.alert('Can not delet the selected idea, status=' +
		//					data.status + "  message=" + data.result.message);
		//			}
		//		}, "application/json").fail(function(textStatus, errorThrown) {
		//			console.log(textStatus);
		//			BootstrapDialog.alert('Can not delet the selected idea, error=' + textStatus);
		//		});
	}
})