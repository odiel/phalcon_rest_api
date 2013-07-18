<?

/**
 * Class Configuration
 *
 * Making Configuration an static class, give a the ability to access it from any place in the code
 */
class Configuration
{
    /**
     * Errors level and Display errors
     *
     * @var array
     */
    public static $errors = array(
        'level' => E_ALL,
        //'display' => false,
        'display' => true
    );


    /**
     * Database settings
     *
     * @var array
     */
    public static $dbs = array(
        'mysql' => array (
            'main' => array(
                'host' => 'localhost',
                'username' => 'root',
                'password' => '123',
                'dbname' => 'gtw',
                'port' => 3306
            )
        ),

        'models' => array(
            'useMetadata' => true
        )
    );

    /**
     * Application settings
     *
     * @var array
     */
    public static $application = array(
        'timezone' => 'UTC',
        'baseUrl' => '',
        'title' => 'gtw',

        'defaultLanguage' => 'en',

        'logger' => array(
            'queries' => true
        ),

        'api' => array(
            'security' => array(

                'time' => array(
                    'operations' => 120 //in seconds ~ 2 minutes
                ),

                'encryption' => array(
                    'method' => 'AES-256-CBC'
                )
            )
        )
    );

    /**
     * Session settings
     *
     * @var array
     */
    public static $sessions = array(
        'user' => array(
            'life_time' => 1200
        )
    );

    /**
     * General logs settings
     *
     * @var array
     */
    public static $logs = array(
        'path' => '/home/www/logs/',

        'db' => array(
            'queries' => 'queries.log'
        )
    );

    /**
     * Initialize the configuration settings
     */
    public static function initialize()
    {
        self::setTimezone();
        self::setErrorLevels();
        self::setErrorHandlers();
    }

    /**
     * Set time zone
     */
    public static function setTimezone()
    {
        date_default_timezone_set(self::$application['timezone']);
    }

    /**
     * Set the errors levels of the application
     */
    public static function setErrorLevels()
    {
        error_reporting(self::$errors['level']);
        ini_set('display_errors', self::$errors['display']);
    }

    /**
     * Set errors/exception handlers
     */
    public static function setErrorHandlers()
    {
        set_error_handler('Configuration::commonErrorHandler');
        register_shutdown_function('Configuration::fatalErrorHandler');
        set_exception_handler('Configuration::exceptionHandler');
    }

    /**
     * Fatal errors handler
     */
    public static function fatalErrorHandler()
    {
        $error = error_get_last();
        if(isset($error)) {
            self::commonErrorHandler($error['type'], $error['message'], $error['file'], $error['line'], array());
        }
    }

    /**
     * Common and minor errors handler
     */
    public static function commonErrorHandler($no, $message, $file, $line, $context)
    {
        self::replyError($no, $message, $file, $line, $context);
    }

    /**
     * Exceptions handler
     */
    public static function exceptionHandler($e)
    {
        self::replyException($e);
    }

    /**
     * Send back an exception response
     *
     * @param \Exception $e
     */
    public static function replyException($e)
    {
        $response = '';

        //The reply is prepared base on settings, we can set our configurations to show errors in our dev environment, and set it false in production
        if (Configuration::$errors['display'] === true) {
            $response = array('exception' => array('class' => get_class($e), 'code' => $e->getCode(), 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTrace()));
        } else {
            $response = array('message' => 'An internal error happened, the operation will be logged in our system, please contact support.');
        }

        self::sendReply($response);
    }

    /**
     * Send back an error response
     *
     * @param int $no
     * @param string $message
     * @param string $file
     * @param int $line
     * @param array $context
     */
    public static function replyError($no, $message, $file, $line, $context)
    {
        $response = '';

        //The reply is prepared base on settings, we can set our configurations to show errors in our dev environment, and set it false in production
        if (Configuration::$errors['display'] === true) {
            $response = array('error' => array('code' => $no, 'message' => $message, 'file' => $file, 'line' => $line, 'context' => $context));
        } else {
            $response = array('response' => array('message' => 'An internal error happened, the operation will be logged in our system, please contact support.'));
        }

        self::sendReply($response);
    }

    /**
     * Common function to send the response
     *
     * @param array $content
     */
    public static function sendReply($content = array())
    {
        ob_clean();

        $response = array(
            'status' => array(
                'code' => 500,
                'message' => 'Internal Server Error'
            ),
            'time' => date('U')
        );

        $response = array_merge($response, $content);

        header('Status: 500 Internal Server Error');
        header('Content-Type', 'application/json');
        echo json_encode($response);
        die;
    }

