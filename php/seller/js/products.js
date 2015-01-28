// Sets the min-height of #page-wrapper to window size
$(function() {
	$('#delete').hide();
	$('#view').hide();


    var formUrl = server + 'services/api/rest/json/?method=product.get_posts&offset=0&limit=0&context=user&username=' + 
			     getCookie('username') + '&api_key=' +
				api_key + '&auth_token=' + getCookie('token');
	console.log("form url=" + formUrl);
	
	var tableProducts = $('#products').DataTable({
		"ajax": {
			//"url": "js/objects_deep_loop.txt",
			"url": formUrl,
			"dataSrc": "result.products"
		},

		"stateSave": true,
		"processing": true,
		"columns": [{
				"data": "product_id"
			}, {
				"data": "product_name"
			}, {
				"data": "product_category"
			}, {
				"data": "product_price"
			}, {
				"data": "sold_number"
			}]
	});
	$('#products tbody').on('click', 'tr', function() {
		if ($(this).hasClass('selected')) {
			$(this).removeClass('selected');
			$('#delete').hide();
			$('#view').hide();
		} else {
			tableProducts.$('tr.selected').removeClass('selected');
			$(this).addClass('selected');
			$('#delete').show();
			$('#view').show();
		}
	});

	$('#products tbody').on('dblclick', 'tr', function() {

		tableProducts.$('tr.selected').removeClass('selected');
		$(this).addClass('selected');
		$('#delete').show();
		$('#view').show();
		openProduct();
	});


	$(document).keypress(function(e) {
		if (e.which == 13) {
			var row = tableProducts.row('.selected');
			if (row.data() != undefined) {
				var pid = row.data().product_id;
				//console.log("selected prodcut_id=" + row.data().product_id);
				if (pid != undefined) {
					openProduct();
				}
			}
		}
	});

	$('#delete').click(function() {
		BootstrapDialog.confirm('Are you sure to delete?', function(result) {
			if (result) {
				deleteProduct();
			}
		});

	});

	$('#view').click(function() {
		openProduct();
	});

	function openProduct() {
		var row = tableProducts.row('.selected');
		console.log("selected prodcut_id=" + row.data().product_id);
		window.location.href = "edit_product.html?product_id=" + row.data().product_id;
	}

	function deleteProduct() {
		var row = tableProducts.row('.selected');
		console.log("selected prodcut_id=" + row.data().product_id);

		var formUrl = server + product_delete + '&api_key=' + api_key + '&auth_token=' + getCookie('token');
		console.log(formUrl);

		console.log('token=' + getCookie('token'));

		$.ajax({
			url: formUrl,
			type: "POST",
			data: 'product_id=' + row.data().product_id,
			crossDomain: true,
			success: function(data, textStatus, jqXHR) {
				//data: return data from server
				console.log(data);
				if (data.status == 0) {
					console.log('read result from server: ' + data.result)
					row.remove().draw(false);
					$('#delete').hide();
					$('#view').hide();
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				console.log(textStatus);
				console.log(jqXHR);
			}
		});
	}

})