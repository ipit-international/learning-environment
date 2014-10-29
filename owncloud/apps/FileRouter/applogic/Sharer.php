<?php
/**
 * Created by PhpStorm.
 * User: AJROSS
 * Date: 11/15/13
 * Time: 12:46 PM
 */

namespace OCA\FileRouter\applogic;

use OC\Files\Cache\Cache;
use OCA\FileRetentionApp\applogic\FileQueue;
use OCA\FileRouter\Db\Transmission;
use OCA\FileRouter\Db\TransmissionMapper;
use OCA\FileRouter\IGATypes;
use OCA\FileRouter\TransmissionStates;
use OCP\MC_User;
use \OCP\Util;
use \OC\Files\Filesystem;
use OCP\Share;
use OCP\Config;
use \OCP\MC_Group;


/**
 * TODO: rethink the kickoff mechanism for this app - can hooks work? cases for manual vs auto upload?
 *
 * Class Sharer
 * @package OCA\FileRouter\applogic
 */
class Sharer
{

    private $shareFilePath; //full path, example: ends with /file1.txt
    private $shareFileName; //just the filename, example: file1.txt or file1.fsc
    private $shareUsers; //array of IDES usernames
    private $shareGroups; //array of IDES groups
    private $fileId; // identifier of the file just uploaded - from oc_filecache database table
    private $hcta; //M1O2 intermediary
    private $sidecarPath; //fixed - was declared dynamically below


    //We can get the full path, it's OC_INSTALL_ROOT/data/$absolutePath
    public static function handlePostWrite($params)
    {
        $retval = self::parseXmlOutOfZipfile($params);
	list($absolutePath, $xml, $fullPathToZip) = $retval;
        // Begin extracting data from Metadata XML file
        $sender = $xml->FATCAEntitySenderId;
        $recipient = $xml->FATCAEntityReceiverId;
	$recipient = utf8_decode($recipient);
	//$sender = utf8_decode($sender); // why was this taken out between 8/15 and 9/17 ?
        //todo: convert GIIN to Jurisdiction or put GIIN in DB.groups

        // Verify that an IGA exists between partners
        if (!in_array($recipient, MC_User::getIGAPartners())) {
            Util::writeLog('handlePostWrite', "Cannot send from $sender to $recipient.", 4);
        //    Filesystem::unlink($absolutePath);
            return false;
        } else {
            //TODO: need to do some defensive programmin here to protect entries into the transmission table and delete failed shares
	    Util::writeLog("handlePostWrite", "Sharing $absolutePath with $recipient.", Util::FATAL);
            //self::shareFileWithGroup($fullPathToZip, $recipient);
            $insertSuccess = self::insertTransmission($fullPathToZip, $recipient);
            self::shareFileWithGroup($fullPathToZip, $recipient);
            return true;
        }
    }

    /* Utility method to pull out a simplexml object based on a path to a zipfile containing a Metadata XML
     Currently used by Sharer::handlePortWrite() in FileRouter (here), and by MailNotify's HookHandler::sendSoapNotification()
    Yes, that means MailNotify will not send SOAP Alerts unless FileRouter app is enabled in OwnCloud

    parameter: $params, an array containing key=>value of 'path'=>'some/relative/path.txt' (clarify)
    returns: OC AbsolutePath, simplexml object representing a Metadata file, and full filesystem path to ZIP starting at OS root /
    Caveats: Path must be to a valid zipfile with one entry whose filename contains Metadata, and that XML must be less than 4096 bytes
    */

    public static function parseXmlOutOfZipfile($params){
        //get the current user
        $user = \OC_User::getUser();

        //get the path (relative from the hook params)
        $path = $params['path'];

        //setup the filesystem for the user
        \OC_Util::setupFS($user);

        //get the view so we can get the path
        $absolutePath = Filesystem::getView()->getAbsolutePath($path);

        //Verify the new file is a zip
        $fullPathToZip = \OC_Config::getValue("datadirectory", \OC::$SERVERROOT . "/data") . $absolutePath;
        $zipResource = zip_open($fullPathToZip);
        if (!is_resource($zipResource)) {
            //Filesystem::unlink($absolutePath);
            Util::writeLog('handlePostWrite', "All files must be ZIP files, please retry with a ZIP formatted file. Error code $zipResource .Deleting " . $absolutePath, 4);
            return false;
        }

        // Search for Metadata by looping through each file in the zip
        while ($zipEntry = zip_read($zipResource)) {
            //check if XML file
            if (strpos(zip_entry_name($zipEntry), "Metadata")) {
                break;
            }
        }

        // No metadata XML file found, so deleting the zip and terminating
        if ($zipEntry == FALSE) {
            Util::writeLog('handlePostWrite', "Metadata not found inside your ZIP file, please ensure your Transmission contains metadata", 4);
            //Filesystem::unlink($absolutePath);
            return false;
        }

        //todo: Refactor validation into new helper method
        // Found Metadata XML file, checking for validity
        if (!zip_entry_open($zipResource, $zipEntry)) {
            Util::writeLog('handlePostWrite', "Error in reading contents of ZIP file that was just uploaded", 4);
        }

        $startOfEntry = zip_entry_read($zipEntry, 4096);

        Util::writeLog('handlePostWrite', "This file looks like: " . substr($startOfEntry, 0, 50), 4);

        $xml = simplexml_load_string($startOfEntry,
            "SimpleXMLElement",
            LIBXML_ERR_WARNING);


        return array($absolutePath, $xml, $fullPathToZip);
    }

