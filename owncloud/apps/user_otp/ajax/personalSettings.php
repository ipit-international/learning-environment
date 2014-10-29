<?php
/**
 * ownCloud - One Time Password plugin
 *
 * @package user_otp
 * @author Frank Bongrand
 * @copyright 2013 Frank Bongrand fbongrand@free.fr
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU AFFERO GENERAL PUBLIC
 * License along with this library. If not, see <http://www.gnu.org/licenses/>.
 * Displays <a href="http://opensource.org/licenses/AGPL-3.0">GNU AFFERO GENERAL PUBLIC LICENSE</a>
 * @license http://opensource.org/licenses/AGPL-3.0 GNU AFFERO GENERAL PUBLIC LICENSE
 *
 */

include_once("user_otp/lib/utils.php");

$l=OC_L10N::get('settings');

OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('user_otp');
OCP\JSON::callCheck();

if( $_POST && $_POST["uid"] ){
  if(OC_User::isAdminUser(OCP\User::getUser())) {
    $uid = $_POST["uid"];
  } else {
    if(\OC_SubAdmin::isSubAdmin(OCP\User::getUser())){
      $uid = $_POST["uid"];
    } else {
      $uid = OCP\User::getUser();
    }
  }
}else{
  OCP\Util::writeLog('ajax', 'Did not get post or post uid.', 4);
  $uid = OCP\User::getUser();
}

// Get data
$mOtp =  new MultiOtpDb(OCP\Config::getAppValue(
    'user_otp','EncryptionKey','DefaultCliEncryptionKey')
);
$mOtp->EnableVerboseLog();
//$mOtp->SetDisplayLogOption(1);