    /**
     * Main DB service
     *
     * @param \Phalcon\Events\Manager $eventsManager
     * @return \Phalcon\Db\Adapter\Pdo\Mysql
     */
    public static function getDBService($eventsManager)
    {
        // Setting adapter base on the application DB settings
        $connection = new \Phalcon\Db\Adapter\Pdo\Mysql(self::$dbs['mysql']['main']);

        //If is desired the application can log every performed query, this setting must be Off in production
        if (true === self::$application['logger']['queries']) {
            //Creating a file logger
            $logger = new \Phalcon\Logger\Adapter\File(self::$logs['path'].self::$logs['db']['queries']);

            $eventsManager->attach('db', function($event, $connection) use ($logger) {
                if ($event->getType() == 'afterQuery') {
                    $logger->log($connection->getSQLStatement(), \Phalcon\Logger::INFO);
                }
            });

            $connection->setEventsManager($eventsManager);
        }

        return $connection;
    }

    /**
     * Models metadata service
     *
     * @return \Phalcon\Mvc\Model\MetaData\Apc
     */
    public static function getModelsMetadataService()
    {
        // Defining the APC adapter to improve the PHP and Phalcon models speed
        $metaData = new \Phalcon\Mvc\Model\MetaData\Apc(array(
            'lifetime' => 86400,
            'suffix' => self::$application['title'].'_'
        ));

        return $metaData;
    }

    /**
     * Dispatcher service
     *
     * @param \Phalcon\Events\Manager $eventsManager
     * @return \Phalcon\Mvc\Dispatcher
     */
    public static function getDispatcherService($eventsManager)
    {
        $dispatcher = new \Phalcon\Mvc\Dispatcher();
        $dispatcher->setEventsManager($eventsManager);
        $dispatcher->setControllerSuffix('');
        $dispatcher->setActionSuffix('');
        return $dispatcher;
    }

    /**
     * Session service
     *
     * @return \Phalcon\Session\Adapter\Files
     */
    public static function getSessionService()
    {
        $session = new \Phalcon\Session\Adapter\Files();
        return $session;
    }

    /**
     * Url service
     *
     * @return \Phalcon\Mvc\Url
     */
    public static function getUrlService()
    {
        $url = new \Phalcon\Mvc\Url();
        $url->setBaseUri(BASE_URL);
        return $url;
    }

    /**
     * Router service
     *
     * @return \Phalcon\Mvc\Router
     */
    public static function getRouterService()
    {
        $router = new \Phalcon\Mvc\Router(false);
        $router->removeExtraSlashes(true);

        //Defining routes for Vendor
        $router->addGet(    '/manage/vendor/{id}/:params',      array('namespace' => 'Modules',       'controller' => 'Manage\Vendor'                               ))->setName('get-vendor');
        $router->addPut(    '/manage/vendor/{id}/:params',      array('namespace' => 'Modules',       'controller' => 'Manage\Vendor'                               ))->setName('edit-vendor');
        $router->addPost(   '/manage/vendor/:params',           array('namespace' => 'Modules',       'controller' => 'Manage\Vendor'                               ))->setName('create-vendor');
        $router->addDelete( '/manage/vendor/{id}/:params',      array('namespace' => 'Modules',       'controller' => 'Manage\Vendor'                               ))->setName('delete-vendor');
        $router->addGet(    '/manage/vendors/:params',          array('namespace' => 'Modules',       'controller' => 'Manage\Vendor',        'action' => 'vendors' ))->setName('get-vendors');

        return $router;
    }


    /**
     * ACL service
     *
     * @return \Phalcon\Acl\Adapter\Memory
     */
    public static function getACLService()
    {
        $acl = new \Phalcon\Acl\Adapter\Memory();
        $acl->setDefaultAction(\Phalcon\Acl::DENY);

        //Defining Everyone role
        $acl->addRole(new \Phalcon\Acl\Role('Everyone'));

        //Defining public resources
        $acl->addResource(new \Phalcon\Acl\Resource('Modules\Error\NotFound'), array('get', 'put', 'post', 'delete'));

        //Assigning public resources to Everyone
        $acl->allow('Everyone', 'Modules\Error\NotFound', '*');


        //Defining Vendor role, who inherits from Everyone
        $acl->addRole(new \Phalcon\Acl\Role('Vendor'), 'Everyone');


        //Defining Resources
        $acl->addResource(new \Phalcon\Acl\Resource('Modules\Manage\Vendor'), array('get', 'put', 'post', 'delete', 'vendors'));

        //Defining permissions
        $acl->allow('Vendor', 'Modules\Manage\Vendor', '*');

        return $acl;
    }


    /**
     * Generate a public Key
     *
     * @return string
     */
    public static function generatePublicKey()
    {
        $nanoseconds = exec('date +%s%N');
        return hash_hmac('md5', $nanoseconds.rand(1, 10000000), 'public');
    }

    /**
     * Generate a private Key
     *
     * @return string
     */
    public static function generatePrivateKey()
    {
        $nanoseconds = exec('date +%s%N');
        return hash_hmac('sha1', $nanoseconds.rand(1, 10000000), 'private');
    }

}