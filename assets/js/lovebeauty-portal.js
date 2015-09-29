$(document).ready(function() {
	$window = $(window);

	$(".ipad").addClass("anim");
	$(".ipad-left").addClass("anim");
	$(".text").addClass("anim");
	$(".text-right").addClass("anim");


	$("nav").onePageNav({
		currentClass: 'active',
		changeHash: false,
		scrollSpeed: 750,
		scrollOffset: 5
	});

	$(".post").hover(
		function() {
			$(".post-pic", this).children("img").addClass("anim");
		}, function() {
			$(".post-pic", this).children("img").removeClass("anim");
		});

	$("a.mini-nav").click(function() {
		$("ul.nav").slideToggle("slow");
		return false;
	});
});