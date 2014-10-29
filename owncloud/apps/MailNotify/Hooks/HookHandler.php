<?php

/**
 * ownCloud - MailNotify App
 *
 * TODO
 *
 */


namespace OCA\MailNotify\Hooks;

require_once 'lib/base.php';

use OC\Files\Cache\Shared_Cache;
use \OC\Files\Filesystem;
use OCA\FileRouter\Db\TransmissionMapper;
use OCA\MailNotify\NotificationTypes;
use \OCP\Files;
use OCP\MC_Group;
use \OCP\Share;
use \OCP\Util;
use \OCA\MailNotify\MailTemplates;
use \OCA\MailNotify\Db\Item;
use \OCA\MailNotify\Db\ItemMapper;
use \OCP\MC_User;
use \OC\User;


/**
 * @brief The class to system hooks
 */
class HookHandler
{
    const CLASSNAME = '\OCA\MailNotify\Hooks\HookHandler';

    /**
     * functions to handle file events
     */
    const handler_file_rename = 'handle_rename';
    const handler_file_create = 'handle_create';
    const handler_file_copy = 'handle_copy';
    const handler_file_write = 'handle_write';
    const handler_file_read = 'handle_read';
    const handler_file_delete = 'handle_delete';

    /**
     * functions to handle Sharing events
     */
    const handler_unshare = 'handle_unshare';
    const handler_shared = 'handle_share';

    /**
     * functions to handle upload success or failure events
     */
    const handler_file_upload_success = 'handle_file_upload_success';
    const handler_file_upload_failure = 'handle_file_upload_failure';
    const handler_external_message = 'handle_external_message';
    const handler_file_unavailable = 'handle_file_unavailable';
    const handler_file_unavailable_two = 'handle_file_unavailable_two';
    const handler_transmission_reject = 'handle_transmission_reject';
    const handler_transmission_approve = 'handle_transmission_approve';

    static $api;
    static $verbose = true;

    static function createNotification($fileId, $noticeType, $noticeCode, $subjectTemplate, $noticeText, $noticeId = null, $fullPath = null, $transmissionId = null)
    {
        if (empty($fileId)) {
            return null;
        }
        if (empty($noticeId) || is_null($noticeId)) {
            $noticeId = self::getNoticeId();
        }
        if (self::$verbose) {
            $params = array(
                'file-id' => $fileId,
                'type' => NotificationTypes::getLabel($noticeType),
                'subject' => $subjectTemplate,
                'text' => $noticeText,
                'notice-id' => $noticeId,
                'full_path' => $fullPath
            );
            $logMsg = sprintf('Notice helper method params: %s', serialize($params));
            Util::writeLog('MailNotify', $logMsg, Util::DEBUG);
        }

        // if this notification relates to a transmission, use that for notification recipients
        $mapper = new TransmissionMapper(self::getApi());
        $tx = $mapper->getTransmission($transmissionId);
        if (!is_null($transmissionId) && !is_null($tx)) {
            $owner = $tx->getSender();
            $recvGroup = MC_Group::getGroupInfo($tx->getRecipient());
            $intGroup = MC_Group::getGroupInfo($tx->getIntermediate());
            $size = filesize($fullPath);
            $shareUsers = array_merge($recvGroup['users'], $intGroup['users']);
        } else {
            // otherwise, this is our file.
            $filePath = Filesystem::getPath($fileId);
            $size = Filesystem::filesize($filePath);
            $owner = Filesystem::getOwner($filePath);
            $recipients = Share::getUsersSharingFile($filePath, $owner, false);
            $shareUsers = $recipients['users'];
        }

        $time = time();
        $recipDisplayNames = self::getDisplayNames($shareUsers);
        $subject = sprintf($subjectTemplate, $noticeId);
        $msg = MailTemplates::fillMessage($noticeId, $owner, join(',', self::getDisplayNames($owner)), $time, $size, $noticeCode, $noticeText, join(',', $recipDisplayNames));

        $item = self::createItem($noticeId, $owner, join(',', $shareUsers), $subject, $msg, $noticeCode, $time, $noticeType, $fileId);
        HookHandler::queueNotification($item);

/*
        try {
            $senderInfo = MC_User::getUserInfo();
            $senderCountry = $senderInfo['country'];
            $rulesResponse = RulesManager::executeNotificationRules($senderCountry);
            Util::writeLog('MailNotify-RuleCheck', $rulesResponse, Util::DEBUG);
        } catch (\Exception $e) {
            Util::writeLog('MailNotify-RuleCheck', $e->getMessage(), Util::WARN);
        }
*/
        // Alert support via SOAP could go here too (for ALL hooks this file handles.
        // Right now we're just looking for files sent to a certain recipient, so...
        // we will add our call in handle_share() in this class.
        return $item->getNid();
    }

