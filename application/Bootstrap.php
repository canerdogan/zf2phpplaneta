<?php

use Zend\Config\Ini as Config,
    Zend\Di\Configuration as DiConfig,
    Zend\Di\Definition,
    Zend\Di\Definition\Builder,
    Zend\Di\DependencyInjector;

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
        $this->setupDi($application);
    }
    
    protected function setupDi($application)
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
}