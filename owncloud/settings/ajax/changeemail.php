<?php
OC_JSON::checkLoggedIn();
OCP\JSON::callCheck();

$l=OC_L10N::get('core');

if( isset( $_POST['email'] ) && filter_var( $_POST['email'], FILTER_VALIDATE_EMAIL) ) {
	$email=trim($_POST['email']);
	$uid=trim($_POST['username']);
	OC_Preferences::setValue($uid, 'settings', 'email', $email);
	$dude = OC_Preferences::getValue($uid, 'settings', 'email');
	OC_JSON::success(array("data" => array( "message" => $l->t("Email saved") )));
}else{
	OC_JSON::error(array("data" => array( "message" => $l->t("Invalid email") )));
}
