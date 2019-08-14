<?php

class Veeqo_Bridge_Adminhtml_InstallController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        if (!$this->_validateFormKey() || !$this->getRequest()->isPost()) {
            $this->_forward('noRoute');
            return;
        }

        $download_url = $this->getRequest()->getPost('download_url', null);
        if (!$download_url) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please enter Bridge2Cart Download Url'));
        } else {
            $installer_model = Mage::getModel('veeqo/installer');
            $result = $installer_model->downloadAndCheck($download_url);

            if ($result) {
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Bridge2Cart Installed Succesfully.'));
            }
        }

        $redirectUrl = Mage::helper("adminhtml")->getUrl("veeqo/adminhtml_index/index");
        $this->_redirectUrl($redirectUrl);
    }
}
