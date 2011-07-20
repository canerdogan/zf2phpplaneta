<?php

namespace Planet\Form\News\Comments;

/**
 * Description of Add
 *
 * @author robert
 */
class Edit extends \Planet\Form\News\Comments
{
    
    public function init()
    {
        parent::init();

        $this->removeElement('honeypot');
        $this->removeElement('js_fill');
    }
}