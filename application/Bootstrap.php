<?php

use Zend\Application,
    Zend\Config\Ini,
    Zend\Log,
    Zend\Registry,
    Zend\Translator\Translator,
    Zend\Validator,
    Zend\Controller\Router\Route;

/**
 *   File: Bootstrap.php
 *
 *   Description:
 *       Application bootstraper, called by Zend_Application in index.php
 *       Basically, initializing resources on application level
*/
class Bootstrap extends Application\Bootstrap {

    /**
     * 
     * @var Zend_Config_Ini
     */
    private $__config = null;

    /**
     * Autoloader for the "public" module
     * 
     * @return Zend_Application_Module_Autoloader
     */
    public function _initPublicAutoload()
    {
        $moduleLoader = new Application\Module\Autoloader(
                                array(
                                    'namespace' => 'Planet',
                                    'basePath' => APPLICATION_PATH . '/modules/public'
                                )
                            );

        // adding model resources to the autoloader
        $moduleLoader->addResourceTypes(
                array(
                'modelResources' => array(
                        'path' => 'models/resources',
                        'namespace' => 'Model_Resource'
                    )
                )
            );

        return $moduleLoader;
    }

    /**
     * Initializing action helpers
     */
    public function _initActionHelpers()
    {
        Zend\Loader\PluginClassLoader::addStaticMap(array('loggedinuser' => 'Planet\Controller\Helper\LoggedInUser'));
    }
    
//    public function _initFcPlugins()
//    {
//        $this->bootstrap('FrontController');
//        $fc = $this->getResource('FrontController');
//        
//        $plugin = new PPN\Plugin\AdminContext();
//        $fc->registerPlugin($plugin);
//    }

    public function _initLogger()
    {
        $config = $this->__getConfig();

        $writer = null;

        if($config->settings->logs->enabled) {
            if(is_writable($config->settings->logs->filepath)) {
                $writer = new Log\Writer\Stream($config->settings->logs->filepath);

                $formatter = new Log\Formatter\Xml();
                $writer->setFormatter($formatter);
            }
        }

        if($writer === null) {
//            trigger_error("Logs are disabled. Is the log path writeable?", E_USER_NOTICE);
            $writer = new Log\Writer\Null();
        }
        
        $logger = new Log\Logger();
        $logger->addWriter($writer);

        Registry::set('logger', $logger);
    }

    public function _initTranslator()
    {
//        $currentLocale = 'sr';
//
//        $translatorOptions = array(
//            'adapter' => 'ArrayAdapter'
//        );
//        
////        $translator = new Translator('ArrayAdapter', realpath(APPLICATION_PATH . '/../resources/languages/sr/Zend_Validate.php'), 'sr');
//        $translator = new Translator($translatorOptions);
//        $translator->setLocale($currentLocale);
//
//        Validator\AbstractValidator::setDefaultTranslator($translator);
//
//        Registry::set('Zend_Translate', $translator);
//
//        return $translator;
    }

    /**
     * Initializing the View, setting the doctype, charset, et al.
     */
    public function _initViewHelpers()
    {
        $this->bootstrap('layout');
        $this->_layout = $this->getResource('layout');
        $this->_view = $this->_layout->getView();

        $this->_view->broker('doctype')->setDoctype('XHTML1_STRICT');
        $this->_view->broker('headMeta')->appendHttpEquiv('Content-type', 'text/html;charset=utf-8');
        $this->_view->broker('headTitle')->direct('PHPplaneta.net')
                                        ->setSeparator(' / ');

        $this->_view->broker('headLink')->direct(array(
            'rel' => 'alternate',
            'type' => 'application/rss+xml',
            'title' => 'PHPPlaneta glavni feed',
            'href' => 'http://feeds.feedburner.com/PHPPlaneta'
        ));

//        $this->_view->addHelperPath('PPN/View/Helper','PPN_View_Helper');
//        $this->_view->addHelperPath('ZendX/JQuery/View/Helper','ZendX_JQuery_View_Helper');

//        $this->_view->jQuery()
//                        ->addStylesheet('/static/css/smoothness/jquery-ui-1.8.1.custom.css')
//                        ->setVersion('1.4.2')
//                        ->enable()
//                        ->setUiVersion('1.8.1')
//                        ->setLocalPath('/static/js/jquery-1.4.2.min.js')
//                        ->setUiLocalPath('/static/js/jquery-ui-1.8.1.custom.min.js')
//                        ->uiEnable();
    }

