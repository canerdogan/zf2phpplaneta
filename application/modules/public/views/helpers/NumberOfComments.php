<?php

namespace Planet\View\Helper;

use Planet\Model\News;

/**
 * File: BaseUrl
 * Ver: 1.0
 * Created: Dec 28, 2009
 * Created by: Robert Basic
 * E-mail: robert.basic@online.rs
 * Description:
 *
 */
class NumberOfComments extends \Zend\View\Helper\AbstractHelper
{
    protected $_model = null;

    public function numberOfComments($newsId)
    {
        $return = '';
        $comments = $this->_getModel()->getCommentsForNews($newsId);

        $numberOfComments = count($comments);

        if($numberOfComments == 1) {
            $return = '1 komentar';
        } elseif($numberOfComments > 1) {
            $return = $numberOfComments . ' komentara';
        } else {
            $return = 'Nema komentara';
        }

        return $return;
    }

    protected function _getModel()
    {
        if($this->_model === null) {
            $this->_model = new News();
        }

        return $this->_model;
    }

}