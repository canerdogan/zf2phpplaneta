<?php

namespace PPN\Plugin;

use Zend\Controller\Plugin\AbstractPlugin as Plugin,
    Zend\Controller\Request\AbstractRequest as Request;

class AdminContext extends Plugin
{
    protected $_auth = null;

    public function preDispatch(Request $request) 
    {
        if($request->getParam('isAdmin')
                or $request->getControllerName() === 'admin') {

            $auth = $this->_getAuth();

            if(!$auth->getIdentity()) {
                $request->setModuleName('public')
                        ->setControllerName('user')
                        ->setActionName('login');
                return;
            }

            $layout = Zend_Layout::getMvcInstance();
            $layout->setLayout('admin');
            $view = $layout->getView();
            $view->headTitle()->prepend('Admin panel');
        }
    }

    protected function _getAuth()
    {
        if($this->_auth === null) {
            $this->_auth = new Planet_Service_Auth();
        }

        return $this->_auth;
    }
}