    public function _initPublicRoutes()
    {
        $this->bootstrap('FrontController');
        $fc = $this->getResource('FrontController');

        $router = $fc->getRouter();

        $contactRoute = new Route\StaticRoute(
                    'kontakt',
                    array(
                        'action' => 'contact',
                        'controller' => 'index',
                        'module' => 'public'
                    )
                );

        $aboutRoute = new Route\StaticRoute(
                    'o-php-planeti',
                    array(
                        'action' => 'about',
                        'controller' => 'index',
                        'module' => 'public'
                    )
                );

        $rssRoute = new Route\StaticRoute(
                    'rss',
                    array(
                        'action' => 'rss',
                        'controller' => 'news',
                        'module' => 'public'
                    )
                );

        $categoryRoute = new Route\Regex(
                    'kategorija/([\w-]+)/strana/(\d+)',
                    array(
                        'action' => 'browse',
                        'controller' => 'news',
                        'module' => 'public',
                        'page' => 1
                    ),
                    array(
                        '1' => 'category',
                        '2' => 'page'
                    ),
                    'kategorija/%s/strana/%d'
                );

        $tagRoute = new Route\Regex(
                    'oznaka/([\w-]+)/strana/(\d+)',
                    array(
                        'action' => 'browse',
                        'controller' => 'news',
                        'module' => 'public',
                        'page' => 1
                    ),
                    array(
                        '1' => 'tag',
                        '2' => 'page'
                    ),
                    'oznaka/%s/strana/%d'
                );

        $dateRoute = new Route\Regex(
                    'datum/([\d-]+)/strana/(\d+)',
                    array(
                        'action' => 'browse',
                        'controller' => 'news',
                        'module' => 'public',
                        'page' => 1
                    ),
                    array(
                        '1' => 'date',
                        '2' => 'page'
                    ),
                    'datum/%s/strana/%d'
                );

        $searchRoute = new Route\Regex(
                    'pretraga/strana/(\d+)',
                    array(
                        'action' => 'search',
                        'controller' => 'news',
                        'module' => 'public',
                        'page' => 1
                    ),
                    array(
                        '1' => 'page'
                    ),
                    'pretraga/strana/%d'
                );

        $newsRoute = new Route\Regex(
                    '([\w-\d]+)',
                    array(
                        'action' => 'view',
                        'controller' => 'news',
                        'module' => 'public'
                    ),
                    array(
                        '1' => 'slug'
                    ),
                    '%s'
                );

        $homePaginationRoute = new Route\Regex(
                    'strana/(\d+)',
                    array(
                        'action' => 'index',
                        'controller' => 'index',
                        'module' => 'public',
                        'page' => 1
                    ),
                    array(
                        '1' => 'page'
                    ),
                    'strana/%d'
                );

        $router->addRoute('news', $newsRoute);
        $router->addRoute('contact', $contactRoute);
        $router->addRoute('about', $aboutRoute);
        $router->addRoute('rss', $rssRoute);
        $router->addRoute('category', $categoryRoute);
        $router->addRoute('tag', $tagRoute);
        $router->addRoute('date', $dateRoute);
        $router->addRoute('search', $searchRoute);
        $router->addRoute('homePagination', $homePaginationRoute);
    }

