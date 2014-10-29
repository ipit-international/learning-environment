<?php

OC_JSON::checkLoggedIn();
OCP\JSON::callCheck();

$l=OC_L10N::get('core');

//$i=($_POST['index']);

$emailString = null;
$bademail = null;
$success = true;

foreach ($_POST["notEmail"] as $email) {
    if( isset( $email ) && filter_var( $email, FILTER_VALIDATE_EMAIL) ) {
        $emailTrim=trim($email);
        if ($emailString != null ){
            $emailString = $emailString.','.$emailTrim;
        } else {
            $emailString = $emailTrim;
        }
    } else {
        $success = false;
        $emailTrim=trim($email);
        if ($bademail != null ){
            $bademail = $bademail.','.$emailTrim;
        } else {
            $bademail = $emailTrim;
        }
    }
}

if ($success == true){
	OC_Preferences::setValue(OC_User::getUser(), 'settings', 'notificationEmail', $emailString);
	$arr = implode( ",", $_POST['notification_opt'] );
	OC_Preferences::setValue(OC_User::getUser(), 'settings', 'notificationTypes', $arr );
	OC_JSON::success(array("data" => array( "message" => $l->t("Email saved") )));
} else {
    OC_JSON::error(array("data" => array( "message" => $l->t($bademail.' Not formatted correctly. No changes made.') )));
}