    public static function handle_transmission_reject($params)
    {
        try {
            if (self::$verbose) {
                $msg = 'Transmission Reject ' . serialize($params);
                Util::writeLog('MailNotify', $msg, Util::DEBUG);
            }
            // expect to get file id or transmission id
            $fileId = $params['fileid'];
            $transId = $params['transmissionid'];
            $mapper = new TransmissionMapper(self::getApi());
            $tx = $mapper->getTransmission($transId);
            $newId = self::createNotification($fileId, NotificationTypes::Transmission_Rejected, '503', MailTemplates::TRANSMISSION_REJECT_SUBJECT, 'Transmission rejected by the Tax Authority.', null, $tx->getFullpath(), $transId);
            if ($newId == null) {
                throw new \Exception('Unable to create notification.');
            }
        } catch (\Exception $e) {
            $msg = sprintf("Error while handling transmission reject message: %s\n%s", $e->getMessage(), $e->getTraceAsString());
            Util::writeLog('MailNotify', $msg, Util::ERROR);
        }
    }

    public static function handle_transmission_approve($params)
    {
        try {
            if (self::$verbose) {
                $msg = 'Transmission Approve ' . serialize($params);
                Util::writeLog('MailNotify', $msg, Util::DEBUG);
            }
            // expect to get file id or transmission id
            $fileId = $params['fileid'];
            $transId = $params['transmissionid'];
            $mapper = new TransmissionMapper(self::getApi());
            $tx = $mapper->getTransmission($transId);
            $newId = self::createNotification($fileId, NotificationTypes::Transmission_Approved, '200', MailTemplates::TRANSMISSION_APPROVE_SUBJECT, 'Transmission approved for release by the Tax Authority.', null, $tx->getFullpath(), $transId);
            if ($newId == null) {
                throw new \Exception('Unable to create notification.');
            }

        } catch (\Exception $e) {
            $msg = sprintf("Error while handling transmission approve message: %s\n%s", $e->getMessage(), $e->getTraceAsString());
            Util::writeLog('MailNotify', $msg, Util::ERROR);
        }
    }

    public static function handle_file_unavailable_two($params)
    {
//        \OCP\Util::emitHook('MappedLocal', 'file_unavailable', array('path' => $path));
        self::handle_file_unavailable($params);
    }

    public static function handle_file_unavailable($params)
    {
        try {
            // Util::emitHook("FileQueue","Signal_Download_Failed",array('path'=>$params['path'])); //Emit a hook for failure
            if (self::$verbose) {
                Util::writeLog(self::CLASSNAME, 'File unavailable notice: ' . serialize($params), Util::DEBUG);
            }
            $filename = $params['path'];
            $fileinfo = Filesystem::getFileInfo($filename);
            if (is_null($fileinfo) || is_null($fileinfo['fileid'] || empty($fileinfo['fileid']))) {
                Util::writeLog(self::CLASSNAME, 'Received file unavailable when file does not yet exist. Ignoring hook.', Util::DEBUG);
                return;
            }
            $fileId = $fileinfo['fileid'];
            $newId = self::createNotification($fileId, NotificationTypes::Transmission_Download_Failure, '404', MailTemplates::DOWNLOAD_FAILURE_SUBJECT, 'Transmission unavailable when download was attempted.');
            if ($newId == null) {
                throw new \Exception('Unable to create notification.');
            }
        } catch (\Exception $e) {
            $msg = sprintf("Error while handling file unavailable message: %s\n%s", $e->getMessage(), $e->getTraceAsString());
            Util::writeLog('MailNotify', $msg, Util::ERROR);
        }
    }

    /**
     * React to an external message.
     * @param $params
     * @return string
     */
    public static function handle_external_message($params)
    {
        try {
            if (self::$verbose) {
                $logMsg = sprintf("Handle external message: %s", serialize($params));
                Util::writeLog('MailNotify', $logMsg, Util::DEBUG);
            }
//            \OCP\Util::emitHook(MessageSender::CLASSNAME, MessageSender::sendMessage_hook, array(
//                MessageSender::message_transmissionid_param => $transmissionId,
//                MessageSender::message_noticeid_param => $newId,
//                MessageSender::message_sender_param => $username,
//                MessageSender::message_code_param => $notificationCode,
//                MessageSender::message_message_param => $messageContents
//            ));

            // copy constants from the message sending class, can't include here.
            $message_noticeid_param = 'noticeid';
            $message_transmissionid_param = 'transmissionid';
            $message_sender_param = 'sender';
            $message_code_param = 'code';
            $message_message_param = 'message';

            $tranId = $params[$message_transmissionid_param];
            $noticeId = $params[$message_noticeid_param];
            //$sender = $params[$message_sender_param];
            $code = $params[$message_code_param];
            $text = $params[$message_message_param];

            // init FileSystem before accessing..
            if (!Filesystem::$loaded) {
                \OC_Util::setupFS();
            }

            $filePath = Filesystem::getPath($tranId);
            if (empty($filePath)) {
                Util::writeLog(HookHandler::CLASSNAME, 'Unable to determine filePath for file id: ' . $tranId, Util::ERROR);
                return;
            }
            $newId = self::createNotification($tranId, NotificationTypes::External_Notification, $code, MailTemplates::EXTERNAL_MESSAGE_SUBJECT, $text, $noticeId);
            if ($newId == null) {
                throw new \Exception('Unable to create notification.');
            }
        } catch (\Exception $e) {
            $msg = sprintf("Error while handling external message: %s\n%s", $e->getMessage(), $e->getTraceAsString());
            Util::writeLog('MailNotify', $msg, Util::ERROR);
        }
    }

