<?php

class CloudFlare_Base_Helper_Data extends Mage_Core_Helper_Data {

    public function getPurgeCacheUrl() {
        return Mage::helper('adminhtml')->getUrl('adminhtml/cloudFlare/cache');
    }
}