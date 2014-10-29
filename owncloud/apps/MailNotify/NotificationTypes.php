<?php
/**
 * Types of notifications, used to determine opt-out settings.
 * User: SHARRISON
 * Date: 12/6/13
 * Time: 7:05 AM
 */

namespace OCA\MailNotify;


class NotificationTypes
{
    const Transmission_Available = 0;
    const Transmission_Unavailable = 1;
    const Transmission_Upload = 2;
    const Transmission_Upload_Failure = 3;
    const Transmission_Renamed = 4;
    const Transmission_Download = 5;
    const Transmission_Download_Failure = 6;
    const Transmission_Deleted = 7;
    const Transmission_Copied = 8;
    const Transmission_Created = 9;
    const Transmission_Written = 10;
    const Transmission_Approved = 11;
    const Transmission_Rejected = 12;

    const External_Notification = 99;

	/* simple array of all the types for ease of iteration */
	static $CodesArray = array(
		self::Transmission_Available,
		self::Transmission_Unavailable,
		self::Transmission_Upload,
		self::Transmission_Upload_Failure,
		self::Transmission_Renamed,
		self::Transmission_Download,
		self::Transmission_Download_Failure,
		self::Transmission_Deleted,
		self::Transmission_Copied,
		self::Transmission_Created,
		self::Transmission_Written,
		self::Transmission_Approved,
		self::Transmission_Rejected,
		self::External_Notification,
	);

    static $Codes = array(
        self::Transmission_Available => 'Transmission Available',
        self::Transmission_Unavailable => 'Transmission Unavailable',
        self::Transmission_Upload => 'Transmission Uploaded',
        self::Transmission_Upload_Failure => 'Transmission Upload Failed',
        self::Transmission_Renamed => 'Transmission Renamed',
        self::Transmission_Download => 'Transmission Downloaded',
        self::Transmission_Download_Failure => 'Transmission Download Failed',
        self::Transmission_Deleted => 'Transmission Deleted',
        self::Transmission_Copied => 'Transmission Copied',
        self::Transmission_Created => 'Transmission Created',
        self::Transmission_Written => 'Transmission Written',
        self::Transmission_Approved => 'Transmission Approved',
        self::Transmission_Rejected => 'Transmission Rejected',

        self::External_Notification=> 'External Notification',
    );

	static $CodeExplanations = array(
		NotificationTypes::Transmission_Available => 'a transmission is available for download',
		NotificationTypes::Transmission_Unavailable => 'a transmission previously available for download has become unavailable',
		NotificationTypes::Transmission_Upload => 'one of your transmissions was successfully uploaded',
		NotificationTypes::Transmission_Upload_Failure => 'one of your transmissions failed to upload',
		NotificationTypes::Transmission_Renamed => 'one of your uploaded transmissions has been renamed',
		NotificationTypes::Transmission_Download => 'a transmission has been successfully downloaded',
		NotificationTypes::Transmission_Download_Failure => 'a transmission failed to download',
		NotificationTypes::Transmission_Deleted => 'one of your transmissions was deleted',
		NotificationTypes::Transmission_Copied => 'one of your transmissions was copied',
		NotificationTypes::Transmission_Created => 'one of your transmissions was successfully started on the server',
		NotificationTypes::Transmission_Written => 'one of your transmissions was successfully written to the server',
		NotificationTypes::Transmission_Approved => 'one of your transmissions has been approved for delivery to its destination',
		NotificationTypes::Transmission_Rejected => 'one of your transmissions was rejected for delivery',
		NotificationTypes::External_Notification => 'one of your recipients has sent a notification to you',
	);


	public static function getLabel($code)
    {
        if (!array_key_exists($code, self::$Codes)) {
            return 'N/A';
        }
        return self::$Codes[$code];
    }
} 