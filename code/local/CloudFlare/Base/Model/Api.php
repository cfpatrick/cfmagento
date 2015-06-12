<?php

class CloudFlare_Base_Model_Api 
{
    const BASE_API_URL = 'https://api.cloudflare.com/client/v4/zones';

    protected $_curlResult = NULL; // last curl request from any api call
	protected $_response = NULL;   //where we store the most current response for the old host API
    protected $_errorMessage = NULL; // storing errors for newer calls, such as plan set and WAF

    protected $_paginationInfo = NULL;

    protected function _getUrl($path, $includeZoneTag = true) {
        $domain  = Mage::getStoreConfig('cloudFlare/general/host');
        $zoneTag = '';
        if ($includeZoneTag) {
            $zoneTag = '/'. $this->domain2tag($domain);
            if (!$zoneTag) {
                Mage::throwException(Mage::helper('cloudFlare_base')->__('Error retrieving domain.'));
            }
        }
        $url = sprintf("%s%s%s", self::BASE_API_URL, $zoneTag, $path);
        return $url;
    }

    public function getErrorMessage() {
        return $this->_errorMessage;
    }
    
    protected function setErrorMessage($message) {
        return $this->_errorMessage = $message;
    }

    public function getPaginationInfo() {
        return $this->pagination_info;
    }

    protected function setPaginationInfo(stdClass $paginationInfo) {
        return $this->_paginationInfo = $paginationInfo;
    }
    
    // helper function to run curl in a single pass
    // used if the base function doesn't need to tweak curl settings
    private function runCurl($url, $params = null)
    {
        $curl = $this->getCurl($url, $params);
        return $this->executeCurl($curl);
    }
    
    // Splitting the curl function to allow functions to tweak the curl as needed
    private function getCurl($url, $params = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_URL, $url);
        
        if (!is_null($params)) {
            $query = http_build_query($params);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
        }

        $content_type = "Content-Type: application/json";
        $headers = array($content_type);

        $userKey   = Mage::getStoreConfig('cloudFlare/general/api_key');
        $userEmail = Mage::getStoreConfig('cloudFlare/general/email');
        
        $auth_key_header = "X-Auth-Key:" . $userKey;
        $email_header = "X-Auth-Email:" . $userEmail;
        $headers = array_merge($headers, array($auth_key_header, $email_header));

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        return $ch;
    }

    private function executeCurl($ch)
    {
        $this->_curlResult = curl_exec($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $response = json_decode($this->_curlResult);

        // store pagination data on this object
        if (isset($response->result_info)) {
            $this->setPaginationInfo($response->result_info);
        }

        return $response;
    }

    // Function to load domain tag for future requests
    private function domain2tag($domain) {
        $filters = array("name" => $domain);
        $url = $this->_getUrl("?" . http_build_query($filters), false);
        $curl = $this->getCurl($url);

        $response = $this->executeCurl($curl);
        echo '<pre>';
        var_dump($response);

        if (!is_array($response->result)) {
            return false;
        }

        foreach ($response->result as $zone) {
            if ($zone->name == $domain) {
                return $zone->id;
            }
        }

        return false;
    }

    // pending API release for this functionality
    public function getPageRules() {}
    public function setPageRule() {}

    public function purgeCache($files = null) {
        if (!$files) {
            $params = array("purge_everything" => true);
        } elseif (is_array($files)) {
            $params = array("files" => $files);
        } else {
            $params = array("files" => array($files));
        }

        $url = $this->_getUrl('/purge_cache');
        $curl = $this->getCurl($url);

        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($params));

        return $this->executeCurl($curl);
    }

    public function getDevMode() {
        $url = $this->_getUrl("/settings/development_mode");
        $curl = $this->getCurl($url);
        return $this->executeCurl($curl);
    }

    /**
    * 
    */
    public function setDevMode($value) {
        if ($value) {
            $params = array("value" => "on");
        } else {
            $params = array("value" => "false");
        }

        $url = $this->_getUrl("/settings/development_mode");
        $curl = $this->getCurl($url);

        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PATCH");
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($params));

        return $this->executeCurl($curl);
    }

}