<?php 
require_once (dirname(__FILE__).'/nusoap/nusoap.php');

class EcordiaUserAccount {
    var $apiKey;
    /**
     * @var nusoap_client
     */
    var $client;
    var $results = null;
    var $requestHasBeenExecuted = false;
	var $useSsl = false;
    
    function EcordiaUserAccount($apiKey, $useSsl = false) {
        $this->apiKey = $apiKey;
		$this->useSsl = $useSsl;
		
		$loc = ($this->useSsl ? 'https' : 'http') . '://vesta.ecordia.com/optimizer/v1/usermanagement.svc/' . ($this->useSsl ? 'ssl' : 'nonssl') . '/';
        $this->client = new nusoap_client($loc);
        $this->client->soap_defencoding = 'utf-8';
		$this->client->use_curl = true;
    }
    
    function UserAccountStatus() {
        $contents = '<GetAccountStatus xmlns="https://vesta.ecordia.com"><submission xmlns:a="http://optimizer.ecordia.com/types/" xmlns:i="http://www.w3.org/2001/XMLSchema-instance"><a:ApiKey>'.$this->apiKey.'</a:ApiKey></submission></GetAccountStatus>';
        $contents = $this->client->serializeEnvelope($contents);
		
		$endpoint = 'https://vesta.ecordia.com/IUserManagement/GetAccountStatus';
        $results = $this->client->send($contents, $endpoint,0,180);
        $this->results = $results;
        $this->requestHasBeenExecuted = true;
    }
    
    function getRawResults() {
        if (!$this->requestHasBeenExecuted) {
            return array();
        } else {
            return $this->results;
        }
    }
    
    function hasError() {
        return $this->requestHasBeenExecuted && (!empty($this->results['faultcode']) || ($error = $this->client->getError()) || ! empty($this->results['GetAccountStatusResult']['Exception']['Message']));
    }
    
    function getError() {
        if (!$this->requestHasBeenExecuted) {
            return false;
        } elseif (!empty($this->results['faultcode'])) {
            return array('Message'=>$this->results['faultstring']['!'], 'Type'=>$this->results['faultcode']);
        } elseif ($this->client->getError()) {
            return array('Message'=>$this->client->getError(), 'Type'=>1);
        } else {
            return $this->results['GetAccountStatusResult']['Exception'];
        }
    }
    
    function getErrorMessage() {
        $error = $this->getError();
        if (is_array($error)) {
            return $error['Message'];
        } else {
            return false;
        }
    }
    
    function getErrorType() {
        $error = $this->getError();
        if (is_array($error)) {
            return $error['Type'];
        } else {
            return false;
        }
    }
    
    function getAccountStatus() {
        if (!$this->requestHasBeenExecuted) {
            return false;
        } else {
            return $this->results['GetAccountStatusResult']['AccountStatus']['AccountStatus'];
        }
    }
    
    function getAccountType() {
        if (!$this->requestHasBeenExecuted) {
            return false;
        } else {
            return $this->results['GetAccountStatusResult']['AccountStatus']['AccountType'];
        }
    }
    
    function getApiKey() {
        return $this->apiKey;
    }
    
    function getCreditsRemaining() {
        if (!$this->requestHasBeenExecuted) {
            return false;
        } else {
            return $this->results['GetAccountStatusResult']['AccountStatus']['CreditsRemaining'];
        }
    }
    
    function getCreditsTotal() {
        if (!$this->requestHasBeenExecuted) {
            return false;
        } else {
            return $this->results['GetAccountStatusResult']['AccountStatus']['CreditsTotal'];
        }
    }
    
    function getLastBilledAmount() {
        if (!$this->requestHasBeenExecuted) {
            return false;
        } else {
            return $this->results['GetAccountStatusResult']['AccountStatus']['LastBilledAmount'];
        }
    }
    
    function getLastBilledDate($format = 'n/j/Y') {
        if (!$this->requestHasBeenExecuted) {
            return false;
        } else {
            return date($format, strtotime(str_replace('T', ' ', $this->results['GetAccountStatusResult']['AccountStatus']['LastBilledDate'])));
        }
    }
    
    function isInvalidApiKey() {
        if (!$this->requestHasBeenExecuted) {
            return false;
        } else {
            return $this->results['GetAccountStatusResult']['Exception']['Type'] == 'InvalidApiKey';
        }
    }
}
