<?php

function siteURI()
{
	$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
	$domainName = $_SERVER['HTTP_HOST'].'/';
	return $protocol.$domainName;
}

define('SITE_URI', siteURI());
define('SITE_ROOT', dirname(__FILE__, 2));

define('ROOT_PATH', dirname(__DIR__));
define('PLAYLISTS_PATH', ROOT_PATH . '/playlists');
define('PLAYLISTS_URI', rtrim(SITE_URI, '/') . str_replace(SITE_ROOT, '', ROOT_PATH) . '/playlists');


spl_autoload_register(function($class){
	include $class . '.php';
});