    /**
     * React to a successful upload event
     * @param $params
     */
    public static function handle_file_upload_success($params)
    {
        try {
            if (self::$verbose) {
                $msg = sprintf("Handle upload success: %s", serialize($params));
                Util::writeLog('MailNotify.HookHandler', $msg, Util::DEBUG);
            }
            $path = $params[Filesystem::signal_param_path];
            $fileinfo = Filesystem::getFileInfo($path);
            $fileId = $fileinfo['fileid'];
            $newId = self::createNotification($fileId, NotificationTypes::Transmission_Upload, '1337', MailTemplates::UPLOAD_SUCCESS_SUBJECT, 'Transmission uploaded.');
            if ($newId == null) {
                throw new \Exception('Unable to create notification.');
            }
        } catch (\Exception $ex) {
            $msg = sprintf("Error while handling upload success notification: %s\n%s", $ex->getMessage(), $ex->getTraceAsString());
            Util::writeLog('MailNotify', $msg, Util::ERROR);
        }
    }

    /**
     * React to a failed upload
     * @param $params
     */
    public static function handle_file_upload_failure($params)
    {
        try {
            if (self::$verbose) {
                $msg = sprintf("Handle upload failure: %s", serialize($params));
                Util::writeLog('MailNotify.HookHandler', $msg, Util::DEBUG);
            }
            $path = $params[Filesystem::signal_param_path];
            $fileinfo = Filesystem::getFileInfo($path);
            $fileId = $fileinfo['fileid'];
            $newId = self::createNotification($fileId, NotificationTypes::Transmission_Upload_Failure, '1338', MailTemplates::UPLOAD_FAILURE_SUBJECT, 'Transmission upload has failed: ' . $params['reason'] . '.');
            if ($newId == null) {
                throw new \Exception('Unable to create notification.');
            }
        } catch (\Exception $ex) {
            $msg = sprintf("Error while handling upload failure notification: %s\n%s", $ex->getMessage(), $ex->getTraceAsString());
            Util::writeLog('MailNotify', $msg, Util::ERROR);
        }
    }

    /**
     * React to a share event
     * @param $params
     */
    public static function handle_share($params)
    {
        try {
            if (self::$verbose) {
                $msg = sprintf("Share Notification: %s", serialize($params));
                Util::writeLog('MailNotify.HookHandler', $msg, Util::DEBUG);
            }
            /*
            \OC_Hook::emit('OCP\Share', 'post_shared', array(
                'itemType' => $itemType,
                'itemSource' => $itemSource,
                'itemTarget' => $groupItemTarget,
                'parent' => $parent,
                'shareType' => $shareType,
                'shareWith' => $shareWith['group'],
                'uidOwner' => $uidOwner,
                'permissions' => $permissions,
                'fileSource' => $fileSource,
                'fileTarget' => $groupFileTarget,
                'id' => $parent,
                'token' => $token
            ));
            */

            // first, check if this file is related to a transmission.
            $mapper = new TransmissionMapper(self::getApi());
	    \OCP\Util::writelog('Mail Notify Close to Error', "id = ".$params['fileSource'], 4);

            $tx = $mapper->getTransmissionForFile($params['fileSource']);
            if (!is_null($tx)) {
                $owner = $tx->getSender();
            } else {
                // sender should be a user. recipient should be a group.
                $owner = $params['uidOwner'];
            }
            $ownerInfo = MC_User::getUserInfo($owner);

            // if user isn't the owner, need to look at other user view.
            if (\OC_User::getUser() != $owner) {
                $view = new \OC\Files\View('/' . $owner . '/files');
                $fileinfo = $view->getFileInfo($params['fileTarget']);
            } else {
                $fileinfo = Filesystem::getFileInfo($params['fileTarget']);
            }

            $recipientGroup = $params['shareWith'];
            $recipientInfo = MC_Group::getGroupInfo($recipientGroup);

            $senderIsGov = in_array("TA", $ownerInfo['types']);
            $senderIsFFI = in_array("FFI", $ownerInfo['types']);
            $recipIsGov = (strpos($recipientInfo['type'], "TA") !== FALSE);
            $recipIsFFI = (strpos($recipientInfo['type'], "FI") !== FALSE);
            //I am of the mind that FFI and TA should be mutually exclusive designations.
            //However, for now it is not safe to assume this, or that a user will be either one.

            $id = '';
            if ($recipIsGov) {
                if ($senderIsGov) {
                    $id = HookHandler::getPrefixNoticeId('.G2G');
                    Util::writeLog(self::CLASSNAME, 'Gov to Gov transmission code ' . $id, Util::DEBUG);
                } elseif ($senderIsFFI) {
                    $id = HookHandler::getPrefixNoticeId('.F2G');
                    Util::writeLog(self::CLASSNAME, 'FI to Gov transmission code ' . $id, Util::DEBUG);
                }
            } else {
                $id = HookHandler::getNoticeId();
                Util::writeLog(self::CLASSNAME, 'Unrecognized transmission case ' . $id, UTIL::WARN);
            }

            $fileId = $params['fileSource'];
            $newId = self::createNotification($fileId, NotificationTypes::Transmission_Available, '1001', MailTemplates::SHARE_SUBJECT, 'Transmission made available for download.', $id);
            if ($newId == null) {
                throw new \Exception('Unable to create notification.');
            }

            // Alert support via SOAP
            // When a certain recipient is getting a message, they want a SOAP request to their own endpoint

	    //if( SHARING RECIPIENT == "IRS") then...
            self::sendSoapNotification($fileId, NotificationTypes::Transmission_Available, '1001', MailTemplates::SHARE_SUBJECT, 'Transmission made available for download.', $id, $params);

        } catch (\Exception $ex) {
            $msg = sprintf("Error while handling share notification: %s\n%s", $ex->getMessage(), $ex->getTraceAsString());
            Util::writeLog('MailNotify', $msg, Util::ERROR);
        }
    }

