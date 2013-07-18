<?

/*
 * This file intention is to:
 * - Define some general constants
 * - Make sure all the required extensions are available before start running the application
 * - Load configuration file
 * - Initialize configuration parameters
 */


//Defining global constants
define('ROOT_PATH', __DIR__.'/../');
define('APP_PATH', ROOT_PATH.'app/');
define('PUBLIC_PATH', __DIR__.'/');
define('LOGS_PATH', ROOT_PATH.'../../logs/');

//Checking extensions
$extensions = get_loaded_extensions();
if (!in_array('phalcon', $extensions)) {
    die('<h3>Error:</h3><h4>Phalcon extension is not loaded.</h4>');
}

if (!in_array('PDO', $extensions)) {
    die('<h3>Error:</h3><h4>PDO extension is not loaded.</h4>');
}

if (!in_array('pdo_mysql', $extensions)) {
    die('<h3>Error:</h3><h4>PDO_Mysql extension is not loaded.</h4>');
}

if (!in_array('apc', $extensions)) {
    die('<h3>Error:</h3><h4>APC extension is not loaded.</h4>');
}

//Checking config file
if (!isset($_SERVER['CONFIG_MAIN_FILE'])) {
    die('<h3>Error:</h3><h4>CONFIG_MAIN_FILE is not defined.</h4>');
}

//Checking if config file exists
if (!is_file($_SERVER['CONFIG_MAIN_FILE'])) {
    die('<h3>Error:</h3><h4>Config file does not exist in the defined location.</h4>');
}

//Including config file
$config = include_once($_SERVER['CONFIG_MAIN_FILE']);

//Verifying is included
if ($config === false) {
    die('<h3>Error:</h3><h4>Config file seems to be corrupted or contain PHP errors, please check the logs for more details.</h4>');
}

Configuration::initialize();

define('BASE_URL', Configuration::$application['baseUrl']);