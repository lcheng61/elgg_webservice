$(function() {
	var revenue_data = [];
	var profit_data = [];
	var cost_data = [];
	getDetail();

	$('#profit_detail').click(function() { //delete the uploaded4 image
		drawChart(profit_data, "Profit");
	});
	$('#profit_arrow_detail').click(function() { //delete the uploaded4 image
		drawChart(profit_data, "Profit");
	});


	$('#cost_detail').click(function() { //delete the uploaded4 image
		drawChart(cost_data, "Cost");
	});

	$('#cost_arrow_detail').click(function() { //delete the uploaded4 image
		drawChart(cost_data, "Cost");
	});


	$('#revenue_detail').click(function() { //delete the uploaded4 image
		drawChart(revenue_data, "Revenue");
	});
	$('#revenue_arrow_detail').click(function() { //delete the uploaded4 image
		drawChart(revenue_data, "Revenue");
	});



	function getDetail() {
		var get_url = server + statistics + '&api_key=' + api_key + '&auth_token=' + getCookie('token');
		//console.log(get_url);

		$.getJSON(get_url, function(data) {
			//console.log(JSON.stringify(data));
			if (data.status == 0) { //read prodcut detail successfully.
				$('#total_orders').text(data.result.total_orders);
				$('#profit').text(data.result.total_profit.toFixed(2));
				$('#cost').text(data.result.total_cost.toFixed(2));
				$('#revenue').text(data.result.total_revenue.toFixed(2));

				calculate_data(data.result.revenue, revenue_data);
				calculate_data(data.result.profit, profit_data);
				calculate_data(data.result.cost, cost_data);

				drawChart(revenue_data, "Revenue");
			} else {
				console.log(JSON.stringify(data));
			}
		});
	}

	function calculate_data(data_from_server, data) {
		var entry = data_from_server[0];

		for (var year in entry) {
			//console.log("year=" + year);
			year_data = entry[year];
			for (month in year_data) {

				value = year_data[month].toFixed(2);
				//console.log("     " + month + " -- " + value);

				var piece = {
					y: year + "-" + month,
					v: value
				}

				data.push(piece);
			}
		}
	}

	function drawChart(data, title) {
		$("#graph").empty();

		Morris.Area({
			element: 'graph',
			data: data,
			xkey: 'y',
			ykeys: ['v'],
			labels: [title]
		});
	}
	
	

})