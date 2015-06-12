<?php

class CloudFlare_Base_Model_Observer 
{
    public function frontInitBefore($data) {
        $devMode = Mage::getStoreConfig('cloudFlare/general/dev_mode');
        $api = Mage::getSingleton('cloudFlare_base/api');
        /**
         *   $api->setDevMode($devMode);
         */
    }

    public function productSaveCommitAfter($data) {
        $api = Mage::getSingleton('cloudFlare_base/api');
        /**
         *   $api->updateProductCache();
         */
    }

    public function quoteSubmitSuccess($data) {
        $quote = $data->getQuote();
        $outOfStockIds = array();
        foreach ($quote->getAllItems() as $item) {
            $product = Mage::getModel('catalog/product')
              ->load($item->getProductId());
            if (!$product->isAvailable()) {
                $outOfStockIds[] = $product->getId();
            }
        }
        $api = Mage::getSingleton('cloudFlare_base/api');
        /**
         *   $api->updateProductCache();
         */
    }

}