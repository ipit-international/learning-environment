<?php
OC_JSON::checkSubAdminUser();
OCP\JSON::callCheck();

$success = true;
$username = $_POST["username"];

if (!OC_User::isAdminUser(OC_User::getUser())
&& (!OC_SubAdmin::isUserAccessible(OC_User::getUser(), $username))) {
	$l = OC_L10N::get('core');
	OC_JSON::error(array( 'data' => array( 'message' => $l->t('Authentication error') )));
	exit();
}

if( OC_User::getLocked($username)) {
	//user is locked, unlock him.
	$action = "Unlock";
	$error = "Unable to unlock user $username";
	$success = OC_User::setLocked( $username, 0 );
	$success = $success && OC_User::setErrorCount( $username, 0 );
}
else {
	//user is not locked, lock him.
	$action = "Lock";
	$error = "Unable to lock user $username"; 
	$success = OC_User::setLocked( $username, 1 );
}

if( $success ) {
	OC_JSON::success(array("data" => array( "username" => $username, "action" => $action)));
}
else{
	OC_JSON::error(array("data" => array( "message" => $error )));
}
