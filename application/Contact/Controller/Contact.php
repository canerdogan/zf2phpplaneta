<?php

namespace Contact\Controller;

use Zf2Mvc\Controller\ActionController;

/**
 * Description of Contact
 *
 * @author robert
 */
class Contact extends ActionController
{
    public function indexAction()
    {
        return array('foo' => 'bar');
    }
}