    public function _initAdminRoute()
    {
        $this->bootstrap('FrontController');
        $fc = $this->getResource('FrontController');

        $router = $fc->getRouter();

        $adminRoute = new Route\Route(
                    'admin/:module/:controller/:action/*',
                    array(
                        'action' => 'index',
                        'controller' => 'admin',
                        'module' => 'public',
                        'isAdmin' => true
                    )
                );

        $router->addRoute('admin', $adminRoute);

        $loginRoute = new Route\StaticRoute(
                    'login',
                    array(
                        'action' => 'login',
                        'controller' => 'user',
                        'module' => 'public'
                    )
                );
        $router->addRoute('login', $loginRoute);
    }

    public function _initFullPageCache()
    {
        $config = $this->__getConfig();

        if(!$config->settings->cache->fullpage->enabled) {
            return false;
        }

        if(!is_writable($config->settings->cache->fullpage->path)) {
            trigger_error("Full page cache path not writeable!", E_USER_NOTICE);
            return false;
        }

        $this->bootstrap('FrontController');
        $fc = $this->getResource('FrontController');
        $fc->setParam('disableOutputBuffering', true);

        if(APPLICATION_ENV == 'development') {
            $debugHeader = true;
        } else {
            $debugHeader = false;
        }

        $frontendOptions = array(
            'lifetime' => 1800,
            'debug_header' => $debugHeader,
            'default_options' => array(
                'cache' => false
            ),
            'regexps' => array(
                '^/$' => array(
                    'cache' => true,
                    'cache_with_get_variables' => true,
                    'cache_with_cookie_variables' => true
                ),
                '^/index/index/' => array(
                    'cache' => true,
                    'cache_with_get_variables' => true,
                    'cache_with_cookie_variables' => true
                ),
                '^/index/about' => array(
                    'cache' => true,
                    'cache_with_get_variables' => true,
                    'cache_with_cookie_variables' => true
                ),
                '^/news/view/' => array(
                    'cache' => true,
                    'cache_with_get_variables' => true,
                    'cache_with_cookie_variables' => true
                ),
                '^/news/browse/' => array(
                    'cache' => true,
                    'cache_with_get_variables' => true,
                    'cache_with_cookie_variables' => true
                ),
                // These two rules are for caching based on routes
                // silly me forgot to test caching after implementing the routes
                '^/(kategorija|oznaka|strana|datum)/' => array(
                    'cache' => true,
                    'cache_with_get_variables' => true,
                    'cache_with_cookie_variables' => true
                ),
                '^/([a-z0-9-_]+)/?$' => array(
                    'cache' => true,
                    'cache_with_get_variables' => true,
                    'cache_with_cookie_variables' => true
                ),
                // just to be on the safe side, don't cache anything
                // from the admin panel
                '^/admin' => array(
                    'cache' => false,
                    'cache_with_get_variables' => true,
                    'cache_with_cookie_variables' => true
                ),
                '^/news/ajax-load-dates/' => array(
                    'cache' => true,
                    'cache_with_get_variables' => true,
                    'cache_with_cookie_variables' => true
                )
            )
        );

        $backendOptions = array(
            'cache_dir' => $config->settings->cache->fullpage->path
        );

        $cache = Zend_Cache::factory(
                    'Page',
                    'File',
                    $frontendOptions,
                    $backendOptions
                );

        Zend_Registry::set('pageCache', $cache);

        $cache->start();
    }

    public function _initDbCache()
    {
        $config = $this->__getConfig();

        if(!$config->settings->cache->db->enabled) {
            return false;
        }
        
        if(!is_writable($config->settings->cache->db->path)) {
            trigger_error("Database cache path not writeable!", E_USER_NOTICE);
            return false;
        }

        $frontendOptions = array(
            'automatic_serialization' => true
        );

        $backendOptions = array(
            'cache_dir' => $config->settings->cache->db->path
        );

        $cache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);

        Zend_Db_Table_Abstract::setDefaultMetadataCache($cache);
    }

    /**
     *
     * @return Zend_Config_Ini
     */
    private function __getConfig()
    {
        if($this->__config === null) {
            $this->__config = new Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
        }

        return $this->__config;
    }

}
