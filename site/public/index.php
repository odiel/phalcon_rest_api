<?
/*
 * This is the bootstrap file
 */

// Checking and setting every the application needs to run
include_once('init_web.php');

// Registering and defining the class auto-loader
$loader = new \Phalcon\Loader();
$loader->registerNamespaces(
    array(
        'Classes'  => APP_PATH.'Classes/',
        'Modules'  => APP_PATH.'Modules/',
        'Routes'  => APP_PATH.'Routes/',
        'Models'  => APP_PATH.'Models/',
    )
);
$loader->register();


//Turning Off Phalcon Null Validation message, the application is going to use his owen messages for this
\Phalcon\Mvc\Model::setup(array(
    'notNullValidations' => false,
));

//Creating the Application
$app = new \Classes\Application();

//Initiating application, services and events
$app->init();

//Setting not found controller
$app->setNotFoundController('Error\NotFound');

//Handling request
$app->handle();

//No exception catch is need, the \Configuration class takes care of it
