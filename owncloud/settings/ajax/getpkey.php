<?php
OC_JSON::callCheck();
//OC_JSON::checkSubAdminUser();
//$groupname = 'test';
if (isset($_POST['groupname'])) {
	$groupname = $_POST['groupname'];
} else {
	OC_JSON::error(array("data" => array( "message" => "No group." )));
}

if (OC_User::isAdminUser(OC_User::getUser())) {
	$result = \OCP\MC_Group::getGroupInfo($groupname);
	OC_JSON::success(array('data' => $result ));
	//OC_JSON::success(array('data' => $result['publicKey'] ));
} else {
	$result = \OCP\MC_Group::getGroupInfo($groupname);
	OC_JSON::success(array('data' => $result ));
	//OC_JSON::success(array('data' => $result['publicKey'] ));
	//if user allowed to see it then 
}
