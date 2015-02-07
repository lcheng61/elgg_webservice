//var server = 'http://social.routzi.com/'
var server = 'http://www.lovebeauty.me/'
var api_key = 'badb0afa36f54d2159e599a348886a7178b98533';


//dev server
//var server = 'http://dev-lovebeauty.rhcloud.com/';
//var api_key = '902a5f73385c0310936358c4d7d58b403fe2ce93';

var get_token = 'services/api/rest/json/?method=auth.gettoken2';

var reset_password = 'services/api/rest/json/?method=user.request_lost_password';
var product_post = 'services/api/rest/json/?method=product.post';
var product_get = 'services/api/rest/json/?method=product.get_detail';
var product_get_posts = 'services/api/rest/json/?method=product.get_posts';
var product_delete = 'services/api/rest/json/?method=product.delete';
var product_image_delete = 'services/api/rest/json/?method=product.image.delete';
var product_search = 'services/api/rest/json/?method=product.search';


var idea_delete = 'services/api/rest/json/?method=ideas.delete_tip';
var idea_get = 'services/api/rest/json/?method=ideas.get_detail';
var idea_get_posts = 'services/api/rest/json/?method=ideas.get_posts';
var idea_post = 'services/api/rest/json/?method=ideas.post_tip';
//var order_list = 'services/api/rest/json/?method=ideas.post_tip';
var order_detail = 'services/api/rest/json/?method=payment.detail.seller_order';
//var order_post = 'services/api/rest/json/?method=payment.order_update';
var order_post = 'services/api/rest/json/?method=payment.order_shipping_update';


var user_get_profile = 'services/api/rest/json?method=user.get_profile';
var user_edit_profile = 'services/api/rest/json/?method=user.edit_profile';
var user_register = 'services/api/rest/json/?method=user.register';
var check_user_availability = 'services/api/rest/json/?method=user.check_username_availability';

var statistics = 'services/api/rest/json/?method=payment.analyze.seller_order';


var username;
var token;

$(function() {

	$('#side-menu').metisMenu({
		toggle: false
	});
	$('#side-menu').find("li > ul").collapse("show");

});

//Loads the correct sidebar on window load,
//collapses the sidebar on window resize.
// Sets the min-height of #page-wrapper to window size
$(function() {
	$(window).bind("load resize", function() {
		topOffset = 50;
		width = (this.window.innerWidth > 0) ? this.window.innerWidth : this.screen.width;
		if (width < 768) {
			$('div.navbar-collapse').addClass('collapse')
			topOffset = 100; // 2-row-menu
		} else {
			$('div.navbar-collapse').removeClass('collapse')
		}

		height = (this.window.innerHeight > 0) ? this.window.innerHeight : this.screen.height;
		height = height - topOffset;
		if (height < 1) height = 1;
		if (height > topOffset) {
			$("#page-wrapper").css("min-height", (height) + "px");
		}
	});


	username = getCookie("username");
	console.log("username=" + username);
	if (window.location.href.indexOf("signup.html") < 0 && window.location.href.indexOf("login.html") < 0
		&& window.location.href.indexOf("reset_password.html") < 0 && (username == undefined || username == "")) {
		window.location.href = "login.html"
	}

})

function setCookie(c_name, value, expiredays) {　　　　
	var exdate = new Date();　　　　
	exdate.setDate(exdate.getDate() + expiredays);　　　　
	document.cookie = c_name + "=" + escape(value) + ((expiredays == null) ? "" : ";expires=" + exdate.toGMTString());
}

function getCookie(c_name) {　　　　
	if (document.cookie.length > 0) {　　 //先查询cookie是否为空，为空就return ""
		　　　　　　
		c_start = document.cookie.indexOf(c_name + "=")　　 //通过String对象的indexOf()来检查这个cookie是否存在，不存在就为 -1　　
			　　　　　　 if (c_start != -1) {　　　　　　　　
				c_start = c_start + c_name.length + 1　　 //最后这个+1其实就是表示"="号啦，这样就获取到了cookie值的开始位置
					　　　　　　　　 c_end = document.cookie.indexOf(";", c_start)　　 //其实我刚看见indexOf()第二个参数的时候猛然有点晕，后来想起来表示指定的开始索引的位置...这句是为了得到值的结束位置。因为需要考虑是否是最后一项，所以通过";"号是否存在来判断
					　　　　　　　　 if (c_end == -1) c_end = document.cookie.length　　　　　　　　　　
				return unescape(document.cookie.substring(c_start, c_end))　　 //通过substring()得到了值。想了解unescape()得先知道escape()是做什么的，都是很重要的基础，想了解的可以搜索下，在文章结尾处也会进行讲解cookie编码细节
					　　　　　　
			}　　　　
	}　　　　
	return ""
}

function getUrlParameter(sParam) {
	var sPageURL = window.location.search.substring(1);
	var sURLVariables = sPageURL.split('&');
	for (var i = 0; i < sURLVariables.length; i++) {
		var sParameterName = sURLVariables[i].split('=');
		if (sParameterName[0] == sParam) {
			return sParameterName[1];
		}
	}
}