    static function sendSoapNotification($fileId, $noticeType, $noticeCode, $subjectTemplate, $noticeText, $noticeId = null, $OrigParams, $fullPath = null, $transmissionId = null)
    {
	/********************************************
	Open the file and pull metadata out of it
	**********************************************/

	$relativePathToZipfile = $OrigParams['fileTarget'];
	$parsingParams = array(
			'path'=>$relativePathToZipfile
			);
	$retval = \OCA\FileRouter\applogic\Sharer::parseXmlOutOfZipfile($parsingParams);
	list($absolutePath, $xml, $fullPathToZip) = $retval;


    $transmissionReceiver = utf8_decode($xml->FATCAEntityReceiverId);
/*	todo: In the production system, this block will be uncommented. Commenting this out allows us to test, by sending an Alert to AlertService stub for ALL shares.

    // Only send a SOAP Alert if the receiver is the IRS - this is the IRS GIIN as of 9/25 from Duane
    if($transmissionReceiver == null || $transmissionReceiver != '000000.00000.US.840'){
		Util::writeLog('MailNotify::sendSoapNotification','Not sending Alert SOAP request, recipient is not IRS it is '.$transmissionReceiver, Util::ERROR);
		return false;
	} */
	Util::writeLog('MailNotify::sendSoapNotification','Building Alert SoapClient, Metadata says recipient is: '.$transmissionReceiver, Util::ERROR);
	//Do not cache the WSDL - when it changes, we will see errors if we cache
	ini_set('soap.wsdl_cache_enabled',0);
	ini_set('soap.wsdl_cache_ttl',0);

    // todo: change to ICMM endpoint in production system. ICMM endpoint will be under http://icmmextpete.services.irs.gov/
    $client = new \SoapClient("http://beryl.mitre.org:8080/AlertService/AlertService?wsdl");

	// Get current UNIX time
	list($microseconds, $secondsSinceEpoch) = explode(" ", microtime());
	
        $params = array(
            'FATCAIDESAlertRequest'=>array(
                'RequestSentTs'=>$secondsSinceEpoch,
                'IDESTransmissionId'=>'00000000000000',			        // newly generated (by IDES?) todo
                'IDESSendingTs'=>$secondsSinceEpoch,
                'IDESReceivedTs'=>'00000000001',			    	    // todo could use FileCreateTs if ownCloud doesn't log time when uploads occur
                'IDESFileNm'=>utf8_decode($xml->FATCAEntitySenderId).'_Payload',
                'AlertRecipientTypeCd'=>'CR',				    	    // Code for alert recipient type - originator or recipient of communication todo
                'FATCAEntitySenderId'=>utf8_decode($xml->FATCAEntitySenderId),
                'FATCAEntityReceiverId'=>$transmissionReceiver,
                'FileApprovalTypeCd'=>'AA',					            // Sender File's Approval Type Code todo
                'AlertTypeCd'=>'SA',					        	    // Code that identifies the alert type todo
                'AlertTxt'=>'File has been shared',
                'FATCAEntCommunicationTypeCd'=>utf8_decode($xml->FATCAEntCommunicationTypeCd),
                'SenderFileId'=>utf8_decode($xml->SenderFileId),
                'CompressedFileSizeKBQty'=>'Unknown',				    // Owncloud should have this somewhere     [Compressed Payload file size (KB)] todo
                'FileRevisionInd'=>utf8_decode($xml->FileRevisionInd),	// todo Service receives true when $xml->FileRevisionInd is false
                'OriginalIDESTransmissionId'=>utf8_decode($xml->OriginalIDESTransmissionId),
                'SenderContactEmailAddressTxt'=>utf8_decode($xml->SenderContactEmailAddressTxt)
            )
	);
    Util::writeLog('MailNotify::sendSoapNotification','SOAP Request includes these parameters: '.implode('; ', $params['FATCAIDESAlertRequest']), Util::ERROR);
    $response = $client->__soapCall("FatcaIdesDataAvailabilityAlertRequestOperation", $params);

	// cast stdClass $response to associative array
	$responseArray = json_decode(json_encode($response), true);        
    Util::writeLog('MailNotify::sendSoapNotification','Got the soap response: '.implode('; ',$responseArray), Util::ERROR);
    }


