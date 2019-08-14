<?php

class Veeqo_Bridge_Model_Installer
{
    public function downloadAndCheck($download_url)
    {
        try {
            $download_result = $this->downloadFile($download_url);
            if ($download_result['status'] != 1) {
                Mage::getSingleton('adminhtml/session')->addError($download_result['message']);
                return false;
            }

            $check_result = $this->CheckBridge($download_url);
            if ($check_result['status'] == 200) {
                Mage::getConfig()->saveConfig(Mage::helper('veeqo')->getBridge2CartUrlXmlPath(), $download_url);
                Mage::app()->getCacheInstance()->cleanType('config');
                return true;
            } elseif ($check_result['status'] == 400) {
                Mage::getSingleton('adminhtml/session')->addError($this->__($check_result['error_messages']));
                return false;
            } else {
                $error_msg = $this->__("Please check Veeqo Validation Url. Response code: %d", $check_result['status']);
                Mage::getSingleton('adminhtml/session')->addError($error_msg);
                return false;
            }

            if (!isset($check_result['status'])) {
                Mage::getSingleton('adminhtml/session')->addError($this->__($check_result['error']));
                return false;
            }
        }
        catch (Mage_Core_Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('An error occured during the installation. See exception.log.'));
            Mage::throwException($e->getMessages());
            return false;
        }
        catch (exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e);
            Mage::logException($e);
            return false;
        }
        return false;
    }

    private function downloadFile($remote_url)
    {
        $mage_root = dirname(Mage::getRoot());
        $dest_dir = $mage_root . DIRECTORY_SEPARATOR . 'bridge2cart';
        $tmp_filename = 'bridge.zip';
        $result = array('status' => 1);

        // create the destination directory if doesn't exists
        if (!file_exists($dest_dir) || !is_dir($dest_dir)) {
            mkdir($dest_dir, 0755);
        }

        // Download the file from remote server to temporary file
        $destinationFilePath = $dest_dir . DIRECTORY_SEPARATOR . $tmp_filename;        
        $client = new Varien_Http_Client($remote_url, array('keepalive' => true));
        $client ->setUri($remote_url)->setMethod('GET');
        $response = $client->request();
        $status = $response->getStatus();
        if ($status == 200) {
            $body = $response->getRawBody();
            $content_type = $response->getHeader('content-type');
            if ($content_type=='application/zip'){
                $fp = fopen($destinationFilePath, 'w');
                file_put_contents($destinationFilePath, $response->getBody());
                fclose($fp);
                
                // return the error message if download failed
                if (!file_exists($destinationFilePath)) {
                    $result['status'] = 0;
                    $result['message'] = $this->__("Can't download the file from server.");
                    return $result;
                }
        
                // Unzip temporary file to destination directory
                try {
        
                    $filter = new Zend_Filter_Decompress(array('adapter' =>
                            'Zend_Filter_Compress_Zip', 'options' => array('target' => $mage_root, )));
                    $filter->filter($destinationFilePath);
                    @unlink($mage_root . DIRECTORY_SEPARATOR . 'readme.txt');
                }
                catch (Mage_Core_Exception $e) {
                    $result['status'] = 0;
                    $result['message'] = $this->__($e);
                    return $result;
                }
            }
            else
            {
                $xml_response = simplexml_load_string($body);
                $msg = 'Error code '.$xml_response->return_code.' - '.$xml_response->return_message;  
                $result['status'] = 0;
                $result['message'] = $this->__($msg);
                return $result;
            }
        }
        
        return $result;
    }

    private function CheckBridge($download_url)
    {
        $validation_url = Mage::helper('veeqo')->getValidationUrl();

        $result = array('status' => null);
        
        if (!$download_url) {
            $result['error_messages'] = $this->__('Bridge2CartUrl Download Url is empty');
            return $result;
        }

        $parts = parse_url($download_url);
        $query = array();
        parse_str($parts['query'], $query);

        if (!isset($query['store_key']) || $query['store_key'] == '') {
            $result['error_messages'] = $this->__('Bridge2CartUrl Download Url have not "store_key" param.');
            return $result;
        }

        if (!$validation_url) {
            $result['error_messages'] = $this->__('Veeqo Validation Url is empty');
            return $result;
        }

        $client = new Varien_Http_Client();
        $raw_params = array("store_key" => $query['store_key']);
        $raw_data = Mage::helper('core')->jsonEncode($raw_params);
        $client->setUri($validation_url)->setMethod('POST')->setConfig(array(
            'maxredirects' => 0,
            'timeout' => 30,
            ))->setRawData($raw_data,
            "application/json;charset=UTF-8");

        $response = $client->request();
        $body = $response->getRawBody();

        $result = Mage::helper('core')->jsonDecode($body);
        $result['status'] = $response->getStatus();

        return $result;
    }

    private function __($msg)
    {
        $args = func_get_args();
        $expr = new Mage_Core_Model_Translate_Expr(array_shift($args), 'veeqo');
        array_unshift($args, $expr);
        return Mage::app()->getTranslator()->translate($args);
    }
}
