// Sets the min-height of #page-wrapper to window size
$(function() {	
	$( "#datepickerStart" ).datepicker();
	$( "#datepickerEnd" ).datepicker();
	$("#search").click(function(){
		alert("search button is clicked");
	});

	var tableOrders = $('#orders').DataTable();
	$('#orders tbody').on( 'click', 'tr', function () {
        if ( $(this).hasClass('selected') ) {
            $(this).removeClass('selected');
        }
        else {
            tableOrders.$('tr.selected').removeClass('selected');
            $(this).addClass('selected');
        }
    } );
	
})
