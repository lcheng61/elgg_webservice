// Sets the min-height of #page-wrapper to window size
$(function() {	
	$( "#datepickerStart" ).datepicker();
	$( "#datepickerEnd" ).datepicker();
	$("#search").click(function(){
		alert("search button is clicked");
	});

    
    
	$('#view').hide();


	var formUrl = server + "services/api/rest/json/?method=payment.list.seller_order&limit=0&api_key=" +
				api_key + '&username='  + getCookie('username') + '&auth_token=' + getCookie('token');
	console.log("form url:  " + formUrl);
				
	var tableOrders = $('#orders').DataTable({
		"ajax": {
			"url": formUrl,
			"dataSrc": "result.product"
		},
		"stateSave": true,
		"processing": true,
		"columns": [{
			"data": "order_guid"
		}, {
			"data": "purchased_time_friendly"
		}, {
			"data": "product_price"
		}, {
			"data": "status"
		}]
	});
	$('#orders tbody').on('click', 'tr', function() {
		if ($(this).hasClass('selected')) {
			$(this).removeClass('selected');
			$('#view').hide();
		} else {
			tableOrders.$('tr.selected').removeClass('selected');
			$(this).addClass('selected');
			$('#view').show();
		}
	});


	$('#orders tbody').on('dblclick', 'tr', function() {

		tableOrders.$('tr.selected').removeClass('selected');
		$(this).addClass('selected');
		$('#view').show();
		openOrder();
	});


	$(document).keypress(function(e) {
		if (e.which == 13) {
			var row = tableOrders.row('.selected');
			if (row.data() != undefined) {
				var pid = row.data().order_guid;
				//console.log("selected prodcut_id=" + row.data().product_id);
				if (pid != undefined) {
					openOrder();
				}
			}
		}
	});


	$('#view').click(function() {
		openOrder();
	});
	
	function openOrder() {
		var row = tableOrders.row('.selected');
		//console.log("selected prodcut_id=" + row.data().tip_id);
		window.location.href = "edit_order.html?order_id=" + row.data().order_guid;
	}
	
})