    /**
     * @brief React to an unshare event
     * @param $params
     */
    public
    static function handle_unshare($params)
    {
        try {
            if (self::$verbose) {
                $msg = sprintf("Unshare notification: %s", serialize($params));
                Util::writeLog('MailNotify.HookHandler', $msg, Util::DEBUG);
            }
            /*
           \OC_Hook::emit('OCP\Share', 'pre_unshare', array(
                    'itemType' => $itemType,
                    'itemSource' => $itemSource,
                    'fileSource' => $item['file_source'],
                    'shareType' => $shareType,
                    'shareWith' => $shareWith,
                    'itemParent' => $item['parent'],
                ));
            */
            // get details of the existing share.
            $share = Share::getItemShared($params['itemType'], $params['itemSource']);
            $shareInfo = reset($share); // get first element
            $path = $shareInfo['path'];

            $owner = Filesystem::getOwner($path); // get owner of file from path
            $ownerInfo = MC_User::getUserInfo($owner);

            $recipientGroup = $params['shareWith'];
            $recipientInfo = MC_Group::getGroupInfo($recipientGroup);

            $senderIsGov = in_array("TA", $ownerInfo['types']);
            $senderIsFFI = in_array("FFI", $ownerInfo['types']);
            $recipIsGov = (strpos($recipientInfo['type'], "TA") !== FALSE);
            $recipIsFFI = (strpos($recipientInfo['type'], "FI") !== FALSE);
            //I am of the mind that FFI and TA should be mutually exclusive designations.
            //However, for now it is not safe to assume this, or that a user will be either one.

            $id = '';
            if ($recipIsGov) {
                if ($senderIsGov) {
                    $id = uniqid('IPIT G2G de-xmit.');
                    //Util::writeLog(self::CLASSNAME, 'Gov to Gov transmission code ' . $id, Util::DEBUG);
                } elseif ($senderIsFFI) {
                    $id = uniqid('IPIT F2G de-xmit.');
                    //Util::writeLog(self::CLASSNAME, 'FI to Gov transmission code ' . $id, Util::DEBUG);
                }
            } else {
                $id = HookHandler::getNoticeId();
                Util::writeLog(self::CLASSNAME, 'Unrecognized de-transmission case ' . $id, UTIL::WARN);
            }

            $fileinfo = Filesystem::getFileInfo($path);
            $fileId = $fileinfo['fileid'];
            $newId = self::createNotification($fileId, NotificationTypes::Transmission_Unavailable, '1002', MailTemplates::UNSHARE_SUBJECT, 'Transmission made unavailable for download.', $id);
            if ($newId == null) {
                throw new \Exception('Unable to create notification.');
            }
        } catch (\Exception $ex) {
            $msg = sprintf("Error while handling unshare notification: %s\n%s", $ex->getMessage(), $ex->getTraceAsString());
            Util::writeLog('MailNotify', $msg, Util::ERROR);
        }
    }

    /**
     * @brief React to a Rename event
     * @param $params
     */
    public static function handle_rename($params)
    {
        try {
            if (self::$verbose) {
                $msg = sprintf("Handle rename: %s", serialize($params));
                Util::writeLog('MailNotify.HookHandler', $msg, Util::DEBUG);
            }
            /*
            \OC_Hook::emit(
                Filesystem::CLASSNAME,
                Filesystem::signal_post_rename,
                array(
                    Filesystem::signal_param_oldpath => $path1,
                    Filesystem::signal_param_newpath => $path2
                )
            );
            */
            $path = $params[Filesystem::signal_param_newpath];
            $fileinfo = Filesystem::getFileInfo($path);
            $fileId = $fileinfo['fileid'];
            $newId = self::createNotification($fileId, NotificationTypes::Transmission_Renamed, '1003', MailTemplates::RENAME_SUBJECT, 'Transmission has been renamed.');
            if ($newId == null) {
                throw new \Exception('Unable to create notification.');
            }
        } catch (\Exception $ex) {
            $msg = sprintf("Error while handling rename notification: %s\n%s", $ex->getMessage(), $ex->getTraceAsString());
            Util::writeLog('MailNotify', $msg, Util::ERROR);
        }
    }

    public static function handle_create($params)
    {
        try {
            if (self::$verbose) {
                $msg = sprintf("Handle create: %s", serialize($params));
                Util::writeLog('MailNotify.HookHandler', $msg, Util::DEBUG);
            }
            /*
             * \OC_Hook::emit(
                    Filesystem::CLASSNAME,
                    Filesystem::signal_post_create,
                    array(Filesystem::signal_param_path => $path)
                );
             */
            $path = $params[Filesystem::signal_param_path];
            $fileinfo = Filesystem::getFileInfo($path);
            $fileId = $fileinfo['fileid'];
            $newId = self::createNotification($fileId, NotificationTypes::Transmission_Created, '1004', MailTemplates::CREATE_SUBJECT, 'Transmission has been created.');
            if ($newId == null) {
                throw new \Exception('Unable to create notification.');
            }
        } catch (\Exception $ex) {
            $msg = sprintf("Error while handling create notification: %s\n%s", $ex->getMessage(), $ex->getTraceAsString());
            Util::writeLog('MailNotify', $msg, Util::ERROR);
        }
    }

