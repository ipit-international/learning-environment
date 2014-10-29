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
 * Date: 7/9/14
 * Time: 9:50 AM
 * To change this template use File | Settings | File Templates.
 */
use OC\Files\Filesystem;
use Zend\Soap\AutoDiscover;
use \OCP\MC_Group;
use \OCP\Util;
use OCP\App;

/* Now include the Owncloud stuff.. */
require_once 'lib/base.php';


class FileTransferService
{
    /**
     * Start Transmission: Upload a ZIP file containing a payload and metadata in one SOAP request
     * @param string $username - user that is sending the transmission
     * @param string $passphrase - password for sending user. Some special characters need to be escaped to ensure valid XML
     * @param string $fileName - Name of file being uploaded
     * @param string $fileContents - The file itself (base64 encoded)
     * @return string -  notification of successful file write or error message
     * @throws Exception
     */
    public function uploadTransmission($username,
                               $passphrase,
                               $fileName,
                               $fileContents)
    {
        $error = "initialized error string";
	//base64_decode() will return false if any characters aren't in base64 alphabet
        $fileContents = base64_decode($fileContents, true);
        if ($fileContents == false) {
	    $error = "File contents should be Base64 encoded. Found characters outside the Base64 alphabet in SOAP request.";
	    \OCP\Util::emitHook(
                        'UploadFile',
                        'upload_failure',
                        array(Filesystem::signal_param_path => Filesystem::normalizePath(stripslashes($fileName)), 'reason' => $error));
            throw new Exception($error);
        }

        // initialize OC stuff
        \OC_User::setupBackends();
        if (!isset($username) || !isset($passphrase)) {
	    $error = "Incomplete credentials supplied for Transmission Upload web service.";
	    \OCP\Util::emitHook(
                        'UploadFile',
                        'upload_failure',
                        array(Filesystem::signal_param_path => Filesystem::normalizePath(stripslashes($fileName)), 'reason' => $error));
            throw new Exception($error);
        }
        $loggedIn = \OC_User::login($username, $passphrase);
        if (!$loggedIn) {
	    $error = "Invalid credentials supplied for Transmission Upload web service.";
	    \OCP\Util::emitHook(
                        'UploadFile',
                        'upload_failure',
                        array(Filesystem::signal_param_path => Filesystem::normalizePath(stripslashes($fileName)), 'reason' => $error));
            throw new Exception($error);
        }
        if (!isset($fileName) || !isset($fileContents) || strpos($fileName,"..")) {
            $error = "Invalid fileName or fileContents supplied for Transmission Upload web service.";    
	    \OCP\Util::emitHook(
                        'UploadFile',
                        'upload_failure',
                        array(Filesystem::signal_param_path => Filesystem::normalizePath(stripslashes($fileName)), 'reason' => $error));
	    throw new Exception($error);
        }


	\OCP\Util::writeLog("uploadfile.php","about to setupFS...",4);
        OC_Util::setupFS($username);
	\OCP\Util::writeLog("uploadfile.php","about to file_put_contents...",4);
        $returnval = Filesystem::file_put_contents($fileName, $fileContents);
	\OCP\Util::writeLog("uploadfile.php","after file_put_contents...",4);

        if($returnval > 0){
	    \OCP\Util::emitHook(
			'UploadFile', 
			'upload_success', 
			array(Filesystem::signal_param_path => Filesystem::normalizePath(stripslashes($fileName))));
	    return "Successfully stored the file. If it was invalid, you will receive an alert email. The filesystem wrote a $returnval byte file.";
        }else{
	    $error = "ERROR - Could not write file, error code $returnval.";
	    \OCP\Util::emitHook(
                        'UploadFile',
                        'upload_failure',
                        array(Filesystem::signal_param_path => Filesystem::normalizePath(stripslashes($fileName)), 'reason' => $error));
            throw new Exception($error);
        }
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
    $lastBit = strtok($_SERVER["REQUEST_URI"], '?'); // more flexible way of getting this file's current location
    if ($includeWsdl) {
        $lastBit .= '?wsdl';
    }
    $currentUrl = $currentUrl . '://' . $_SERVER['SERVER_NAME'] . $portBit . $lastBit;
    return $currentUrl;
}

if (isset($_GET['wsdl'])) {
    $autodiscover = new AutoDiscover();
    $autodiscover->setClass('FileTransferService');
    // build the URL accordingly
    $wsdlUrl = constructWsdlUrl(false);
    $autodiscover->setUri($wsdlUrl);
    header('Content-Type: text/xml');
    print($autodiscover->toXml());
} else {
    // host our SOAP service
    $wsdlUrl = constructWsdlUrl(false);
    $server = new SoapServer(null, array('uri' => $wsdlUrl));
    $server->setClass('FileTransferService');
    try {
        $server->handle();
    } catch (Exception $e) {
        $server->fault('Sender', $e->getMessage());
    }
}
