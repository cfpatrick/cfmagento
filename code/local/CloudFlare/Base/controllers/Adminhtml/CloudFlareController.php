<?php

class CloudFlare_Base_Adminhtml_CloudFlareController extends Mage_Adminhtml_Controller_Action
{
    public function cacheAction() {
        $api = Mage::getSingleton('cloudFlare_base/api');
        $result = true;
        /**
         * $result = $api->purgeCache();
         */
        
        if ($result) {
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('cloudFlare_base')->__('CloudFlare cache has been purged.'));
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('cloudFlare_base')->__('CloudFlare cache has been failed to purge.'));
        }
        $this->_redirect('adminhtml/cache');
    }
}