    public static function handle_copy($params)
    {
        try {
            if (self::$verbose) {
                $msg = sprintf("Handle copy: %s", serialize($params));
                Util::writeLog('MailNotify.HookHandler', $msg, Util::DEBUG);
            }
            /*
                \OC_Hook::emit(
                        Filesystem::CLASSNAME,
                        Filesystem::signal_post_copy,
                        array(
                            Filesystem::signal_param_oldpath => $path1,
                            Filesystem::signal_param_newpath => $path2
                        )
                    );
             */
            $path = $params[Filesystem::signal_param_newpath];
            $fileinfo = Filesystem::getFileInfo($path);
            $fileId = $fileinfo['fileid'];
            $newId = self::createNotification($fileId, NotificationTypes::Transmission_Copied, '1005', MailTemplates::COPY_SUBJECT, 'Transmission has been copied.');
            if ($newId == null) {
                throw new \Exception('Unable to create notification.');
            }
        } catch (\Exception $ex) {
            $msg = sprintf("Error while handling copy notification: %s\n%s", $ex->getMessage(), $ex->getTraceAsString());
            Util::writeLog('MailNotify', $msg, Util::ERROR);
        }
    }

    public static function handle_write($params)
    {
        try {
            if (self::$verbose) {
                $msg = sprintf("Handle write: %s", serialize($params));
                Util::writeLog('MailNotify.HookHandler', $msg, Util::DEBUG);
            }
            /*
            \OC_Hook::emit(
                Filesystem::CLASSNAME,
                Filesystem::signal_post_write,
                array(Filesystem::signal_param_path => $path2)
            );
            */
            $path = $params[Filesystem::signal_param_path];
            $fileinfo = Filesystem::getFileInfo($path);
            $fileId = $fileinfo['fileid'];
            $newId = self::createNotification($fileId, NotificationTypes::Transmission_Written, '1006', MailTemplates::WRITE_SUBJECT, 'Transmission has been written.');
            if ($newId == null) {
                throw new \Exception('Unable to create notification.');
            }
        } catch (\Exception $ex) {
            $msg = sprintf("Error while handling write notification: %s\n%s", $ex->getMessage(), $ex->getTraceAsString());
            Util::writeLog('MailNotify', $msg, Util::ERROR);
        }
    }


    //todo: is a read a download?
    public static function handle_read($params)
    {
        try {
            if (self::$verbose) {
                $msg = sprintf("Handle read: %s", serialize($params));
                Util::writeLog('MailNotify.HookHandler', $msg, Util::DEBUG);
            }
            /*
            \OC_Hook::emit(
                Filesystem::CLASSNAME,
                Filesystem::signal_read,
                array(Filesystem::signal_param_path => $path)
            );
            */
            $path = $params[Filesystem::signal_param_path];
            $fileinfo = Filesystem::getFileInfo($path);
            $fileId = $fileinfo['fileid'];
            $newId = self::createNotification($fileId, NotificationTypes::Transmission_Download, '1007', MailTemplates::READ_SUBJECT, 'Transmission has been downloaded.');
            if ($newId == null) {
                throw new \Exception('Unable to create notification.');
            }
        } catch (\Exception $ex) {
            $msg = sprintf("Error while handling read notification: %s\n%s", $ex->getMessage(), $ex->getTraceAsString());
            Util::writeLog('MailNotify', $msg, Util::ERROR);
        }
    }

    public static function handle_delete($params)
    {
        try {
            if (self::$verbose) {
                $msg = sprintf("Handle delete: %s", serialize($params));
                Util::writeLog('MailNotify.HookHandler', $msg, Util::DEBUG);
            }

            $path = $params[Filesystem::signal_param_path];
            $fileinfo = Filesystem::getFileInfo($path);
            $fileId = $fileinfo['fileid'];

            $newId = self::createNotification($fileId, NotificationTypes::Transmission_Deleted, '1008', MailTemplates::DELETE_SUBJECT, 'Transmission has been deleted.');
            if ($newId == null) {
                throw new \Exception('Unable to create notification.');
            }
        } catch (\Exception $ex) {
            $msg = sprintf("Error while handling delete notification: %s\n%s", $ex->getMessage(), $ex->getTraceAsString());
            Util::writeLog('MailNotify', $msg, Util::ERROR);
        }
    }

    /**
     * Generate a unique notification ID within the system.
     * @return string
     */
    static function getNoticeId()
    {
        return uniqid('IPIT');
    }

    /**
     * Generate a unique notification ID within the system given a prefix.
     * @param $prefix
     * @return string
     */
    static function getPrefixNoticeId($prefix)
    {
        return uniqid('IPIT' . $prefix);
    }

