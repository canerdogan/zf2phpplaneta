<?php

namespace Planet\View;

use Zend\Loader\PluginClassLoader;

class HelperLoader extends PluginClassLoader
{
    protected $plugins = array(
        'numberofcomments' => 'Planet\View\Helper\NumberOfComments'
    );
}