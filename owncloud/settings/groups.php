<?php
/**
 * Copyright (c) 2011, Robin Appelman <icewind1991@gmail.com>
 * This file is licensed under the Affero General Public License version 3 or later.
 * See the COPYING-README file.
 */

//OC_Util::checkSubAdminUser();
//MITRE COMMENT: we commented out the previous line so that normal users can see the page
//with this line in non-subadmins/admins cant access the page at all.
OC_App::loadApps();

// We have some javascript foo!
//OC_Util::addScript( 'settings', 'users') ;//why the hell was this included?
OC_Util::addScript( 'core', 'multiselect' );
OC_Util::addScript( 'core', 'singleselect' );
OC_Util::addScript('core', 'jquery.inview');
OC_Util::addScript('settings', 'groups');
OC_Util::addStyle( 'settings', 'settings' );
OC_App::setActiveNavigationEntry( 'core_groups' );

$isadmin = OC_User::isAdminUser(OC_User::getUser());
$issubadmin = OC_SubAdmin::isSubAdmin(OC_User::getUser());

if (!empty($_GET['deleteGroup']) && ($_GET['deleteGroup']=='delete' ))
{
	//MITRE COMMENT: 
	//This is the more effective approach to prevent non-authorized users from deleting their groups
	//simply making the delete button unavailable could allow someone to submit a request to delete a group
	//without even being able to view the page
	if ($isadmin || OC_SubAdmin::isSubAdminofGroup(OC_User::getUser(), $_GET['groupname'])) { 
	  OC_Group::deleteGroup( $_GET['groupname'] );
	}
}
else if ((!empty( $_POST['groupname'] )) && ($isadmin || OC_SubAdmin::isSubAdminofGroup(OC_User::getUser(),$_POST['groupname'])))  // create the group if it's specified
{
	$group = $_POST['groupname'];
	// create the base group first
	if(!OC_Group::groupExists($group)) {
		OC_Group::createGroup($group);
	}
        $stmt = OC_DB::prepare( "UPDATE `*PREFIX*groups` SET `displayname`=?, `country`=?, `giin`=?, `type`=?, `publicKey`=?, `suEmail`=?, `encryption`=?, `compression`=?, `antivirus`=?, `issuer`=?, `subject`=?, `certVersion`=? WHERE `gid`='" . $group . "'"  );
        $result = $stmt->execute( array( $_POST['displayname'], $_POST['country'], $_POST['giin'], $_POST['type'], $_POST['publicKey'], $_POST['suEmail'], $_POST['encryption'], $_POST['compression'], $_POST['antivirus'], $_POST['issuer'], $_POST['subject'], $_POST['certVersion'] ) );

}

$users = array();
$groups = array();

$recoveryAdminEnabled = OC_App::isEnabled('files_encryption') &&
					    OC_Appconfig::getValue( 'files_encryption', 'recoveryAdminEnabled' );

//if($isadmin) {
	$accessiblegroups = OC_Group::getGroups();
	$accessibleusers = OC_User::getDisplayNames('', 30);
	$subadmins = OC_SubAdmin::getAllSubAdmins();
if (!$isadmin) {
	$accessiblegroups = array_flip($accessiblegroups);
	unset($accessiblegroups['admin']);
	$accessiblegroups = array_flip($accessiblegroups);
}
/*}elseif ($issubadmin){
	$accessiblegroups = OC_SubAdmin::getSubAdminsGroups(OC_User::getUser());
	$accessibleusers = OC_Group::displayNamesInGroups($accessiblegroups, '', 30);
	$subadmins = false;
} else {
	$accessiblegroups = OC_Group::getUserGroups(OC_User::getUser());
	$accessibleusers = OC_Group::displayNamesInGroups($accessiblegroups, '', 30);
	$subadmins = false;
}*/

// load preset quotas
$quotaPreset=OC_Appconfig::getValue('files', 'quota_preset', '1 GB, 5 GB, 10 GB');
$quotaPreset=explode(',', $quotaPreset);
foreach($quotaPreset as &$preset) {
	$preset=trim($preset);
}
$quotaPreset=array_diff($quotaPreset, array('default', 'none'));

$defaultQuota=OC_Appconfig::getValue('files', 'default_quota', 'none');
$defaultQuotaIsUserDefined=array_search($defaultQuota, $quotaPreset)===false
	&& array_search($defaultQuota, array('none', 'default'))===false;

// load users and quota
foreach($accessibleusers as $uid => $displayName) {
	$quota=OC_Preferences::getValue($uid, 'files', 'quota', 'default');
	$isQuotaUserDefined=array_search($quota, $quotaPreset)===false
		&& array_search($quota, array('none', 'default'))===false;

	$name = $displayName;
	if ( $displayName !== $uid ) {
		$name = $name . ' ('.$uid.')';
	}

	$users[] = array(
		"name" => $uid,
		"displayName" => $displayName,
		"groups" => OC_Group::getUserGroups($uid),
		'quota' => $quota,
		'isQuotaUserDefined' => $isQuotaUserDefined,
		'subadmin' => OC_SubAdmin::getSubAdminsGroups($uid),
	);
}

foreach( $accessiblegroups as $i ) {
	// Do some more work here soon
	$groups[] = \OCP\MC_Group::getGroupInfo( $i );// array( "name" => $i );
}

$tmpl = new OC_Template( "settings", "groups", "user" );
$tmpl->assign( 'users', $users );
$tmpl->assign( 'groups', $groups );
$tmpl->assign( 'isadmin', (int) $isadmin);
$tmpl->assign( 'subadmins', $subadmins);
$tmpl->assign( 'numofgroups', count($accessiblegroups));
$tmpl->assign( 'quota_preset', $quotaPreset);
$tmpl->assign( 'default_quota', $defaultQuota);
$tmpl->assign( 'defaultQuotaIsUserDefined', $defaultQuotaIsUserDefined);
$tmpl->assign( 'recoveryAdminEnabled', $recoveryAdminEnabled);
$tmpl->assign( 'enableAvatars', false );
$tmpl->printPage();
