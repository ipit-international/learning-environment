<?php
/**
 * Copyright (c) 2012 Frank Karlitschek <frank@owncloud.org>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

/**
 * OC_Mail
 *
 * A class to handle mail sending.
 */

require_once 'class.phpmailer.php';

class OC_Mail {

	/**
	 * send an email
	 *
	 * @param string $toaddress
	 * @param string $toname
	 * @param string $subject
	 * @param string $mailtext
	 * @param string $fromaddress
	 * @param string $fromname
	 * @param bool|int $html
	 * @param string $altbody
	 * @param string $ccaddress
	 * @param string $ccname
	 * @param string $bcc
	 * @throws Exception
	 */
	public static function send($toaddress, $toname, $subject, $mailtext, $fromaddress, $fromname,
		$html=0, $altbody='', $ccaddress='', $ccname='', $bcc='') {

		$SMTPMODE = OC_Config::getValue( 'mail_smtpmode', 'sendmail' );
		$SMTPHOST = OC_Config::getValue( 'mail_smtphost', '127.0.0.1' );
		$SMTPPORT = OC_Config::getValue( 'mail_smtpport', 25 );
		$SMTPAUTH = OC_Config::getValue( 'mail_smtpauth', false );
		$SMTPAUTHTYPE = OC_Config::getValue( 'mail_smtpauthtype', 'LOGIN' );
		$SMTPUSERNAME = OC_Config::getValue( 'mail_smtpname', '' );
		$SMTPPASSWORD = OC_Config::getValue( 'mail_smtppassword', '' );
		$SMTPDEBUG    = OC_Config::getValue( 'mail_smtpdebug', false );
		$SMTPTIMEOUT  = OC_Config::getValue( 'mail_smtptimeout', 10 );
		$SMTPSECURE   = OC_Config::getValue( 'mail_smtpsecure', '' );


		$mailo = new PHPMailer(true);
		if($SMTPMODE=='sendmail') {
			$mailo->IsSendmail();
		}elseif($SMTPMODE=='smtp') {
			$mailo->IsSMTP();
		}elseif($SMTPMODE=='qmail') {
			$mailo->IsQmail();
		}else{
			$mailo->IsMail();
		}

		OCP\Util::writeLog('OC_MAIL',"SMTPMODE = $SMTPMODE", OCP\Util::DEBUG);
		$mailo->Host = $SMTPHOST;
		$mailo->Port = $SMTPPORT;
		$mailo->SMTPAuth = $SMTPAUTH;
		$mailo->SMTPDebug = $SMTPDEBUG;
		$mailo->SMTPSecure = $SMTPSECURE;
		$mailo->AuthType = $SMTPAUTHTYPE;
		$mailo->Username = $SMTPUSERNAME;
		$mailo->Password = $SMTPPASSWORD;
		$mailo->Timeout  = $SMTPTIMEOUT;

		$mailo->From = $fromaddress;
		$mailo->FromName = $fromname;;
		$mailo->Sender = $fromaddress;
		try {
			//TODO: making terrible assumptions about these arrays being similar and not empty, in theory mail notify is the only email user
			//and sends mail using users to get emails so there should be a one to one relationship 
			$arrayaddress = explode(',',$toaddress);
			$arraynames = explode(',',$toname);
			for($i = 0; $i < count($arrayaddress); $i++) {
				$mailo->AddAddress($arrayaddress[$i], $arraynames[$i]);
			}
			//option 2
		/*	$arrayaddresses = explode(',',$toaddress);
			foreach($arrayaddresses as $ $address) {
				$mailo->AddAddress($address);
			}
		*/


			if($ccaddress<>'') $mailo->AddCC($ccaddress, $ccname);
			if($bcc<>'') $mailo->AddBCC($bcc);

			$mailo->AddReplyTo($fromaddress, $fromname);

			$mailo->WordWrap = 50;
			if($html==1) $mailo->IsHTML(true); else $mailo->IsHTML(false);

			$mailo->Subject = $subject;
			if($altbody=='') {
				//$mailo->Body    = $mailtext.OC_MAIL::getfooter();
				$mailo->Body = $mailtext;
				$mailo->AltBody = '';
			}else{
				$mailo->Body    = $mailtext;
				$mailo->AltBody = $altbody;
			}
			$mailo->CharSet = 'UTF-8';
			
			//this works :)
			//$mailo->Sign("/etc/pki/tls/certs/privdovecot.pem", "/etc/pki/tls/private/dovecot.pem","");
			/*
			$userInfo = \OCP\MC_User::getUserInfo();
			$group = $userInfo["group"];
			$groupInfo = \OCP\MC_Group::getGroupInfo($group);
			OCP\Util::writeLog('mail','got here...',4);
			$publicKey = $groupInfo['publicKey'];
			//$mailo->Sign("/etc/pki/tls/certs/privdovecot.pem", "/etc/pki/tls/private/dovecot.pem","",$publicKey);
			//$mailo->Sign("/etc/pki/tls/certs/privdovecot.pem", "/etc/pki/tls/private/dovecot.pem","","/etc/pki/tls/certs/tdmaher.crt");
			$mailo->Sign("/etc/pki/tls/certs/privdovecot.pem", "/etc/pki/tls/private/dovecot.pem","","/home/tdmaher/ssl/cert.crt");
			*/
			
			/*
			$mailo->sign_cert_file="/etc/pki/tls/certs/privdovecot.pem";
			$mailo->sign_key_file="/etc/pki/tls/private/dovecot.pem";

			//we don't use a password so not sure if empty string is required or not
			$mailo->sign_key_pass="";
			*/	
			$mailo->Send();
			unset($mailo);
			OC_Log::write('mail',
				'Mail from '.$fromname.' ('.$fromaddress.')'.' to: '.$toname.'('.$toaddress.')'.' subject: '.$subject,
				OC_Log::DEBUG);
		} catch (Exception $exception) {
			OC_Log::write('mail', $exception->getMessage(), OC_Log::ERROR);
			throw($exception);
		}
	}

	/**
	 * return the footer for a mail
	 *
	 */
	public static function getfooter() {

		$defaults = new OC_Defaults();

		$txt="\n--\n";
		$txt.=$defaults->getName() . "\n";
		$txt.=$defaults->getSlogan() . "\n";

		return($txt);

	}

	/**
	 * @param string $emailAddress a given email address to be validated
	 * @return bool
	 */
	public static function ValidateAddress($emailAddress) {
		return PHPMailer::ValidateAddress($emailAddress);
	}
}
