<?php

/*
 * add Zend to include path
 */
// Define relative path to ZendFramework in public_html
define('ZF_PATH', __DIR__ . '/3rdparty/ZendFramework-minimal-2.2.5/library/Zend');

// Define path to application directory
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define real path to ZendFramework if it's not yet included in include_path
if (!strpos(get_include_path(), 'zendframework'))
    define('ZF_REAL_PATH', realpath(APPLICATION_PATH . ZF_PATH));
else define('ZF_REAL_PATH', '');

// Updating include_path
set_include_path(implode(PATH_SEPARATOR, array(ZF_REAL_PATH, get_include_path(),)));

/*
 * finished  setting include path, load autoloader
 */
$zfdir = __DIR__ . '/3rdparty/ZendFramework-minimal-2.2.5/library/Zend/';
require_once $zfdir . 'Loader/StandardAutoloader.php';

$zendLoader = new Zend\Loader\StandardAutoloader(array(
    'namespaces' => array(
        'Zend' => $zfdir,
        'Zend\Server' => $zfdir . 'Server',
        'Zend\Server\Reflection' => $zfdir . 'Server/Reflection',
        'Zend\Code' => $zfdir . 'Code',
        'Zend\Code\Reflection' => $zfdir . 'Code/Reflection',
        'Zend\Soap' => $zfdir . 'Soap',
        'Zend\Soap\AutoDiscover' => $zfdir . 'Soap/AutoDiscover',
        'Zend\Soap\AutoDiscover\DiscoveryStrategy' => $zfdir . 'Soap/AutoDiscover/DiscoveryStrategy',
    ),
    'fallback_autoloader' => true));

$zendLoader->register();


/**
 * Created by JetBrains PhpStorm.
 * User: MTORCHIO
 * Date: 7/2/14
 * Time: 9:05 AM
 * To change this template use File | Settings | File Templates.
 */
use Zend\Soap\AutoDiscover;
use \OCP\MC_Group;
use \OCP\Util;

class PublicKeyService
{
    /**
     * Allow authenticated users to request a listing of the public keys of other users with whom they have an IGA
     * @param string $username - user that is sending a notification
     * @param string $passphrase - password for sending user. Some special characters need to be escaped to ensure valid XML
     * @param string $filter - Narrow results based on Jurisdiction name. Leave blank for all possible results. Accepts either full name or first few characters of name, not case sensitive
     * @return string -  array of key-value (Jurisdiction, PublicKey)
     * @throws Exception
     */
    public function requestKeys($username,
                                $passphrase,
                                $filter)
    {
        // initialize OC stuff
        \OC::initPaths();
        \OC_User::setupBackends();

        $loggedIn = \OC_User::login($username, $passphrase);
        if (!$loggedIn) {
            throw new Exception("Invalid credentials supplied for public key web service");
        }

        $groupNames = MC_Group::getGroups($filter);
        $igas = \OCP\MC_User::getIGAPartners();

        $resultArray = array();
        foreach ($groupNames as $gid){
            $aGroup = MC_Group::getGroupInfo($gid);
            //if aGroup is one of the groups in igas
            if(in_array($aGroup["groupId"], $igas)){
                $resultArray[$gid] = $aGroup["publicKey"];
            }
        }
        return $resultArray;
    }


}

function constructWsdlUrl($includeWsdl)
{
    $portBit = '';
    $currentUrl = 'http';
    if ($_SERVER['HTTPS'] == 'on') {
        $currentUrl .= 's';
        if ($_SERVER['SERVER_PORT'] != '443') {
            $portBit = ':' . $_SERVER['SERVER_PORT'];
        }
    } else {
        if ($_SERVER['SERVER_PORT'] != '80') {
            $portBit = ':' . $_SERVER['SERVER_PORT'];
        }
    }
    $lastBit = strtok($_SERVER["REQUEST_URI"],'?'); // more flexible way of getting this file's current location
    if ($includeWsdl) {
        $lastBit .= '?wsdl';
    }
    $currentUrl = $currentUrl . '://' . $_SERVER['SERVER_NAME'] . $portBit . $lastBit;
    return $currentUrl;
}

if (isset($_GET['wsdl'])) {
    $autodiscover = new AutoDiscover();
    $autodiscover->setClass('PublicKeyService');
 // build the URL accordingly
    $wsdlUrl = constructWsdlUrl(false);
    $autodiscover->setUri($wsdlUrl);
    header('Content-Type: text/xml');
    print($autodiscover->toXml());
} else {
    /* Now include the Owncloud stuff.. */
    require_once 'lib/base.php';

    // host our SOAP service
    $wsdlUrl = constructWsdlUrl(false);
    $server = new SoapServer(null, array('uri' => $wsdlUrl));
    $server->setClass('PublicKeyService');
    try {
        $server->handle();
    } catch (Exception $e) {
        $server->fault('Sender', $e->getMessage());
    }
}
