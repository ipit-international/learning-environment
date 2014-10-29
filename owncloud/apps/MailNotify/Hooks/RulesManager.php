<?php
/**
 * Created by PhpStorm.
 * User: AJROSS
 * Date: 12/13/13
 * Time: 9:40 AM
 */

namespace OCA\MailNotify\Hooks;

use \OCP\Config;

class RulesManager {

    /**
     * Wrapper around the soap call to execute notification business rules based on sender country.
     * @param $senderCountry
     * @return mixed
     */
    
    

    public static function executeNotificationRules($senderCountry){
   
	$opts = array(
	'http' => array(
		'user_agent' => 'PHPSoapClient'
		)
  
   	 );

        $context = stream_context_create($opts);

        $wsdl = Config::getSystemValue('ruleswsdl');
        $soapClient = new \SoapClient($wsdl, array('stream_context' => $context,
					'cache_wsdl' => WSDL_CACHE_NONE));
	$result = $soapClient->isModelOneOptionTwo(
            array('arg0'=>$senderCountry)
        );
        return $result->return;
    }


} 
