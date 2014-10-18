<?php
/**
 * Elgg Market Plugin
 * @package market
 */

?>

.market_pricetag {
	font-weight: bold;
	color: #ffffff;
	background:#00a700;
	border: 1px solid #00a700;
	-webkit-border-radius: 4px; 
	-moz-border-radius: 4px;
	width: auto;
	height: 12px;
	padding: 2px 10px 2px 10px;
	margin:10px 0 10px 0;
}
.market_soldcounttag {
	font-weight: bold;
	color: #ffffff;
	background:#00a700;
	border: 1px solid #00a700;
	-webkit-border-radius: 4px; 
	-moz-border-radius: 4px;
	width: auto;
	height: 12px;
	padding: 2px 10px 2px 10px;
	margin:10px 0 10px 0;
}
.market-image-popup {
	max-width: 100%;
}
.market-category-menu-item {
	line-height: 21px;
	display: block;
	text-decoration: none;
	padding-left: 3px;
}
.market-category-menu-item:hover {
	background-color: #dedede;
	text-decoration: none;
}
.market-category-menu-item.selected {
	background-color: #dedede;
	text-decoration: none;
}
.market-image-block > .elgg-image {
	min-width: 208px;
	text-align: center;
}
.market-image-block > .elgg-image-alt {
	margin: 25px 25px 0 0;
}
.market-river-image {
	width: 60px;
	height: 60px;
}
.market-form-image {
	width: 75px;
	height: 75px;
}
.market-thumbnail {
	cursor:url(<?php echo elgg_get_site_url(); ?>mod/market/graphics/zoom_in.png),url(<?php echo elgg_get_site_url(); ?>mod/market/graphics/zoom_in.png),auto;
	margin-right: 5px;
}
/* Special for small devices */
@media (max-width: 600px) {
	.market-item-list > .elgg-image-block > .elgg-image {
		max-width: 100px;
	}
	img.market-image-list {
		width: 100%;
	}
	.market-image-block > .elgg-image {
		min-width: 104px;
		width: 104px;
		margin-right: 15px;
	}
	.market-image-block > .elgg-image > img.elgg-photo {
		width: 100%;
	}
}
