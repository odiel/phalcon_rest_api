<?

namespace Classes;

/**
 * Class Application
 *
 * The base Application class
 *
 * @package Classes
 */
class Application extends \Phalcon\Mvc\Application
{

    /**
     * Method to initiate Application services
     *
     * @return void
     */
    public function init()
    {
        $di = new \Phalcon\DI\FactoryDefault();

        $eventsManager = new \Phalcon\Events\Manager();

        //We need to customize the dispatcher to be able to call the desired controller when a URI is not found
        $eventsManager->attach('dispatch', function($event, $dispatcher, $exception) use ($di) {
            if ('beforeException' == $event->getType()) {
                switch ($exception->getCode()) {
                    case \Phalcon\Dispatcher::EXCEPTION_HANDLER_NOT_FOUND:
                    case \Phalcon\Dispatcher::EXCEPTION_ACTION_NOT_FOUND:
                        $request = $di->get('request');
                        $dispatcher->forward(array(
                            'namespace' => 'Modules',
                            'controller' => $this->_notFoundController,
                            'action' => strtolower($request->getMethod())
                        ));
                        return false;
                }
            }
        });

        //Initiating application services
        $di->set('request', new \Classes\Phalcon\Request(), true);

        $di->set('url', \Configuration::getUrlService(), true);

        $di->set('session', \Configuration::getSessionService(), true);

        //Using metaData cache if the application need its, must be active for production
        if (true == \Configuration::$dbs['models']['useMetadata']) {
            $di->set('modelsMetadata', \Configuration::getModelsMetadataService(), true);
        }

        $di->set('db', \Configuration::getDBService($eventsManager), true);

        $di->set('router', \Configuration::getRouterService(), true);

        $di->set('dispatcher', \Configuration::getDispatcherService($eventsManager), true);

        $di->set('acl', \Configuration::getACLService(), true);

        $this->setDI($di);
    }


    /**
     * Handle the request
     *
     * @param null $uri
     * @return void
     */
    public function handle($uri = null)
    {
        //Getting Router service
        $router = $this->di->get('router');

        if (null != $uri) {
          $router->handle($uri);
        } else {
          $router->handle();
        }

        //Getting the associated resource to process for the matched URI
        $namespace = $this->router->getNamespaceName();
        $controller = $this->router->getControllerName();
        $action = $this->router->getActionName();
        $params = $this->router->getParams();

        //If there is not defined action, we use the HTTP Method for the action
        if (null == $action) {
            $request = $this->di->get('request');
            $action = strtolower($request->getMethod());
        }

        //Setting Dispatcher service with the resource information to be dispatch
        $dispatcher = $this->di->getShared('dispatcher');
        $dispatcher->setNamespaceName($namespace);
        $dispatcher->setControllerName($controller);
        $dispatcher->setActionName($action);
        $dispatcher->setParams($params);
        $dispatcher->dispatch();

        //Getting Response service
        $response = $this->di->get('response');

        //If response is not sent, then we send the current response.
        if (false === $response->isSent()) {
            ob_clean();
            $response->sendHeaders();

            //Getting response content to be rendered
            $toRender = $response->getContent();
            if (is_string($toRender)) {
                echo $toRender;
            }
        }
    }

    /**
     * Se the not found controller
     *
     * @param $controller
     */
    public function setNotFoundController($controller)
    {
        $this->_notFoundController = $controller;
    }

    private $_notFoundController;
}