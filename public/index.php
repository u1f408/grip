<?php declare(strict_types=1);

// If using the PHP development server, allow falling through to serving
// any static files that may exist within the webroot
if (php_sapi_name() === 'cli-server') {
	$fn = dirname(__FILE__) . parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
	if (file_exists($fn) && !is_dir($fn)) {
		return false;
	}
}

// Get the base application path, and set up autoload
define("BASE_PATH", dirname(dirname(($p = realpath(__FILE__)) === false ? __FILE__ : $p)));
require_once(BASE_PATH . "/vendor/autoload.php");

// Load configuration
\Grip\Configuration::load();

// Create application, and run it
($app = \Grip\AppFactory::create())->run();