    /**
     * @brief Queue a notification to be sent in the background
     */
    static function queueNotification($notification)
    {
        $tranId = $notification->getTransmissionid();
        if (empty($tranId)) {
            Util::writeLog('MailNotify', 'Notification does not have transmission ID. Skipping notification.', Util::WARN);
            return;
        }
        $mapper = new ItemMapper(self::$api);
        $notification = $mapper->insert($notification);
        $notifyId = $notification->getId();

//        $newJob = new MailJob();
//        BackgroundJob::registerJob($newJob, $notifyId);

        $msg = 'Notification Persisted: ' . $notifyId;
        Util::writeLog('MailNotify', $msg, Util::DEBUG);
    }

    /**
     * Get the notifications that have not been sent, and send them.
     */
    public static function sendNotifications()
    {
        try {
            $theApi = self::getApi();
            if (is_null($theApi)) {
                Util::writeLog('MailNotify', 'Attempting to run mail job before app is initialized. Skipping job.', Util::DEBUG);
                // we aren't initialized yet.. exit!
                return;
            }
            $mapper = new ItemMapper($theApi);
            $pendingNotices = $mapper->listUnsentNotifications();
            if (is_null($pendingNotices) || count($pendingNotices) < 1) {
                return;
            }

            foreach ($pendingNotices as $notice) {
                if (is_null($notice)) {
                    continue;
                }
                try {
                    $sent = self::sendNotification($notice->getId(), $notice);
                    if ($sent) {
                        $notice->setSentflag(-1);
                    } else {
                        $notice->setSentflag(0);
                    }
                    $mapper->update($notice);
                } catch (\Exception $e) {
                    Util::writeLog('MailNotify', 'Error sending a notification. Queueing notification for re-send: ' . $e->getMessage(), Util::WARN);
                    // error sending.. not sent!
                    $notice->setSentflag(0);
                    $mapper->update($notice);
                }
            }
        } catch (\Exception $e) {
            Util::writeLog('MailNotify', 'Error sending notifications: ' . $e->getMessage(), Util::DEBUG);
        }
    }

    /**
     * Send an email for a notification.
     * @param $notificationId string - ID of the notification
     * @param $notificationToSend Item - the actual notification to send.
     * @return bool - true if email was sent, false otherwise.
     */
    public static function sendNotification($notificationId, $notificationToSend)
    {
        if (is_null($notificationToSend)) {
            $msg = sprintf('Unable to locate notification. Skipping notification with ID: %s', $notificationId);
            Util::writeLog('MailNotify', $msg, Util::WARN);
            return false;
        }

        //Util::writeLog(self::CLASSNAME, serialize($notificationToSend), Util::DEBUG);

        // collect users to send to..
        $deliveryUsers = array();
        array_push($deliveryUsers, $notificationToSend->getOrigin()); // add notification 'generator'
        if (!is_null($notificationToSend->getRecipients())) {
            $deliveryUsers = array_merge($deliveryUsers, explode(',', $notificationToSend->getRecipients()));
        }
        // have array of UIDs. now, get the appropriate email addresses to send to
        // (based on user preferences AND notification email addresses).
        $deliveryEmails = self::getDeliveryEmails($notificationToSend->getNotificationtype(), $deliveryUsers);

        // delivery emails is an array of all of the email addresses to deliver this notification to (if any!)
        if (is_null($deliveryEmails) || count($deliveryEmails) == 0) {
            // if we found that we don't deliver this to anyone, just return. success!
            Util::writeLog('MailNotify', 'No users opted to receive email for notification ' . $notificationId, Util::DEBUG);
            return true;
        }
        $deliveryEmailString = implode(',', $deliveryEmails);
        $deliveryUsersString = implode(',', $deliveryUsers);	


	Util::writeLog(self::CLASSNAME, "RPG: emails= $deliveryEmailString names= $deliveryUsersString", Util::DEBUG);

        $subject = $notificationToSend->getSubject();
        $message = $notificationToSend->getMessage();
        // wrap lines at 70 chars
        $message = wordwrap($message, 70, "\r\n");

        if (self::$verbose) {
            $msg = sprintf(
                "About to send email. Recipients: %s, Subject: %s, Message: %s",
                $deliveryEmailString,
                $subject,
                $message);
            Util::writeLog(self::CLASSNAME, $msg, Util::DEBUG);
        }

	//mail using owncloud.  This try catch block probably only works
	//on linux and the try catch block using PHP mail() should be used
	//for windows
	try {
	     $domain = \OCP\Config::getSystemValue('trusted_domains');
	     Util::writeLog("MailNotify","RPG: mail@= ".$domain[0],Util::DEBUG);
	     \OC_Mail::send($deliveryEmailString, $deliveryUsersString, $subject, $message, 'Mail_Alert@'.$domain[0], 'IPIT');
             $msg = sprintf("Notification sent %s", $notificationId);
             Util::writeLog(self::CLASSNAME, $msg, Util::DEBUG);

	     if (self::$verbose) {
                $msg = sprintf("Finished sending queued notification: %s", $notificationId);
                Util::writeLog('MailNotify', $msg, Util::DEBUG);
             }
             return true;
	} catch (\Exception $ex) {
   	     $msg = sprintf("Error while sending email notification id: %s: %s\n%s", $notificationId, $ex->getMessage(), $ex->getTraceAsString());
             Util::writeLog('MailNotify', $msg, Util::ERROR);
	     return false;
	}
        /*try {
	    $mailSent = mail($deliveryEmailString, $subject, $message);
            if ($mailSent) {
                $msg = sprintf("Notification sent %s", $notificationId);
                Util::writeLog(self::CLASSNAME, $msg, Util::DEBUG);
            } else {
                $msg = sprintf("Error sending email for notification %s", $notificationId);
                Util::writeLog(self::CLASSNAME, $msg, Util::WARN);
            }
            if (self::$verbose) {
                $msg = sprintf("Finished sending queued notification: %s", $notificationId);
                Util::writeLog('MailNotify', $msg, Util::DEBUG);
            }
            return $mailSent;
        } catch (\Exception $ex) {
            $msg = sprintf("Error while sending email notification id: %s: %s\n%s", $notificationId, $ex->getMessage(), $ex->getTraceAsString());
            Util::writeLog('MailNotify', $msg, Util::ERROR);
            return false;
        }*/
        return false;
    }

