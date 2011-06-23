<?php

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

defined('LIBRARY_PATH')
    || define('LIBRARY_PATH', realpath(dirname(__FILE__) . '/../library'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path
//set_include_path(implode(PATH_SEPARATOR, array(
//    realpath(APPLICATION_PATH . '/../library'),
//    get_include_path(),
//)));

require_once __DIR__ . '/../library/Zend/Loader/StandardAutoloader.php';

$loader = new \Zend\Loader\StandardAutoloader();
// no need to register ZendFramework, it's autoregistered
// $loader->registerNamespace('Zend', LIBRARY_PATH . '/Zend');
$loader->registerNamespace('App', LIBRARY_PATH . '/App');
$loader->register();

use Zend\Application\Application;

$app = new Application(APPLICATION_ENV,
                        APPLICATION_PATH . '/configs/application.ini');
$app->bootstrap()
        ->run();