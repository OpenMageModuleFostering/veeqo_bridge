<?php

class Veeqo_Bridge_Adminhtml_IndexController extends Mage_Adminhtml_Controller_Action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('veeqo');
		return $this;
	}   
 
	public function indexAction() {
	    if (!extension_loaded('zip'))
        {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please Enable PHP ZIP extension'));
        }
		$this->_initAction()
			->renderLayout();
	}

}