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
 * Date: 7/15/14
 * Time: 10:45 AM
 * To change this template use File | Settings | File Templates.
 */
use OC\Files\Filesystem;
use Zend\Soap\AutoDiscover;
use \OCP\MC_Group;
use \OCP\Util;
use OCP\App;

class TransmissionDownloadService
{
    /**
     * List transmissions available for download by a given user
     * @param string $username - user that is downloading
     * @param string $passphrase - password for user.  Some special characters need to be escaped to ensure valid XML
     * @return array -  key-value pairs describing each shared file. The key is a number which can be passed to downloadTransmission to retrieve a file. The value is nested key-value pairs listing file path, timestamp, sender, and recipient Jurisdiction.
     * @throws Exception
     */
    public function listTransmissions($username,
                                      $passphrase)
    {
        // initialize OC stuff and input validation
        \OC::initPaths();
        \OC_User::setupBackends();
        if (!isset($username) || !isset($passphrase)) {
            throw new Exception("Incomplete credentials supplied for Transmission Download web service");
        }
        $loggedIn = \OC_User::login($username, $passphrase);
        if (!$loggedIn) {
            throw new Exception("Invalid credentials supplied for Transmission Download web service");
        }

        //Get a list of all files shared with this user, along with metadata about the sharing event
        $receivedFiles = \OCP\Share::getItemSharedWith('file', NULL);

        $responseArray = array();
        foreach ($receivedFiles as $shareEventID => $shareDetailsArray) {
            //Note: If a user is in multiple groups and the same file is shared with more than one of her groups, she will only be informed of one of the shares.
            $responseArray[$shareEventID]['path'] = $shareDetailsArray['path'];
            $responseArray[$shareEventID]['shareTime'] = $shareDetailsArray['stime'];
            $responseArray[$shareEventID]['shareWith'] = $shareDetailsArray['share_with_displayname'];
            $responseArray[$shareEventID]['sender'] = $shareDetailsArray['displayname_owner'];
        }
        return $responseArray;
    }


    /**
     * Download Transmission: Retrieve a ZIP file containing a payload and metadata in one SOAP request
     * @param string $username - user that is downloading
     * @param string $passphrase - password for user. Some special characters need to be escaped to ensure valid XML
     * @param string $uniqueID - unique identifier for the transmission to be downloaded. This is the number returned as the key in the listing from listTransmissions.
     * @return string -  a Transmission, the contents of a ZIP file encoded with base64
     * @throws Exception
     */
    public function downloadTransmission($username,
                                         $passphrase,
                                         $uniqueID)
    {
        // initialize OC stuff and input validation
        \OC::initPaths();
        \OC_User::setupBackends();
        if (!isset($username) || !isset($passphrase)) {
            throw new Exception("Incomplete credentials supplied for Transmission Download web service");
        }
        $loggedIn = \OC_User::login($username, $passphrase);
        if (!$loggedIn) {
            throw new Exception("Invalid credentials supplied for Transmission Download web service");
        }
        if (!isset($uniqueID)) {
            throw new Exception("No unique transmission identifier supplied to Transmission Download web service");
        }

        //ensure that the Filesystem object is ready for reading
        Filesystem::init($username, '/');

        $receivedFiles = \OCP\Share::getItemSharedWith('file', NULL);
        $sharingDetailsOfDesiredFile = $receivedFiles[$uniqueID];
        //get sharer's username
        $userWhoSharedFile = $sharingDetailsOfDesiredFile['uid_owner'];
        //filecacheID matches fileid from table oc_filecache
        $filecacheID = $sharingDetailsOfDesiredFile['item_source'];
        // returns two-item array:  0 => storage 1 => path
        $cacheResults = \OC\Files\Cache\Cache::getById($filecacheID);
        $sharersRelativePathToFile = $cacheResults[1];
        // read file into memory, from its location in the sender's file storage
        $transmissionContents = Filesystem::file_get_contents($userWhoSharedFile . '/' . $sharersRelativePathToFile);
        $transmissionContents = base64_encode($transmissionContents);
        if ($transmissionContents == false) {
            throw new Exception("File contents could not be Base64 encoded before sending.");
        }
        return $transmissionContents;
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
    $autodiscover->setClass('TransmissionDownloadService');
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
    $server->setClass('TransmissionDownloadService');
    try {
        $server->handle();
    } catch (Exception $e) {
        $server->fault('Sender', $e->getMessage());
    }
}
