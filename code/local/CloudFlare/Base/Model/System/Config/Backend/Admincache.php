<?php
class CloudFlare_Base_Model_System_Config_Backend_Admincache extends Mage_Core_Model_Config_Data
{
    protected function _beforeSave()
    {
        $oldValue = (int)Mage::getStoreConfig('cloudFlare/general/backend_cached');
        if (!$oldValue && $this->getValue()) {
            $api = Mage::getSingleton('cloudFlare_base/api');
            /**
             *  $api->createDisableAdminRule();
             */
        }

    }
}
