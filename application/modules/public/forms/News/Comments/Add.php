<?php

namespace Planet\Form\News\Comments;

/**
 * Description of Add
 *
 * @author robert
 */
class Add extends \Planet\Form\News\Comments
{
    
    public function init()
    {
        parent::init();

        $this->removeElement('active');
        $this->removeElement('id');
        $this->removeElement('datetime_added');
    }
}