//product server
var server = 'http://m.lovebeauty.me/';
var api_key = '87573c9e87172e86b8a3e99bd73f1d9e9c19086b';

//development server
//var server = 'http://social.routzi.com/'
//var server = 'http://www.lovebeauty.me/'
//var api_key = 'badb0afa36f54d2159e599a348886a7178b98533';


//dev server
//var server = 'http://dev-lovebeauty.rhcloud.com/';
//var api_key = '902a5f73385c0310936358c4d7d58b403fe2ce93';






var get_token = 'services/api/rest/json/?method=auth.gettoken2';
var signout = 'services/api/rest/json/?method=user.logout';


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
var check_user_email_availability = 'services/api/rest/json/?method=user.check_email_availability';

var statistics = 'services/api/rest/json/?method=payment.analyze.seller_order';
var user_get_settings = 'services/api/rest/json/?method=user.get_seller_setting';
var user_set_settings = 'services/api/rest/json/?method=user.set_seller_setting';
is_user_login = 'services/api/rest/json/?method=site.test_auth';

var username;
var token;

$(function() {

	$('#side-menu').metisMenu({
		toggle: false
	});
	$('#side-menu').find("li > ul").collapse("show");


	$('#signout').click(function() { //delete the uploaded4 image
		logout();

	});


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
	if (window.location.href.indexOf("signup.html") < 0 && window.location.href.indexOf("login.html") < 0 && window.location.href.indexOf("reset_password.html") < 0 && (username == undefined || username == "")) {
		window.location.href = "login.html"
	}

	isUserLogin(function(isLogin) {
		if (window.location.href.indexOf("signup.html") < 0 && window.location.href.indexOf("login.html") < 0 && window.location.href.indexOf("reset_password.html") < 0 && !isLogin) {
			window.location.href = "login.html"
		}
	});

})


function logout() {
	setCookie('username', "", 1000);
	setCookie('token', '', 1000);

	var formUrl = server + signout + '&api_key=' + api_key + '&auth_token=' + getCookie('token');
	//console.log(get_url);


	$.ajax({
		url: formUrl,
		type: "POST",
		//data: 'username=robin123&password=robin123',
		//data: $('#login_form').serialize(),
		crossDomain: true,
		success: function(data, textStatus, jqXHR) {
			//data: return data from server
			console.log(JSON.stringify(data));
			//alert(data.result);
			if (data.status == 0) {
				setCookie('username', "", 1000);
				setCookie('token', '', 1000);
			}
			window.location.href = 'login.html';
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus);
			console.log(jqXHR);
		}
	});
}


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


function isUserLogin(callback) {
	var get_user_auth_url = server + is_user_login + '&api_key=' + api_key + '&auth_token=' + getCookie('token');

	$.getJSON(get_user_auth_url, function(data) {
		console.log(JSON.stringify(data));
		if (data.status == 0) { //read prodcut detail successfully.

			callback(true);
		} else {
			callback(false);
		}
	});
}

/**
 *
 *  Base64 encode / decode
 *  http://www.webtoolkit.info/
 *
 **/
var Base64 = {

	// private property
	_keyStr: "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",

	// public method for encoding
	encode: function(input) {
		var output = "";
		var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
		var i = 0;

		input = Base64._utf8_encode(input);

		while (i < input.length) {

			chr1 = input.charCodeAt(i++);
			chr2 = input.charCodeAt(i++);
			chr3 = input.charCodeAt(i++);

			enc1 = chr1 >> 2;
			enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
			enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
			enc4 = chr3 & 63;

			if (isNaN(chr2)) {
				enc3 = enc4 = 64;
			} else if (isNaN(chr3)) {
				enc4 = 64;
			}

			output = output +
				this._keyStr.charAt(enc1) + this._keyStr.charAt(enc2) +
				this._keyStr.charAt(enc3) + this._keyStr.charAt(enc4);

		}

		return output;
	},

	// public method for decoding
	decode: function(input) {
		var output = "";
		var chr1, chr2, chr3;
		var enc1, enc2, enc3, enc4;
		var i = 0;

		input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

		while (i < input.length) {

			enc1 = this._keyStr.indexOf(input.charAt(i++));
			enc2 = this._keyStr.indexOf(input.charAt(i++));
			enc3 = this._keyStr.indexOf(input.charAt(i++));
			enc4 = this._keyStr.indexOf(input.charAt(i++));

			chr1 = (enc1 << 2) | (enc2 >> 4);
			chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
			chr3 = ((enc3 & 3) << 6) | enc4;

			output = output + String.fromCharCode(chr1);

			if (enc3 != 64) {
				output = output + String.fromCharCode(chr2);
			}
			if (enc4 != 64) {
				output = output + String.fromCharCode(chr3);
			}

		}

		output = Base64._utf8_decode(output);

		return output;

	},

	// private method for UTF-8 encoding
	_utf8_encode: function(string) {
		string = string.replace(/\r\n/g, "\n");
		var utftext = "";

		for (var n = 0; n < string.length; n++) {

			var c = string.charCodeAt(n);

			if (c < 128) {
				utftext += String.fromCharCode(c);
			} else if ((c > 127) && (c < 2048)) {
				utftext += String.fromCharCode((c >> 6) | 192);
				utftext += String.fromCharCode((c & 63) | 128);
			} else {
				utftext += String.fromCharCode((c >> 12) | 224);
				utftext += String.fromCharCode(((c >> 6) & 63) | 128);
				utftext += String.fromCharCode((c & 63) | 128);
			}

		}

		return utftext;
	},

	// private method for UTF-8 decoding
	_utf8_decode: function(utftext) {
		var string = "";
		var i = 0;
		var c = c1 = c2 = 0;

		while (i < utftext.length) {

			c = utftext.charCodeAt(i);

			if (c < 128) {
				string += String.fromCharCode(c);
				i++;
			} else if ((c > 191) && (c < 224)) {
				c2 = utftext.charCodeAt(i + 1);
				string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
				i += 2;
			} else {
				c2 = utftext.charCodeAt(i + 1);
				c3 = utftext.charCodeAt(i + 2);
				string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
				i += 3;
			}

		}

		return string;
	}

}