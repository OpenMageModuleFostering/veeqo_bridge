<?php

class Veeqo_Bridge_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_BRIDGE2CART_URL = 'veeqo/general/bridge2cart_url';
    const XML_VALIDATION_URL = 'veeqo/general/validation_url';

    public function getBridge2CartUrlXmlPath()
    {
        return self::XML_PATH_BRIDGE2CART_URL;
    }

    public function getBridge2CartUrl()
    {
        return Mage::getStoreConfig(self::XML_PATH_BRIDGE2CART_URL);
    }

    public function getValidationUrl()
    {
        return Mage::getStoreConfig(self::XML_VALIDATION_URL);
    }
}