    /**
     * Retrieve the email addresses to send a notification to. Applies user preferences for opt-out
     * and for notification email addresses.
     * @param $notificationType int - type of notification being processed
     * @param $users array of users to retrieve email addresses for
     * @return array of email addresses
     */
    static function getDeliveryEmails($notificationType, $users)
    {
        $collectedAddresses = array();
        if (is_null($users) || is_null($notificationType) || count($users) == 0) {
            Util::writeLog('MailNotify', 'No params passed to getDeliveryEmails', Util::WARN);
            return $collectedAddresses;
        }
        foreach ($users as $user) {
            // get a user's notification preferences
            $optedTypes = MC_User::getNotificationOptions($user);
            // if user opted out of all notifications, OR if the current notification type is NOT in their preferences,
            // skip this user.
            if (is_null($optedTypes) || count($optedTypes) == 0 || !in_array($notificationType, $optedTypes)) {
                continue;
            }
            $userAddresses = self::getNotificationEmails($user);
            // add to accumulated array
            $collectedAddresses = array_merge($collectedAddresses, $userAddresses);
        }
        return array_unique($collectedAddresses, SORT_STRING);
    }

    /**
     * @param array $users - array of user IDs to retrieve display names for
     * @return array - array of display names.
     */
    static function getDisplayNames($users)
    {
        $names = array();
        if (is_array($users)) {
            if (empty($users) || count($users) < 1) {
                return array();
            }
            foreach ($users as $uid) {
                $name = \OC_User::getDisplayName($uid);
                if (empty($name)) {
                    // if there is no name, use uid
                    $name = $uid;
                }
                if (empty($name)) {
                    continue;
                }
                array_push($names, $name);
            }
        } else {
            $name = \OC_User::getDisplayName($users);
            array_push($names, $name);
        }
        return $names;
    }

    /**
     * Get the user IDs from a group.
     * @param $group - name of group to get users from.
     * @return array - null if not a group, or an array with display names (value) and user ids(key)
     */
    static function getGroupUsers($group)
    {
//        Util::writeLog(self::CLASSNAME, 'Retrieving users for group ' . $group, Util::DEBUG);
        if (empty($group) || strlen($group) < 1) {
            return null;
        }
        $groupInfo = MC_Group::getGroupInfo($group);
        if (is_null($groupInfo) || empty($groupInfo)) {
            Util::writeLog(self::CLASSNAME, 'Group info is null for group ' . $group, Util::DEBUG);
            return null;
        }
        return $groupInfo["users"];
    }

    /**
     * Get notification emails for a user account
     * @param string $userId - name of userid to get email addresses for.
     * @return array - array of email addresses for a user.
     */
    static function getNotificationEmails($userId)
    {
        if (is_null($userId)) {
            return array();
        }

        $email = MC_User::getNotificationEmails($userId);
        if (is_null($email) || empty($email)) {
            Util::writeLog(self::CLASSNAME, 'Notification email for user not set. reverting to password recovery email for user ' . $userId, Util::WARN);
            array_push($email, \OC_Preferences::getValue($userId, 'settings', 'email'));
        }
        return $email;
    }

    /**
     * Construct a new Item
     * @param $id - ID of this notification to use
     * @param $owner - user ID that generated this notification
     * @param $recipients - other user IDs to receive this notification
     * @param $subject - subject of notice
     * @param $message - message for notice
     * @param $code - code for notice
     * @param $time - time that notification is prepared.
     * @param $type - the type of the notification
     * @param $tranId - transmission that this notification relates to.
     * @return Item - returns a newly filled item
     */
    static function createItem($id, $owner, $recipients, $subject, $message, $code, $time, $type, $tranId)
    {
        $item = new Item();
        $item->setNid($id);

        $item->setOrigin($owner);
        $item->setRecipients($recipients);
        $item->setSubject($subject);
        $item->setMessage($message);
        $item->setNcode($code);
        $item->setTimestamp($time);
        $item->setNotificationtype($type);
        $item->setTransmissionid($tranId);

        return $item;
    }

    /**
     * @return mixed
     */
    public static function getApi()
    {
        return self::$api;
    }

    /**
     * @param mixed $api
     */
    public static function setApi($api)
    {
        self::$api = $api;
    }
}
