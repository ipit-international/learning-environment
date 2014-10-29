<?php
$CONFIG = array (
  'instanceid' => 'oc0e3c5c5f9e',
  'passwordsalt' => 'af24a7f90158be67a3670262c1358c',
  'trusted_domains' => 
  array (
    0 => 'azure2.mitre.org',
  ),
  'dbtype' => 'mysql',
  'version' => '6.0.3.1',
  'dbtableprefix' => 'oc_',
  'installed' => true,
  'ruleswsdl' => 'http://beryl.mitre.org:8080/WSA/PridesRulesImpl?wsdl',
  'mail_smtpmode' => 'smtp',
  'mail_smtphost' => 'cerulean.mitre.org',
  'mail_smtpport' => 25,
  'mail_domain' => 'ipit.org',
  'mail_smtpsecure' => 'tls',
  'mail_smtpdebug' => true,
  'datadirectory' => '/var/www/html/devcloud/data',
  'dbname' => 'devcloud',
  'dbhost' => 'localhost',
  'dbuser' => 'oc_devcloudAdmin',
  'dbpassword' => 'c1bf3e09f0312317c2c229b514e0d0',

 /* Custom CSP policy, changing this will overwrite the standard policy */
  "custom_csp_policy" => "style-src 'self' 'unsafe-inline'; frame-src *; img-src *; font-src 'self' data:; media-src *",
);