    public function __construct()
    {
        //    Util::writeLog("sharer","In the no-op constructor, please call either webFileUploadHandler (HTTP) or webserviceSharer() (SOAP)",Util::FATAL);
    }

    private static function shareFileWithGroup($path, $recipient)
    {
        $recipientGroupName = \OCP\MC_Group::getGroupNameFromGiin($recipient);
        \OCP\Util::writeLog("File Router Sharer", "local path = $path", Util::FATAL);
        $fileId = self::lookupFileId(Filesystem::getLocalPath($path));
        \OCP\Util::writeLog("File Router Sharer", "fileid = $fileId and recipient is $recipient AKA $recipientGroupName", Util::FATAL);
//        Share::shareItem("file", $fileId, Share::SHARE_TYPE_GROUP, 'Torchland', \OCP\PERMISSION_ALL);
        Share::shareItem("file", $fileId, Share::SHARE_TYPE_GROUP, $recipientGroupName, \OCP\PERMISSION_ALL);//, Filesystem::getLocalPath($path));
    }


    private static function insertTransmission($shareFilePath, $shareWithGroup)
    {
        $fileId = self::lookupFileId(Filesystem::getLocalPath($shareFilePath));
        if (\OC_User::isLoggedIn()) {
            $userInfo = MC_User::getUserInfo();
            if (in_array('TA', $userInfo['types'])) {
                \OCP\Util::writeLog("File Router Sharer", "Trying to do M1O1 transmission", 4);
                \OCP\Util::writeLog("File Router Sharer", "fileId = " . $fileId, 4);
                \OCP\Util::writeLog("File Router Sharer", "share group = " . $shareWithGroup, 4);
                $tx = new Transmission();
                $tx->setFileid($fileId);
                $tx->setIgatype(IGATypes::ModelOneOptionOne);
                $tx->setIntermediate("Not Applicable");
                $tx->setSender($userInfo['userId']);
                $tx->setRecipient($shareWithGroup);
                $tx->setState(TransmissionStates::AutoReleased);
                $tx->setFullpath($shareFilePath);

                $db = new TransmissionMapper(self::makeAppAPI());
                $db->insert($tx);
                \OCP\Util::writeLog("FileRouter Sharer", "table = " . $db->getTableName(), 4);
            } else {
                \OCP\Util::writeLog("File Router Sharer", "Trying to do M1O2 transmission", Util::FATAL);
                \OCP\Util::writeLog("File Router Sharer", "fileId = " . $fileId, Util::FATAL);
                \OCP\Util::writeLog("File Router Sharer", "share group = " . $shareWithGroup, Util::FATAL);

                $tx = new Transmission();
                $tx->setFileid($fileId);

                //todo: get IGA type and HCTA info from groups table
                $tx->setIgatype(IGATypes::ModelOneOptionTwo);
		$hcta = "testSelenium1";
                /*if (!isset($hcta)) {
                    \OCP\Util::writeLog("File Router Sharer", "hcta is not set, that causes DB errors, setting to NONE", Util::FATAL);
                    $hcta = "Not Applicable";
                }*/
                $tx->setIntermediate($hcta);
                $tx->setSender($userInfo['userId']);
                $tx->setRecipient($shareWithGroup);
                $tx->setState(TransmissionStates::PendingReview); //was TransmissionStates::PendingReview
                $tx->setFullpath($shareFilePath);

                $db = new TransmissionMapper(self::makeAppAPI());
                $db->insert($tx);
            }
            return true;
        } else {
            return false;
        }
    }


    /**
           * Makes another instance of the API declared in FileRouter/appinfo/app.php
           * @return \OCA\AppFramework\Core\API
           */
    private static function makeAppAPI()
    {
        $api = new \OCA\AppFramework\Core\API('FileRouter');

        $api->addNavigationEntry(array(
            // the string under which your app will be referenced in owncloud
            'id' => $api->getAppName(),
            // sorting weight for the navigation. The higher the number, the higher
            // will it be listed in the navigation
            'order' => 10,
            // the route that will be shown on startup
            'href' => $api->linkToRoute('tx_index'),
            // the icon that will be shown in the navigation
            // this file needs to exist in img/example.png
            'icon' => $api->imagePath('env.png'),
            // the title of your application. This will be used in the
            // navigation or on the settings page of your app
            'name' => $api->getTrans()->t('Transmissions')
        ));

        //    $api->addScript('public');
        $api->addStyle('public');
        return $api;
    }



    //    //Need a static function here that can lookup in the oc_filecache table the fileid of the file just uploaded.
//    //fileid is referred to as $itemSource, in all of the sharing code.

    private static function lookupFileId($localPath)
    {
        Util::writeLog("Sharer::lookupFileId", "local path = $localPath", Util::FATAL);

        $localPath = trim($localPath, '\\');
        $localPath = trim($localPath, "/");

        $query = \OC_DB::prepare('SELECT `fileid` FROM `*PREFIX*filecache` WHERE `name` = ?');
        $path = array($localPath);
        $query->execute($path);

        $rows[] = array();
//        $msg = serialize($query);

        while ($row = $query->fetchRow()) {
            $rows[] = $row;
            $msg = serialize($row);
            Util::writeLog("Sharer::lookupFileId", $msg, 4);
        }
	//$id="";
        foreach($rows as $r){
                $id = $r['fileid'];
	}/*
        foreach ($rows as $r) {
            if (strlen($r['fileid']) > 0) {
                $id = $r['fileid'];
                Util::writeLog("Sharer::lookupFileId", "ignore errors, we found at least one fileid and it is " . $id, 4);
            }
        }*/
        return $id;
    }
}
