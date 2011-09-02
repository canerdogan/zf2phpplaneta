<?php

use Zend\Controller\Action;
use Zend\Mail\Mail;
use Planet\Model;
use Planet\Form;
use Planet\Service;

class IndexController extends Action
{

    public function init()
    {
        $this->fm = $this->broker('flashmessenger');
        $this->loggedInUser = $this->broker('loggedinuser');
        
        $this->model = new Model\News();
    }

    public function indexAction()
    {
        $page = $this->_getParam('page', 1);

        $this->view->vars()->news = $this->model->getAllActiveNews($page);
    }

    public function aboutAction()
    {
    }

    public function contactAction()
    {
        $bootstrap = $this->getInvokeArg('bootstrap');
        $di = $bootstrap->getResource('di');
        $contactService = $di->get('servicecontact');
        var_dump($contactService);
        
        $contactForm = new Form\Contact();

        if($this->_request->isPost()) {
            if($contactForm->isValid($this->_request->getPost())) {
                try {
                    $contactService->setMailData($contactForm->getValues());
                    $contactService->sendMail();
                    
                    $this->fm->addMessage(array('fm-good' => 'E-mail uspešno poslat!'));

                    return $this->redirector->gotoRoute(
                           array('action' => 'contact', 'controller' => 'index'),
                           '', true
                           );
                    
                } catch (Exception $e) {
                    $this->fm->addMessage(array('fm-good' => 'Greška prilikom slanja E-maila! Molim Vas pokušajte ponovo!'));
                    try {
                        $logger = Zend_Registry::get('logger');
                        $logger->log($e->getMessage(),2);
                    } catch (Exception $e) {
                    }
                }
            }
        }

        $this->view->contactForm = $contactForm;
    }

}