if(
   $_POST &&
   $_POST["otp_action"]==="delete_otp" &&
   $mOtp->CheckUserExists($uid)
){
    if($mOtp->DeleteUser($uid)){
        OCP\JSON::success(array("data" => array( "message" => $l->t("OTP Changed") )));
    }else{
        OCP\JSON::error(array("data" => array( "message" => $l->t("check apps folder rights") )));
    }
}else if (
    $_POST &&
    $_POST["otp_action"]==="send_email_otp" &&
    $mOtp->CheckUserExists($uid)
){

    $mOtp->SetUser($uid);
    
    if(OCP\Config::getAppValue('user_otp','TokenBase32Encode',true)){
        $UserTokenSeed=base32_encode(hex2bin($mOtp->GetUserTokenSeed()));
        //$tmpl->assign('TokenBase32Encode',true);
    }else{
        $UserTokenSeed=hex2bin($mOtp->GetUserTokenSeed());    
    }
    
	$key = 'email';
	$mail ="";
	$query=OC_DB::prepare('SELECT `configvalue` FROM `*PREFIX*preferences` WHERE `configkey` = ? AND `userid`=?');
	$result=$query->execute(array($key, $uid));
	if(!OC_DB::isError($result)) {
		$row=$result->fetchRow();
		$mail = $row['configvalue'];
	}

	$txtmsg = '<html><p>Welcome to IPIT International, '.$uid.'! <br><br>';
	//$txtmsg .= '<p>Here\'s the information you\'ll need to log in for the first time...<br>';
	$txtmsg .= 'The following link will provide you with a one-time password: http://'.getenv('SERVER_NAME').'/otp_tool/index.html?tokenSeed='.$UserTokenSeed.'<br><br>';
	//$txtmsg .= 'User Token Seed : <br>&nbsp;&nbsp;&nbsp;&nbsp;'.$UserTokenSeed."<br><br>";
	//$txtmsg .= 'OTP Type : '.$mOtp->GetUserAlgorithm().'<br>';
	//$txtmsg .= 'Time Interval Or Last Event : '.(strtolower($mOtp->GetUserAlgorithm())==='htop'?$mOtp->GetUserTokenLastEvent():$mOtp->GetUserTokenTimeInterval())."<br><br>";
	//$txtmsg .= 'You can convert your User Token Seed into a one-time password here http://'.getenv('SERVER_NAME').'/otp_tool/index.html?tokenSeed='.$UserTokenSeed.'<br>';
	//$txtmsg .= 'The login page: http://azure2.mitre.org/rosscloud/<br>';
	//$txtmsg .= 'OTP Type : '.$mOtp->GetUserAlgorithm().'<br>';
	//$txtmsg .= 'Time Interval Or Last Event : '.(strtolower($mOtp->GetUserAlgorithm())==='htop'?$mOtp->GetUserTokenLastEvent():$mOtp->GetUserTokenTimeInterval())."<br>";
	if($mOtp->GetUserPrefixPin()){
		$txtmsg .= 'User Pin : '.$mOtp->GetUserPin().'<br><br>';
	}
	//$txtmsg .= 'User Token Seed : <br>&nbsp;&nbsp;&nbsp;&nbsp;'.$UserTokenSeed."<br>";
	//$txtmsg .= 'Token URL : '.$mOtp->GetUserTokenUrlLink()."<br>";
	//$txtmsg .= 'Enter your seed here http://azure2.mitre.org/otp_tool/<br>';
	//$txtmsg .= 'OR scan this barcode with the authenticator app of your choice.<br><br>';
//	$txtmsg .= 'With xandroid token apps select base32 before input seed<br>';
	//$txtmsg .= '<img src="data:image/png;base64,'.base64_encode($mOtp->GetUserTokenQrCode($mOtp->GetUser(),'','binary')).'"/><br><br>';

	$txtmsg .= 'Log in to IPIT International with your one-time password http://'.getenv('SERVER_NAME').str_replace('index.php', '', getenv('SCRIPT_NAME')).'<br><br>';

	$txtmsg .= $l->t('<p>This email was sent from a notification-only address that cannot accept incoming email. Please do not reply to this message.</p></html>');
	if ($mail !== NULL) {
		try{
			$result = OC_Mail::send($mail, $uid, '['.getenv('SERVER_NAME')."] - OTP", $txtmsg, 'Mail_Notification@'.getenv('SERVER_NAME'), 'IPIT', 1 );	
			OCP\JSON::success(array("data" => array( "message" => $l->t("email sent to ".$mail) )));
		}catch(Exception $e){
			 OCP\JSON::error(array("data" => array( "message" => $l->t($e->getMessage()) )));
		}
	}else{
		//echo "Email address error<br>";
		OCP\JSON::error(array("data" => array( "message" => $l->t("Email address error : ".$mail) )));
	}
}else if (
    $_POST &&
    ($_POST["otp_action"]==="create_otp" || $_POST["otp_action"]==="replace_otp")
){
	if($mOtp->CheckUserExists($uid) && $_POST["otp_action"]==="replace_otp"){		
		if(!$mOtp->DeleteUser($uid)){
			OCP\JSON::error(array("data" => array( "message" => $l->t("error during deleting otp") )));
			return;
		}
	}
	
	if($_POST["otp_action"]==="create_otp" && $mOtp->CheckUserExists($uid)){
		OCP\JSON::error(array("data" => array( "message" => $l->t("otp already exists") )));
		return;
	}
    
    
    // format token seedll :
    if($_POST["UserTokenSeed"]===""){
		//if (OCP\Config::getAppValue('user_otp','TokenBase32Encode',true) ){
			$GA_VALID_CHAR = "ABCDEFGHIJKLMNOPQRSTUVWXYZ234567";
			$UserTokenSeed=generateRandomString(8,64,8,$GA_VALID_CHAR);
		//}
    }else{
		$UserTokenSeed=$_POST["UserTokenSeed"];
	}
  //$UserTokenSeed="234567234567AZAZ";
  //$UserTokenSeed="Hello!";
    //~ if (OCP\Config::getAppValue('user_otp','TokenBase32Encode',true)){
        //~ $UserTokenSeed=bin2hex(base32_decode($UserTokenSeed));
    //~ }//else{
		//$UserTokenSeed=bin2hex($UserTokenSeed);
    $UserTokenSeed=bin2hex(base32_decode($UserTokenSeed));
    //$UserTokenSeed=bin2hex(base32_decode($UserTokenSeed));
    //echo $UserTokenSeed." / ".base32_encode($UserTokenSeed);exit;
    //echo $UserTokenSeed." / ".hex2bin($UserTokenSeed);exit;
	//}
//echo "toto";
    $result = $mOtp->CreateUser(
        $uid,
        (OCP\Config::getAppValue('user_otp','UserPrefixPin','0')?1:0),
        OCP\Config::getAppValue('user_otp','UserAlgorithm','TOTP'),
        $UserTokenSeed,
        $_POST["UserPin"],
        OCP\Config::getAppValue('user_otp','UserTokenNumberOfDigits','6'),
        OCP\Config::getAppValue('user_otp','UserTokenTimeIntervalOrLastEvent','30')
    );//var_dump($result);
    //exit;
    if($result){
        OCP\JSON::success(array("data" => array( "message" => $l->t("OTP Changed") )));
    }else{
        OCP\JSON::error(array("data" => array( "message" => $l->t("check apps folder rights") )));
    }
}else{
    OCP\JSON::error(array("data" => array( "message" => $l->t("Invalid request") )));
}
