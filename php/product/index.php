<?php
$request = $_SERVER["REQUEST_URI"];

$iPod    = stripos($_SERVER['HTTP_USER_AGENT'],"iPod");
$iPhone  = stripos($_SERVER['HTTP_USER_AGENT'],"iPhone");
$iPad    = stripos($_SERVER['HTTP_USER_AGENT'],"iPad");
$Android = stripos($_SERVER['HTTP_USER_AGENT'],"Android");
$webOS   = stripos($_SERVER['HTTP_USER_AGENT'],"webOS");

$path  = parse_url($request, PHP_URL_PATH);
$query = parse_url($request, PHP_URL_QUERY);


if ($iPhone && ($path == "/product/")) {
    header('Location: lovebeauty://product?'.$query);
} else if (strpos($request,'routzi') == false) {
    header('Location: http://m.lovebeauty.me');
}

exit;
?>