<?php

use Zend\Config\Ini as Config,
    Zend\Di\Configuration as DiConfig,
    Zend\Di\Definition,
    Zend\Di\Definition\Builder,
    Zend\Di\DependencyInjector,
    Zf2Mvc\Router\Http\Literal as LiteralRoute,
    Zend\EventManager\StaticEventManager,
    Zend\View\Variables as ViewVariables,
    Zend\Stdlib\ResponseDescription as Response,
    Zf2Mvc\Application;

class Bootstrap {
    
    /**
     * Application config
     * 
     * @var Config
     */
    protected $config = null;
    
    public function __construct($config)
    {
        if(is_string($config)) {
            $config = new Config($config);
        }
        
        $this->config = $config;
    }
    
    public function bootstrap($application)
    {
        $this->initDi($application);
        $this->initRouting($application);
        $this->initEvents($application);
    }
    
    protected function initDi($application)
    {
        $diConfig = new Config(APPLICATION_PATH . '/configs/di.ini', APPLICATION_ENV);
        $diConfig = $diConfig->di;
        
        $definitionAggregator = new Definition\AggregateDefinition();
        $definitionAggregator->addDefinition(new Definition\RuntimeDefinition());
        
        $di = new DependencyInjector();
        $di->setDefinition($definitionAggregator);
        
        $config = new DiConfig($diConfig);
        $config->configure($di);
        
        // recommended by ralphschindler, will have to see what this does
        // $di->getDefinition()->getIntrospectionRuleset()->addSetterRule('paramCanBeOptional', false)
        
        $application->setLocator($di);
    }
    
    protected function initRouting($application)
    {
        $router = $application->getRouter();
        
        $route = new LiteralRoute(
                array(
                    'route' => '/contact',
                    'defaults' => array(
                        'controller' => 'controller-contact',
                        'action' => 'index'
                    )
                )
        );
        
        $router->addRoute('contact', $route);
    }
    
    protected function initEvents($app)
    {
        /**
        * Wire events into the Application's EventManager, and/or setup
        * static listeners for events that may be invoked.
        */
        $di = $app->getLocator();
        $view = $di->get('view');
        $view->resolver()->addPath(APPLICATION_PATH . '/Contact/views');
        // Needed until I can figure out why DI isn't working
//        $view->broker()->getClassLoader()->registerPlugin('url', 'site\View\Helper\Url');
//        $url = $view->broker('url');
//        $url->setRouter($app->getRouter());

        $layoutHandler = function($content, $response) use ($view) {
            // Layout
            $vars = new ViewVariables(array('content' => $content));
            $layout = $view->render('layouts/layout.phtml', $vars);

            $response->setContent($layout);
        };

        $events = StaticEventManager::getInstance();

        // View Rendering
        $events->attach('Zf2Mvc\Controller\ActionController', 'dispatch.post', function($e) use ($view, $layoutHandler) {
            $vars = $e->getParam('__RESULT__');
            if ($vars instanceof Response) {
                return;
            }
            
            if ($vars instanceof ArrayObject) {
                $vars = (array) $vars;
            }
            
            $response = $e->getParam('response');
            if ($response->getStatusCode() == 404) {
                // Render 404 responses differently
                return;
            }

            $request = $e->getParam('request');
            $routeMatch = $request->getMetadata('route-match');
            $controller = $routeMatch->getParam('controller', 'error');
            $controller = str_replace('controller-', '', $controller);
            $action = $routeMatch->getParam('action', 'index');
            $script = $controller . '/' . $action . '.phtml';
            $vars = new ViewVariables($vars);

            // Action content
            $content = $view->render($script, $vars);

            // Layout
            $layoutHandler($content, $response);
            return $response;
        });

        // Render 404 pages
        $events->attach('Zf2Mvc\Controller\ActionController', 'dispatch.post', function($e) use ($view, $layoutHandler) {
            $vars = $e->getParam('__RESULT__');
            if ($vars instanceof Response) {
                return;
            }

            $response = $e->getParam('response');
            if ($response->getStatusCode() != 404) {
                // Only handle 404's
                return;
            }

            $vars = array('message' => 'Page not found.');

            $content = $view->render('error/index.phtml', $vars);

            // Layout
            $layoutHandler($content, $response);
            return $response;
        });

        // Error handling
        $app->events()->attach('dispatch.error', function($e) use ($view) {
            $error = $e->getParam('error');
            $app = $e->getTarget();

            switch ($error) {
                case Application::ERROR_CONTROLLER_NOT_FOUND:
                    $vars = array(
                        'message' => 'Page not found.',
                    );
                    break;
                case Application::ERROR_CONTROLLER_INVALID:
                default:
                    $vars = array(
                        'message' => 'Unable to serve page; invalid controller.',
                    );
                    break;
            }

            $content = $view->render('error/index.phtml', $vars);

            // Layout
            $response = $app->getResponse();
            $layoutHandler($content, $response);
            return $response;
        });
    }
}