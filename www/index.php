<?php

// Set some configuration values.
ini_set('display_errors', true);
ini_set('error_reporting', E_ALL);

// Define header content type.
header('Content-Type : text/html; charset=utf-8');

// Set error handler.
set_error_handler(function($errno, $errstr, $errfile, $errline){
    throw new \ErrorException($errstr, $errno, 0, $errfile, $errline);
});

// Define the global variable ROOT_PATH.
$projectName = $_SERVER['REQUEST_URI'];
$projectName = strpos($_SERVER['REQUEST_URI'], '/www') !== false ? substr( $_SERVER['SCRIPT_NAME'], 0, -13) : $projectName;

defined('ROOT_PATH') || define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT'].$projectName);

// Include the loader class.
require_once(ROOT_PATH.'Kernel/Core/Loader.class.php');

// Register the autoload function.
spl_autoload_register(array('Kernel\Core\Loader', 'autoload'));

// Bootstrap the application.
$application = new Kernel\Core\Application();
$application->init();

// Run the application.
$application->run();

?>