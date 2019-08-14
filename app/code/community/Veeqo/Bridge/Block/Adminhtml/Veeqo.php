<?php
class Veeqo_Bridge_Block_Adminhtml_Veeqo extends Mage_Adminhtml_Block_Template
{           
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('veeqo/veeqo.phtml');

    }
}