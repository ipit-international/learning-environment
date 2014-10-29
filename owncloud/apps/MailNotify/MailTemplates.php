<?php
/**
 * Created by JetBrains PhpStorm.
 * User: SHARRISON
 * Date: 10/11/13
 * Time: 11:45 AM
 * To change this template use File | Settings | File Templates.
 */

namespace OCA\MailNotify;


class MailTemplates
{
    /**
     * Template definition for email notification
     */
    const MESSAGE = "IPIT Transmission ID:\t\t\t%s
From:\t\t\t\t\t%s
To:\t\t\t\t\t%s
Sender ID:\t\t\t\t%s
Sending Application Timestamp:\t%s
File Size:\t\t\t\t%s
Sending Timestamp:\t\t\t%s
Alert Timestamp:\t\t%s
Alert Code:\t\t\t%s

Message:
%s

_______________________________________________________
This alert email was auto-generated from IPIT
International.  To view or change alert preferences, 
go to %s/index.php/settings/personal
";

    /**
     * Subject for unshare email
     */
    const UNSHARE_SUBJECT = "[IPIT] Transmission no longer available (%s)";

    /**
     * Subject for Share email
     */
    const SHARE_SUBJECT = "[IPIT] Transmission available for download (%s)";

    /**
     * Subject for Rename email
     */
    const RENAME_SUBJECT = "[IPIT] Transmission renamed (%s)";

    /**
     * Subject for Read/access email.
     */
    const READ_SUBJECT = "[IPIT] Transmission accessed/downloaded (%s)";

    /**
     * Subject for write email.
     */
    const WRITE_SUBJECT = "[IPIT] Transmission written to exchange (%s)";

    /**
     * Subject for delete email
     */
    const DELETE_SUBJECT = "[IPIT] Transmission deleted (%s)";

    /**
     * Subject for copy email
     */
    const COPY_SUBJECT = "[IPIT] Transmission copied (%s)";

    /**
     * Subject for Create email.
     */
    const CREATE_SUBJECT = "[IPIT] Transmission created (%s)";

    /**
     * Subject for Upload Success email.
     */
    const UPLOAD_SUCCESS_SUBJECT = "[IPIT] Transmission uploaded (%s)";

    /**
     * Subject for Upload Failure email.
     */
    const UPLOAD_FAILURE_SUBJECT = "[IPIT] Transmission upload failed (%s)";

    /**
     * Subject for Download Failure email.
     */
    const DOWNLOAD_FAILURE_SUBJECT = "[IPIT] Transmission download failed (%s)";

    /**
     * Subject for external message
     */
    const EXTERNAL_MESSAGE_SUBJECT = "[IPIT] External notification for transmission (%s)";

    /**
     * Subject for when a transmission is rejected by the HCTA
     */
    const TRANSMISSION_REJECT_SUBJECT = "[IPIT] Transmission rejected by TA (%s)";

    /**
     * Subject for when a transmission is approved by HCTA
     */
    const TRANSMISSION_APPROVE_SUBJECT = "[IPIT] Transmission approved by TA (%s)";

    /**
     * Parameters for message template array
     */
    const param_noticeId = 'nofiticationId';
    const param_senderId = 'senderId';
    const param_senderName = 'senderName';
    const param_recipients = 'recipients';
    const param_timestamp = 'timestamp';
    const param_filesize = 'filesize';
    const param_notificationCode = 'ncode';
    const param_message = 'message';

    static $serverUrl = null;

    /**
     * @brief Fill the email message template with the desired values.
     * @param $noticeId - ID of this notice
     * @param $senderId - ID of sender in system
     * @param $senderName - Name of sender
     * @param $timeStamp - Time that notification was generated
     * @param $fileSize - size of file
     * @param $code - Notification code
     * @param $message - free text message for notification
     * @param string $recipientNames - Name(s) of any recipients [optional]
     * @return string - the formatted message string
     */
    public static function fillMessage($noticeId, $senderId, $senderName, $timeStamp, $fileSize, $code, $message, $recipientNames = '')
    {
        $parameters = array(
            self::param_filesize => $fileSize,
            self::param_noticeId => $noticeId,
            self::param_notificationCode => $code,
            self::param_recipients => $recipientNames,
            self::param_senderId => $senderId,
            self::param_senderName => $senderName,
            self::param_timestamp => $timeStamp,
            self::param_message => $message
        );
        $filled = MailTemplates::fillMessageTemplate($parameters);
        return $filled;
    }

    /**
     * @brief Fill the email message template with the desired values.
     * @param $params - array of parameters for message.
     * @return string - the formatted message string
     */
    public static function fillMessageTemplate($params)
    {
        if (is_int($params[self::param_timestamp])) {
            $params[self::param_timestamp] = date('o-m-d H:i:s e', $params[self::param_timestamp]);
        }

        if (is_null(self::$serverUrl)) {
            $portBit = '';
            $currentUrl = 'http';
           // if ($_SERVER['HTTPS'] == 'on') {
	    if ( isset(  $_SERVER["HTTPS"] ) && strtolower( $_SERVER["HTTPS"] ) == "on" ) {
                $currentUrl .= 's';
                if($_SERVER['SERVER_PORT'] != '443'){
                    $portBit = ':'.$_SERVER['SERVER_PORT'];
                }
            }
            else{
                if($_SERVER['SERVER_PORT'] != '80'){
                    $portBit = ':'.$_SERVER['SERVER_PORT'];
                }
            }
            self::$serverUrl = $currentUrl .'://' . $_SERVER['SERVER_NAME'] . $portBit;
        }
        
        $message = sprintf(
            self::MESSAGE,
            $params[self::param_noticeId],
            $params[self::param_senderName],
            $params[self::param_recipients],
            $params[self::param_senderId],
            $params[self::param_timestamp],
            $params[self::param_filesize],
            $params[self::param_timestamp],
            $params[self::param_timestamp],
            $params[self::param_notificationCode],
            $params[self::param_message],
            self::$serverUrl
        );
        return $message;
    }
}
