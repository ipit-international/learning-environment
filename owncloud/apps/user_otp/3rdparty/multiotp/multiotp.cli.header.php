<?php
/**
 * @file  multiotp.cli.header.php
 * @brief Command line implementation of the multiOTP PHP class.
 *
 * multiOTP PHP CLI header - Strong two-factor authentication PHP class
 * http://www.multiotp.net
 *
 * Visit http://forum.multiotp.net/ for additional support.
 *
 * Donation are always welcome! Please check http://www.multiotp.net
 * and you will find the magic button ;-)
 *
 * If the name of this file is multiotp.php, it means that it is already
 * the result of the merge of the two files multiotp.cli.header.php and
 * multiotp.class.php
 *
 * The MultiOTP PHP CLI header is simply merged with the MultiOTP PHP
 * class in order to provide an authentication command line script.
 *
 * This script can be used as an external authentication provider with at
 * least the following RADIUS servers:
 *  - TekRADIUS LT, a free Radius server for Windows with SQLite backend
 *    (http:/www.tekradius.com)
 *  - TekRADIUS, a free Radius server for Windows with MS-SQL backend
 *    (http:/www.tekradius.com)
 *  - FreeRADIUS, a free Radius server implementation for Linux
 *    and *nix environments (http://freeradius.org)
 *  - FreeRADIUS for Windows, a free Radius server implementation ported
 *    for Windows (http://sourceforge.net/projects/freeradius/)
 *
 * For Windows, you can also use the multiotp.exe file provided, which is
 * an embedded PHP interpreter together with the result of the merge.
 *
 *
 * LICENCE
 *
 *   Copyright (c) 2010-2014 SysCo systemes de communication sa
 *   SysCo (tm) is a trademark of SysCo systemes de communication sa
 *   (http://www.sysco.ch)
 *   All rights reserved.
 * 
 *   This file is part of the MultiOTP PHP class
 *
 *   MultiOTP PHP class is free software; you can redistribute it and/or
 *   modify it under the terms of the GNU Lesser General Public License as
 *   published by the Free Software Foundation, either version 3 of the License,
 *   or (at your option) any later version.
 * 
 *   MultiOTP PHP class is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU Lesser General Public License for more details.
 * 
 *   You should have received a copy of the GNU Lesser General Public
 *   License along with MultiOTP PHP class.
 *   If not, see <http://www.gnu.org/licenses/>.
 *
 *
 * PHP 5.3.0 or higher is supported.
 *
 * @author    Andre Liechti, SysCo systemes de communication sa, <info@multiotp.net>
 * @version   4.2.4.2
 * @date      2014-04-13
 * @since     2010-06-08
 * @copyright (c) 2010-2014 SysCo systemes de communication sa
 * @copyright GNU Lesser General Public License
 *
 *
 * Command line usage
 *
 *   Type multiotp -help to have the full description of the options,
 *    and have a look at the readme.txt file for enhanced explanations
 *
 *
 * Return codes
 *
 *   0 OK: Token accepted
 *  11 INFO: User successfully created or updated
 *  12 INFO: User successfully deleted
 *  13 INFO: User PIN code successfully changed
 *  14 INFO: Token has been resynchronized successfully
 *  15 INFO: Tokens definition file successfully imported
 *  16 INFO: QRcode successfully created
 *  17 INFO: UrlLink successfully created
 *  18 INFO: SMS code request received
 *  19 INFO: Requested operation successfully done
 *  21 ERROR: User doesn't exist
 *  22 ERROR: User already exists
 *  23 ERROR: Invalid algorithm
 *  24 ERROR: Token locked (too many tries)
 *  25 ERROR: Token delayed (too many tries, but still a hope in a few minutes)
 *  26 ERROR: The time based token has already been used
 *  27 ERROR: Resynchronization of the token has failed
 *  28 ERROR: Unable to write the changes in the file
 *  29 ERROR: Token doesn't exist
 *  30 ERROR: At least one parameter is missing
 *  31 ERROR: Tokens definition file doesn't exist
 *  32 ERROR: Tokens definition file not successfully imported
 *  33 ERROR: Encryption hash error, encryption key is not the same
 *  34 ERROR: Linked user doesn't exist
 *  35 ERROR: User not created
 *  39 ERROR: Requested operation aborted
 *  41 ERROR: SQL error
 *  50 ERROR: QRcode not created
 *  51 ERROR: UrlLink not created (no provisionable client for this protocol)
 *  60 ERROR: No information on where to send SMS code
 *  61 ERROR: SMS code request received, but an error occured during transmission
 *  62 ERROR: SMS provider not supported
 *  70 ERROR: Server authentication error
 *  71 ERROR: Server request is not correctly formatted
 *  72 ERROR: Server answer is not correctly formatted
 *  80 ERROR: Server cache error
 *  99 ERROR: Authentication failed (and other possible unknown errors)
 *
 *
 * Radius integration examples
 *
 *   Example 1 (FreeRADIUS 2.x under Linux or Windows, new fashion)
 *
 *     Have a look in the readme file, everything is explained.
 *
 *
 *   Example 2 (FreeRADIUS under Linux or Windows, old fashion)
 *
 *     Define a DEFAULT entry in the /etc/freeradius/users file like this:
 *     DEFAULT Auth-Type = Accept
 *     Exec-Program-Wait = "/usr/local/bin/multiotp.php %{User-Name} %{User-Password}",
 *     Fall-Through = Yes,
 *     Reply-Message = "Hello, %{User-Name}"
 *
 *
 *   Example 3 (TekRADIUS or TekRADIUS LT under Windows)
 *
 *     TekRADIUS supports a Default Username to be used when a matching user
 *     profile cannot be found for an incoming RADIUS authentication request.
 *     So a quick and easy way is to create in the TekRADIUS Manager a User
 *     named 'Default' that belongs to the existing 'Default' Group.
 *     Then add to this Default user the following attribute :
 *     Check  External-Executable  C:\multitop\multiotp.exe %ietf|1% %ietf|2%
 *
 *
 * External files created
 *
 *   Users database files in the subfolder called users
 *   Tokens database files in the subfolder called tokens
 *
 *
 * External file needed
 *
 *   Users database files in the subfolder called users
 *   Tokens database files in the subfolder called tokens
 *
 *
 * Special issues
 *
 *   If you need specific developements concerning strong authentication,
 *   do not hesistate to contact us per email at info@multiotp.net.
 *
 *
 * Users feedbacks and comments
 *
 * 2013-04-04 Stefan Kügler, SerNet GmbH (DE)
 * 2013-04-01 Daniel Särnström, Donator AB (SE)
 *   Daniel & Stefan asks some info in order to import tokens without a know format.
 *   Good question, multiOTP supports now importation of tokens from CSV file.
 *
 * 2014-04-02 Prashant Kumar, Alscient (UK)
 *   Prash, is playing with FreeRADIUS and VPN (PPTP with MPPE). This requires radius to send MPPE keys.
 *   Interesting feedback, multiOTP provides now NT_KEY, like the ntlm_auth external helper.
 *
 * 2014-03-31 Alex Tasikas (GR)
 *   Thanks Alex for your valuable feedback concerning some bugs in LDAP support.
 *
 * 2014-03-25 Prashant Kumar, Alscient (UK)
 *   As proposed by Prash, we have added the possibility to modify the list of attributes to encrypt.
 *
 * 2014-03-17 Arthur de Jong, West Consulting (NL)
 *   Arthur gave some feedbacks concerning distributing the source code in the
 *    "preferred form of the work for making modifications".
 *
 * 2014-03-14 Soeren Malchow, MCON (DE)
 *   Thanks for your feedback concerning a bug in the SQL request for the log table.
 *
 * 2014-01-27 Henk van der Helm (NL)
 *   MANY thanks for your appreciated $$$ donation.
 *
 * 2014-01-19 Erik Nylund (FI)
 *   Thanks four your feedback concerning specific parameters order in QRCode for Microsoft Authenticator
 *
 * 2014-01-14 Sylvain Maret (CH)
 *   Thanks for your feedback concerning possible zero division in the ComputeOathTruncate method.
 *    Method has been altered in order to be more compatible with almost any PHP version.
 *   Thanks also for the suggestion to resync without the prefix PIN. Both are supported now.
 *
 * 2014-01-08/09  Cheng Shao-Pin (CN)
 *   Thanks for your feedback concerning possible missing JSON extension in old PHP distribution
 *    and possible image functions incompatibilities with some PHP versions during QRcode generation.
 *   Thanks also for your appreciated $ donation.
 *
 * 2014-01-08  Cheng Shao-Pin (CN) and Daniel Särnström, Donator AB (SE)
 *   Thanks for your feedback concerning md5.js missing in the distribution.
 *
 * 2013-12-20 Rico Zeiss, Hermann Wegener GmbH & Co. KG (DE)
 *   MANY thanks for your appreciated $$$ sponsorship to support us to add MS-CHAP and MS-CHAPv2 in a next release.
 *
 * 2013-12-18 Xavier Céspedes (ES)
 *   Thanks to Xavier who noticed a problem with the hex2bin() function during the scratch password generation.
 *   In the meantime, the GetUserScratchPasswordsList() function has been improved and fixed and is in the 4.1 release.
 *
 * 2013-09-20 Sean Butler-Lee (IE)
 *   Thanks a lot for announcing a bug with the GetUserScratchPasswordsArray() method.
 *
 * 2013-08-22,26 Frank Bongrand (FR)
 *   Thanks a lot for valuable feedbacks concerning some minor bugs in 4.0.4 and 4.0.6
 *
 * 2013-08-21 Henk van der Helm (NL)
 *   Thanks a lot for a valuable feedback concerning some minor bugs in 4.0.4
 *
 * 2013-08-15 Donator AB (SE)
 *   MANY thanks for your appreciated $$$ sponsorship to support us to add self-registration in a next release.
 *
 * 2013-08-13 Daniel Särnström, Donator AB (SE)
 *   Daniel proposed to add self-registration and pskc v12 with encrypted data support (OATH compliant).
 * 
 * 2013-07-25 Dominik Pretzsch from Last Squirrel IT (DE)
 *   After some discussions with Dominik, integration of the client/server support in the basic library
 *
 * 2013-07-23 Stefan Kügler (DE) (again ;-)
 *   Stefan proposed to add the possibility to show the log, which is especially convenient for MySQL log.
 *   He proposed also to be able to call an external program to send SMS.
 *
 * 2013-07-11 Stefan Kügler (DE)
 *   Stefan proposed to add a lock and unlock option for the user.
 *
 * 2013-06-19 SerNet GmbH (DE)
 *   MANY thanks for your appreciated $$$ sponsorship after we implemented some features proposed by Stefan Kügler.
 *
 * 2013-06-13 Henk van der Helm (NL) (again ;-)
 *   Henk proposed to be able to have a specific description for the software token.
 *   (we use the already existing user description attribute)
 *
 * 2013-06-01 Stefan Irion (CH)
 *   Thanks for your appreciated $$ donation.
 *
 * 2013-05-14 Henk van der Helm (NL)
 *   Henk asked to support also the provider IntelliSMS. Thanks for the $$ sponsorship!
 *
 * 2013-05-03 Stefan Kügler (DE)
 *   Stefan proposed to lower the default max_time_window to 600 seconds.
 *
 * 2013-03-04 Alan DeKok (CA)
 *   Alan proposed in the freeradius mailing-list to put a prefix to be able to handle the
 *   debug info by the freeradius server.
 *
 * 2012-11-28  Gareth Thomas
 *   Thanks for your appreciated $$ donation.
 *
 * 2012-03-16 Nicolas Goralski (LU)
 *   Nicolas proposed an enhancement in order to support PAM. Thanks also for the $$ sponsorship!
 *     (with the -checkpam option in the command line edition)
 *
 * 2011-05-19 Fabiano Domeniconi (CH)
 *   Fabiano found old info in the samples, CheckToken() is not boolean anymore! Samples fixed.
 *
 * 2011-04-24 Steven Roddis (AU)
 *   Steven asked for more examples. Thanks to Steven for the $ donation ;-)
 *
 * 2010-09-15 Jasper Pol (NL)
 *   Jasper has added an initial MySQL backend support
 *
 * 2010-09-13 Brenno Hiemstra (NL)
 *   Brenno reported bad extra spaces after the #!/usr/bin/php in the Linux version of multiotp.php
 *
 * 2010-08-20 BirdNet, C. Christophi (CH)
 *   Documentation enhancement proposal for the TekRADIUS part, thanks !
 *
 * 2010-07-19 SysCo/al (CH)
 *   Well, as requested by some users, the new "class" design is done, enjoy !
 *
 *
 * Change Log
 *
 *   2014-04-13 4.2.4.2 SysCo/al Minor fixes
 *   2014-04-06 4.2.4.1 SysCo/al Fixed bug concerning LDAP handling
 *                               NT_KEY support added (for FreeRADIUS further handling)
 *                               Tokens CSV import (serial_number;manufacturer;algorithm;seed;digits;interval_or_event)
 *                               When a user is deleted, the token(s) attributed to this user is/are unassigned
 *                               New option -user-info added
 *   2014-03-30 4.2.4   SysCo/al Fixed bug concerning MySQL handling and mysqli support added
 *                               Enhanced SetAttributesToEncrypt function
 *                               New implementation fo some external classes
 *                               Generated QRcode are better
 *                               LOT of new QA tests, more than 60 different tests (including PHP class and command line versions)
 *                               Enhanced documentation
 *   2014-03-13 4.2.3   SysCo/al Updated examples
 *   2014-03-03 4.2.2   SysCo/al Cleaned some non-interpreted TekRADIUS variables (for old TeKRADIUS releases)
 *                               Some values can now go back to TekRADIUS
 *   2014-02-07 4.2.0   SysCo/al MS-CHAP and MS-CHAPv2 fully supported
 *   2014-01-21 4.1.2   SysCo/al Direct call of class methods using -call-method
 *   2014-01-20 4.1.1   SysCo/al Minor fixes
 *   2013-12-23 4.1.0   SysCo/al Some modifications in order to correctly handle the class methods
 *                               It is now possible to activate or desactivate a user
 *                               Encrypted pskc files are now supported
 *   2013-08-30 4.0.7   SysCo/al GetScriptFolder() was still buggy sometimes, thanks Frank for the feedback
 *                               File mode of the created QRcode file is also changed base on GetLinuxFileMode()
 *   2013-08-25 4.0.6   SysCo/al base32_encode() is now RFC compliant with uppercases
 *                               GetUserTokenQrCode() and GetTokenQrCode() where buggy
 *                               GetScriptFolder() use now __FILE__ if the full path is included
 *                               When doing a check in the CLI header, @... is automatically removed from the
 *                                username if the user doesn't exist, and the check is done on the clean name
 *                               Added a lot of tests to enhance release quality
 *   2013-08-21 4.0.5   SysCo/al Fixed the check of the cache lifetime
 *                               Added a temporary server blacklist during the same instances
 *                               Default server timeout is now set to 1 second
 *   2013-08-20 4.0.4   SysCo/al Added an optional group attribute for the user
 *                                (which will be send with the Radius Filter-Id option)
 *                               Added scratch passwords generation (if the token is lost)
 *                               Automatic database schema upgrade using method UpgradeSchemaIfNeeded()
 *                               Added client/server support with local cache
 *                               Added CHAP authentication support (PAP is of course still supported)
 *                               The encryption key is now a parameter of the class constructor
 *                               The method SetEncryptionKey('MyPersonalEncryptionKey') IS DEPRECATED
 *                               The method DefineMySqlConnection IS DEPRECATED
 *                               Full MySQL support, including tables creation (see example and SetSqlXXXX methods)
 *                               Added email, sms and seed_password to users attributes
 *                               Added sms support (aspsms, clickatell, intellisms, exec)
 *                               Added prefix support for debug mode (in order to send Reply-Message := to Radius)
 *                               Added a lot of new methods to handle easier the users and the tokens
 *                               General speedup by using available native functions for hash_hmac and others
 *                               Default max_time_window has been lowered to 600 seconds (thanks Stefan for suggestion)
 *                               Integrated Google Authenticator support with integrated base 32 seed handling
 *                               Integrated QRcode generator library (from Y. Swetake)
 *                               General options in an external configuration file
 *                               Comments have been reformatted and enhanced for automatic documentation
 *                               Development process enhanced, source code reorganized, external contributions are
 *                                added automatically at the end of the library after an internal build release
 *   2011-10-25 3.9.2   SysCo/al Improved get_script_dir() for Linux/Windows compatibility
 *   2011-09-15 3.9.1   SysCo/al Some quick fixes concerning multiple users
 *   2011-09-13 3.9.0   SysCo/al Adding support for account with multiple users
 *   2011-07-06 3.2.0   SysCo/al Encryption hash handling with additional error message 33
 *                                (if the key has changed)
 *                               Adding more examples
 *                               Adding generic user with multiple account
 *                                (Real account name is combined: "user" and "account password")
 *                               Adding log options, now default doesn't log token value anymore
 *                               Debugging MySQL backend support for the token handling
 *                               Fixed automatic detection of \ or / for script path detection
 *   2010-12-19 3.1.1   SysCo/al Better MySQL backend support, including in CLI version
 *   2010-09-15 3.1.0   SysCo/al Removed bad extra spaces in the multiotp.php file for Linux
 *                               MySQL backend support
 *   2010-09-02 3.0.0   SysCo/al Adding tokens handling support, including importing XML tokens definition file
 *                                (http://tools.ietf.org/html/draft-hoyer-keyprov-pskc-algorithm-profiles-00)
 *                               Enhanced flat database file format (multiotp is still compatible with old formats)
 *                               Internal method SetDataReadFlag renamed to SetUserDataReadFlag
 *                               Internal method GetDataReadFlag renamed to GetUserDataReadFlag
 *   2010-08-21 2.0.4   SysCo/al Enhancement in order to use an alternate php "compiler" for Windows command line
 *                               Documentation enhancement
 *   2010-08-18 2.0.3   SysCo/al Minor notice fix, define timezone if not defined (for embedded command line)
 *                               If user doesn't exist, do not create the related flat file after a check
 *   2010-07-21 2.0.2   SysCo/al Fix to create correctly the folders "users" and "log" if needed
 *   2010-07-19 2.0.1   SysCo/al Adding more information in the help text
 *   2010-07-19 2.0.0   SysCo/al New design using a class and a cli header stub
 *   2010-06-15 1.1.5   SysCo/al Adding OATH/TOTP support
 *   2010-06-15 1.1.4   SysCo/al Project renamed to multiotp to avoid overlapping
 *   2010-06-08 1.1.3   SysCo/al Typo in script folder detection
 *   2010-06-08 1.1.2   SysCo/al Typo in variable name
 *   2010-06-08 1.1.1   SysCo/al Status bar during resynchronization
 *   2010-06-08 1.1.0   SysCo/al Fix in the example, distribution not compressed
 *   2010-06-07 1.0.0   SysCo/al Initial implementation
 *
 *********************************************************************/

require_once('multiotp.class.php');

// Trick to define the current folder as the script folder
function get_script_dir()
{
    // Detect the current folder, change Windows notation to universal notation if needed
    $current_folder = convert_to_unix_path(getcwd());
    $current_script_folder = convert_to_unix_path($_SERVER["argv"][0]);
    if ('' == (trim($current_script_folder)))
    {
        $current_script_folder = $_SERVER['SCRIPT_FILENAME'];
    }
    
    if (FALSE === strpos($current_script_folder,"/"))
    {
        $current_script_folder_detected = dirname($current_folder."/fake.file");
    }
    else
    {
        $current_script_folder_detected = dirname($current_script_folder);
    }

    if (substr($current_script_folder_detected,-1) != "/")
    {
        $current_script_folder_detected.="/";
    }
    return convert_to_windows_path_if_needed($current_script_folder_detected);
}


// Function to convert into a unix path notation
function convert_to_unix_path($path)
{
    return str_replace("\\","/",$path);
}


// Function to convert into a windows path notation if needed
function convert_to_windows_path_if_needed($path)
{
    $result = $path;
    if (FALSE !== strpos($result,":"))
    {
        $result = str_replace("/","\\",$result);
    }
    return $result;
}

$folder_path = get_script_dir();
$result = chdir($folder_path);

            
// Trick to have mostly the correct timezone in embedded command line version
// and to avoid error messages when using time functions
if (function_exists("date_default_timezone_get"))
{
    $actual_timezone = @date_default_timezone_get();
    if (function_exists("date_default_timezone_set"))
    {
        @date_default_timezone_set($actual_timezone);
    }
}


// Clean quotes of the parameters if any
if (!function_exists('clean_quotes'))
{
    function clean_quotes($value)
    {
        $var = $value;
        if ((('"' == substr($var,0,1)) || ("'" == substr($var,0,1))) && (('"' == substr($var,-1)) || ("'" == substr($var,-1))))
        {
            $var = substr($var, 1, strlen($var)-2);
        }
        return $var;
    }
}


// Be sure that STDIN, STDOUT and STDERR are defined correctly for command line edition
if (!defined('STDIN'))
{
    define(STDIN, fopen('php://stdin', 'r'));
}
if (!defined('STDOUT'))
{
    define(STDOUT, fopen('php://stdout', 'w'));
}
if (!defined('STDERR'))
{
    define(STDERR, fopen('php://stderr', 'w'));
}


// Initialize some variables
$command            = 'check';
$call_method        = '';
$display_help       = FALSE;
$display_status     = FALSE;
$prefix_pin         = FALSE;
$crlf               = "\n"; // was chr(13).chr(10);
$result             = 99; // Unknown error
$token_id_creation  = FALSE;
$mysql_backend      = FALSE;
$mysql_parameters   = array();
$param_info_debug   = FALSE;
$show_false_pin     = FALSE;
$base_dir           = '';
$source_tag         = '';
$source_ip          = '';
$source_mac         = '';
$calling_ip         = '';
$calling_mac        = '';
$chap_id            = '';
$chap_challenge     = '';
$chap_password      = '';
$ms_chap_challenge  = '';
$ms_chap_response   = '';
$ms_chap2_response  = '';
$verbose_prefix     = '';
$display_log        = FALSE;
$enable_log         = FALSE;
$verbose_log        = FALSE;
$initialize_backend = FALSE;
$keep_local         = FALSE;
$encrypted_password = FALSE;
$request_nt_key     = FALSE;

// Extract all parameters
$param_count = 0;
$all_args = array();

for ($arg_loop=1; $arg_loop < $_SERVER["argc"]; $arg_loop++)
{
    $current_arg = clean_quotes($_SERVER["argv"][$arg_loop]);

    if ("-base-dir=" == substr(strtolower($current_arg),0,10))
    {
        $base_array = explode("=",$current_arg,2);
        if (2 == count($base_array))
        {
            $base_dir = $base_array[1];
        }
    }
    elseif ("-src=" == substr(strtolower($current_arg),0,5))
    {
        $src_array = explode("=",$current_arg,2);
        if (2 == count($src_array))
        {
            $source_ip = $src_array[1];
        }
    }
    elseif ("-tag=" == substr(strtolower($current_arg),0,5))
    {
        $src_array = explode("=",$current_arg,2);
        if (2 == count($src_array))
        {
            $source_tag = $src_array[1];
        }
    }
    elseif ("-mac=" == substr(strtolower($current_arg),0,5))
    {
        $src_array = explode("=",$current_arg,2);
        if (2 == count($src_array))
        {
            $source_mac = $src_array[1];
        }
    }
    elseif ("-calling-ip=" == substr(strtolower($current_arg),0,12))
    {
        $src_array = explode("=",$current_arg,2);
        if (2 == count($src_array))
        {
            $calling_ip = $src_array[1];
        }
    }
    elseif ("-calling-mac=" == substr(strtolower($current_arg),0,13))
    {
        $src_array = explode("=",$current_arg,2);
        if (2 == count($src_array))
        {
            $calling_mac = $src_array[1];
        }
    }
    elseif ("-chap-id=" == substr(strtolower($current_arg),0,16))
    {
        $src_array = explode("=",$current_arg,2);
        if (2 == count($src_array))
        {
            $chap_id = $src_array[1];
            if (("%msoft" == strtolower(substr($chap_id,0,6))) || ("%ietf" == strtolower(substr($chap_id,0,5))))
            {
                $chap_id = '';
            }
        }
    }
    elseif ("-chap-challenge=" == substr(strtolower($current_arg),0,16))
    {
        $src_array = explode("=",$current_arg,2);
        if (2 == count($src_array))
        {
            $chap_challenge = $src_array[1];
            if (("%msoft" == strtolower(substr($chap_challenge,0,6))) || ("%ietf" == strtolower(substr($chap_challenge,0,5))))
            {
                $chap_challenge = '';
            }
        }
    }
    elseif ("-chap-password=" == substr(strtolower($current_arg),0,15))
    {
        $src_array = explode("=",$current_arg,2);
        if (2 == count($src_array))
        {
            $chap_password = $src_array[1];
            if (("%msoft" == strtolower(substr($chap_password,0,6))) || ("%ietf" == strtolower(substr($chap_password,0,5))))
            {
                $chap_password = '';
            }
            else
            {
                $encrypted_password = TRUE;
            }
        }
    }
    
    elseif ("-ms-chap-challenge=" == substr(strtolower($current_arg),0,19))
    {
        $src_array = explode("=",$current_arg,2);
        if (2 == count($src_array))
        {
            $ms_chap_challenge = $src_array[1];
            if (("%msoft" == strtolower(substr($ms_chap_challenge,0,6))) || ("%ietf" == strtolower(substr($ms_chap_challenge,0,5))))
            {
                $ms_chap_challenge = '';
            }
        }
    }
    elseif ("-ms-chap-response=" == substr(strtolower($current_arg),0,18))
    {
        $src_array = explode("=",$current_arg,2);
        if (2 == count($src_array))
        {
            $ms_chap_response = $src_array[1];
            if (("%msoft" == strtolower(substr($ms_chap_response,0,6))) || ("%ietf" == strtolower(substr($ms_chap_response,0,5))))
            {
                $ms_chap_response = '';
            }
            else
            {
                $encrypted_password = TRUE;
            }
        }
    }
    elseif ("-ms-chap2-response=" == substr(strtolower($current_arg),0,19))
    {
        $src_array = explode("=",$current_arg,2);
        if (2 == count($src_array))
        {
            $ms_chap2_response = $src_array[1];
            if (("%msoft" == strtolower(substr($ms_chap2_response,0,6))) || ("%ietf" == strtolower(substr($ms_chap2_response,0,5))))
            {
                $ms_chap2_response = '';
            }
            else
            {
                $encrypted_password = TRUE;
            }
        }
    }
    elseif ("-call-method=" == substr(strtolower($current_arg),0,13))
    {
        $src_array = explode("=",$current_arg,2);
        if (2 == count($src_array))
        {
            $command = "call-method";
            $call_method = $src_array[1];
        }
    }
    elseif ("-activate" == strtolower($current_arg))
    {
        $command = "activate";
    }
    elseif ("-desactivate" == strtolower($current_arg))
    {
        $command = "desactivate";
    }
    elseif ("-backup-config" == strtolower($current_arg))
    {
        $command = "backup-config";
    }
    elseif ("-check" == strtolower($current_arg))
    {
        $command = "check";
    }
    elseif ("-checkpam" == strtolower($current_arg))
    {
        $command = "checkpam";
    }
    elseif ("-create" == strtolower($current_arg))
    {
        $command = "create";
    }
    elseif ("-fastcreate" == strtolower($current_arg))
    {
        $command = "fastcreate";
    }
    elseif ("-createga" == strtolower($current_arg))
    {
        $command = "createga";
    }
    elseif ("-qrcode" == strtolower($current_arg))
    {
        $command = "qrcode";
    }
    elseif ("-requiresms" == strtolower($current_arg))
    {
        $command = "requiresms";
    }
    elseif ("-urllink" == strtolower($current_arg))
    {
        $command = "urllink";
    }
    elseif ("-userslist" == strtolower($current_arg))
    {
        $command = "userslist";
    }
    elseif ("-ldap-users-list" == strtolower($current_arg))
    {
        $command = "ldap-users-list";
    }
    elseif ("-ldap-users-sync" == strtolower($current_arg))
    {
        $command = "ldap-users-sync";
    }
    elseif ("-ldap-user-info" == strtolower($current_arg))
    {
        $command = "ldap-user-info";
    }
    elseif ("-ldap-check" == strtolower($current_arg))
    {
        $command = "ldap-check";
    }
    elseif ("-scratchlist" == strtolower($current_arg))
    {
        $command = "scratchlist";
    }
    elseif ("-tokenslist" == strtolower($current_arg))
    {
        $command = "tokenslist";
    }
    elseif ("-showlog" == strtolower($current_arg))
    {
        $command = "showlog";
    }
    elseif ("-debug" == strtolower($current_arg))
    {
        $verbose_log = TRUE;
    }
    elseif ("-display-log" == strtolower($current_arg))
    {
        $display_log = TRUE;
    }
    elseif ("-delete" == strtolower($current_arg))
    {
        $command = "delete";
    }
    elseif ("-phpinfo" == strtolower($current_arg))
    {
        $command = "phpinfo";
    }
    elseif ("-custominfo" == strtolower($current_arg))
    {
        $command = "custominfo";
    }
    elseif ("-libhash" == strtolower($current_arg))
    {
        $command = "libhash";
    }
    elseif ("-help" == strtolower($current_arg))
    {
        $command = "help";
    }
    elseif ("-import" == strtolower($current_arg))
    {
        $command = "import";
    }
    elseif ("-import-csv" == strtolower($current_arg))
    {
        $command = "import-csv";
    }
    elseif ("-import-pskc" == strtolower($current_arg))
    {
        $command = "import-pskc";
    }
    elseif ("-import-xml" == strtolower($current_arg))
    {
        $command = "import-xml";
    }
    elseif ("-import-alpine-xml" == strtolower($current_arg))
    {
        $command = "import-alpine-xml";
    }
    elseif ("-import-dat" == strtolower($current_arg))
    {
        $command = "import-dat";
    }
    elseif ("-import-sql" == strtolower($current_arg))
    {
        $command = "import-sql";
    }
    elseif ("-initialize-backend" == strtolower($current_arg))
    {
        $initialize_backend = true;
    }
    elseif ("-keep-local" == strtolower($current_arg))
    {
        $keep_local = TRUE;
    }
    elseif ("-lock" == strtolower($current_arg))
    {
        $command = "lock";
    }
    elseif ("-log" == strtolower($current_arg))
    {
        $enable_log = TRUE;
    }
    elseif ("-no-prefix-pin" == strtolower($current_arg))
    {
        $set_prefix_pin = FALSE;
    }
    elseif ("-prefix-pin" == strtolower($current_arg))
    {
        $set_prefix_pin = TRUE;
    }
    elseif ("-resync" == strtolower($current_arg))
    {
        $command = "resync";
    }
    elseif ("-seed-info" == strtolower($current_arg))
    {
        $command = "seed";
    }
    elseif ("-show-false-pin" == strtolower($current_arg))
    {
        $show_false_pin = TRUE;
    }
    elseif ("-status" == strtolower($current_arg))
    {
        $display_status = TRUE;
    }
    elseif ("-token-id" == strtolower($current_arg))
    {
        $token_id_creation = TRUE;
    }
    elseif ("-unlock" == strtolower($current_arg))
    {
        $command = "unlock";
    }
    elseif ("-update" == strtolower($current_arg))
    {
        $command = "update";
    }
    elseif ("-update-pin" == strtolower($current_arg))
    {
        $command = "update-pin";
    }
    elseif ("-user-info" == strtolower($current_arg))
    {
        $command = "user-info";
    }
    elseif ("-set" == strtolower($current_arg))
    {
        $command = "set";
    }
    elseif ("-config" == strtolower($current_arg))
    {
        $command = "config";
    }
    elseif (("-request-nt-key" == strtolower($current_arg)) ||
            ("--request-nt-key" == strtolower($current_arg)) // Typo for ntlm_auth users ;-)
           )
    {
        $request_nt_key = TRUE;
    }
    elseif (("-version" == strtolower($current_arg)) || ("-v" == strtolower($current_arg)))
    {
        $command = "version";
    }
    elseif ("-version-only" == strtolower($current_arg))
    {
        $command = "version-only";
    }
    elseif ("-php-version" == strtolower($current_arg))
    {
        $command = "php-version";
    }
    elseif ("-param" == strtolower($current_arg))
    {
        $param_info_debug = TRUE;
    }
    elseif ("-mysql" == strtolower($current_arg))
    {
        $mysql_backend = TRUE;
        $arg_loop++;
        if ($arg_loop < $_SERVER["argc"])
        {
            $mysql_parameters = explode(",",strtolower($current_arg));
        }
    }
    else
    {
        $param_count++;
        $all_args[$param_count] = $current_arg;
    }
}


// Be sure that inexistant parameters are empty
for ($i = ($param_count+1); $i <= 100; $i++)
{
    $all_args[$i] = "";
}


// if not enough parameters, display help
if (($param_count < 1) &&
    ($command != "backup-config") &&
    ($command != "call-method") &&
    ($command != "checkpam") &&
    ($command != "custominfo") &&
    ($command != "ldap-check") &&
    ($command != "ldap-users-list") &&
    ($command != "ldap-users-sync") &&
    ($command != "libhash") &&
    ($command != "phpinfo") &&
    ($command != "showlog") &&
    ($command != "tokenslist")&&
    ($command != "userslist") &&
    ($command != "version") &&
    ($command != "php-version") &&
    ($command != "version-only"))
{
    $command = "help";
}


if ('' != $base_dir)
{
    $folder_path = $base_dir;
    $result = chdir($folder_path);
}


// Create a new Multiotp object
// The log and users subfolders are set by default under the folder of the script
// We set directly a specific encryption key for the config, tokens and users files
// PLEASE DO NOT CHANGE THIS LINE IF YOU DON'T KNOW WHAT YOU DO!
// IF YOU CHANGE THE ENCRYPTION KEY, YOUR PREVIOUS ENCRYPTED DATA WILL NOT BE READABLE ANYMORE


if (($command == "libhash") || ($command == "help"))
{
    if (!isset($multiotp))
    {
        $multiotp = new Multiotp('DefaultCliEncryptionKey', FALSE, $folder_path);
    }
}
else
{
    if (!isset($multiotp))
    {
        $multiotp = new Multiotp('DefaultCliEncryptionKey', $initialize_backend, $folder_path);
    }
    $multiotp->UpgradeSchemaIfNeeded();
    $verbose_prefix = $multiotp->GetVerboseLogPrefix(); // for example Reply-Message := 
}


// Initialize multiOTP options
if ($enable_log)
{
    $multiotp->EnableLog();
}
if ($verbose_log)
{
    $multiotp->EnableVerboseLog();
}
if ($display_log)
{
    $multiotp->EnableDisplayLog();
}
if ($keep_local)
{
    $multiotp->EnableKeepLocal();
}


$prefix_pin = $multiotp->IsDefaultRequestPrefixPin();
if (isset($set_prefix_pin))
{
    $prefix_pin = $set_prefix_pin;
}


$multiotp->SetSourceTag($source_tag);
$multiotp->SetSourceIp($source_ip);
$multiotp->SetSourceMac($source_mac);
$multiotp->SetCallingIp($calling_ip);
$multiotp->SetCallingMac($calling_mac);
$multiotp->SetChapId($chap_id);
$multiotp->SetChapChallenge($chap_challenge);
$multiotp->SetChapPassword($chap_password);
$multiotp->SetMsChapChallenge($ms_chap_challenge);
$multiotp->SetMsChapResponse($ms_chap_response);
$multiotp->SetMsChap2Response($ms_chap2_response);


// Check if enough parameters for the MySQL backend
if ($mysql_backend)
{
    if (count($mysql_parameters) < 4)
    {
        $result = 41; // ERROR: SQL error
        $command = "error";
    }
    else
    {
        $mysql_parameters = array_pad($mysql_parameters, 7, NULL);
        $multiotp->DefineMySqlConnection($mysql_parameters[0], $mysql_parameters[1], $mysql_parameters[2], $mysql_parameters[3], $mysql_parameters[4], $mysql_parameters[5], $mysql_parameters[6]);
    }
}    


switch ($command)
{
    case "version":
        echo $multiotp->GetClassName()." ".$multiotp->GetVersion()." (".$multiotp->GetDate().")".$crlf;
        $result = 19;
        break;
    case "version-only":
        echo $multiotp->GetVersion();
        $result = 19;
        break;
    case "php-version":
        echo 'PHP '.phpversion().$crlf;
        $result = 19;
        break;
    case "backup-config":
        $result = (($multiotp->BackupConfiguration())?19:99);
        break;
    case "call-method";
        if (method_exists($multiotp,$call_method))
        {
            $call_result = $multiotp->$call_method();
            if ($multiotp->GetVerboseFlag())
            {
                $multiotp->WriteLog('Debug: Method '.$call_method.' returned the following result: '.print_r($call_result, TRUE), FALSE, FALSE, 19, 'Debug', '');
            }
            $result = 19;
        }
        else
        {
            $result = 99;
        }
        break;
    case "check";
        $self_registration = '';
        $otp_inline = '';
        if  ($param_count > 1)
        {
            if (!$multiotp->CheckUserExists($all_args[1]))
            {
                if (FALSE !== strpos($all_args[1], ':'))
                {
                    /*************************************************************************
                     * Here we check special cases
                     *
                     * 1) serial_number:username (for alternate self-registration process)
                     *    Do not forget to activate self-registration !
                     *
                     * 2) username:OTP (for alternate authentication with OTP and AD password)
                     *    not implemented yet
                     *
                     *************************************************************************/
                    $part1 = substr($all_args[1], 0, strpos($all_args[1], ':'));
                    $part2 = substr($all_args[1], strpos($all_args[1], ':')+1);
                    if ($multiotp->IsSelfRegistrationEnabled() && ($multiotp->CheckTokenExists($part1)))
                    {
                        $self_registration = $part1;
                        $all_args[1] = $part2;
                    }
                    elseif ($multiotp->IsUserRequestLdapPasswordEnabled() && ($multiotp->CheckUserExists($part1)))
                    {
                        $all_args[1] = $part1;
                        $otp_inline = $part2;
                    }
                }
                if (FALSE !== strpos($all_args[1], '@'))
                {
                    $cleaned_user = substr($all_args[1], 0, strpos($all_args[1], '@'));
                    if ($multiotp->CheckUserExists($cleaned_user))
                    {
                        $all_args[1] = $cleaned_user;
                        $multiotp->SetUser($all_args[1]);
                    }
                }
                elseif (FALSE !== strpos($all_args[1], "\\"))
                {
                    $cleaned_user = substr($all_args[1], strpos($all_args[1], "\\")+1);
                    if ($multiotp->CheckUserExists($cleaned_user))
                    {
                        $all_args[1] = $cleaned_user;
                        $multiotp->SetUser($all_args[1]);
                    }
                }
                else
                {
                    $clean_phone = $multiotp->CleanPhoneNumber($all_args[1]);
                    if ($multiotp->CheckUserExists($clean_phone))
                    {
                        $all_args[1] = $clean_phone;
                        $multiotp->SetUser($all_args[1]);
                    }
                }
            }
            # check extension can be added here
        }
        if  (($param_count < 2) && (!$encrypted_password))
        {
            $result = 30; // ERROR: At least one parameter is missing
        }
        elseif (!$multiotp->ReadUserData($all_args[1]))
        {
            if ("ERROR" == $multiotp->GetUserEncryptionHash())
            {
                $result = 33; // ERROR: Encryption hash error, encryption key is not the same
            }
            else
            {
                $result = 21; // ERROR: user doesn't exist.
            }
        }
        else
        {
            $sync_info = '';
            if ($multiotp->IsAutoResync())
            {
                if (strlen($all_args[2]) == strlen($all_args[3]))
                {
                    $sync_info = $all_args[3];
                }
                elseif ((FALSE !== strpos($all_args[2], ' ')) && ((strlen($all_args[2]) % 2) > 0))
                {
                    $space_pos = strpos($all_args[2], ' ');
                    if ($space_pos == ((strlen($all_args[2]) -1) / 2))
                    {
                        $sync_info = substr($all_args[2],$space_pos);
                        $all_args[2] = substr($all_args[2],0, $space_pos);
                    }
                }
            }
            elseif ("" != $all_args[3])
            {
                $all_args[2] = $all_args[2]." ".$all_args[3];
            }
            $result = $multiotp->CheckToken($all_args[2], $sync_info, FALSE, FALSE, FALSE, FALSE, $self_registration); // Result provided by the MultiOTP class
            if (($multiotp->IsAutoResync()) && (14 == $result))
            {
                $result = 0;
            }
        }
        break;
    case "checkpam":
        if (!$multiotp->ReadUserData(isset($_ENV["PAM_USER"])?$_ENV["PAM_USER"]:'PAM_USER_NOT_DEFINED!'))
        {
            if ("ERROR" == $multiotp->GetUserEncryptionHash())
            {
                $result = 33; // ERROR: Encryption hash error, encryption key is not the same
            }
            else
            {
                $result = 21; // ERROR: user doesn't exist.
            }
        }
        else
        {
            $result = $multiotp->CheckToken(isset($_ENV["PAM_AUTHTOK"])?$_ENV["PAM_AUTHTOK"]:'PAM_AUTHTOK_NOT_DEFINED!');
        }
        break;
    case "create":
    case "update":
        if (("create" == $command) && $multiotp->ReadUserData($all_args[1], TRUE, TRUE))
        {
            $result = 22; // ERROR: user already exists.
        }
        elseif (("update" == $command) && (!$multiotp->ReadUserData($all_args[1], FALSE, TRUE)))
        {
            $result = 21; // ERROR: user doesn't exist.
        }
        elseif  ($param_count < 3)
        {
            $result = 30; // ERROR: At least one parameter is missing
        }
        else
        {
            $multiotp->SetUser($all_args[1]);
            $multiotp->SetUserPrefixPin($prefix_pin?1:0);
            
            if ($token_id_creation)
            {
                $key_id = $all_args[2];
                if (!$multiotp->ReadTokenData($key_id))
                {
                    $result = 29; // ERROR: token doesn't exist.
                }
                else
                {
                    $multiotp->SetUserKeyId($key_id);
                    if (!$multiotp->SetUserAlgorithm($multiotp->GetTokenAlgorithm()))
                    {
                        $result = 23; // ERROR: invalid algorithm
                    }
                    else
                    {
                        $multiotp->SetUserTokenSeed($multiotp->GetTokenSeed());
                        $multiotp->SetUserTokenNumberOfDigits($multiotp->GetTokenNumberOfDigits());
                        $multiotp->SetUserTokenTimeInterval($multiotp->GetTokenTimeInterval());
                        $multiotp->SetUserTokenLastEvent($multiotp->GetTokenLastEvent());
                        $multiotp->SetUserTokenAlgoSuite($multiotp->GetTokenAlgoSuite());
                        
                        $multiotp->SetUserPin($all_args[3]);
                        
                        if ($multiotp->WriteUserData())
                        {
                            $result = 11; // INFO: user successfully created or updated
                        }
                        else
                        {
                            $result = 28; // ERROR: Unable to write the changes in the file
                        }
                    }
                }
            }
            elseif (!$multiotp->SetUserAlgorithm($all_args[2]))
            {
                $result = 23; // ERROR: invalid algorithm
            }
            else
            {
                $multiotp->SetUserTokenSeed($all_args[3]);
                
                if  ($param_count < 4)
                {
                    $result = 30; // ERROR: At least one parameter is missing
                }
                else
                {
                    $multiotp->SetUserPin($all_args[4]);
                    if ("" == $all_args[5])
                    {
                        $all_args[5] = 6; // Default numnber of digits is set to 6
                    }
                    $multiotp->SetUserTokenNumberOfDigits($all_args[5]);
                    switch (strtoupper($all_args[2]))
                    {
                        // This is the time interval for mOTP
                        case "MOTP":
                            if ("" == $all_args[6])
                            {
                                $all_args[6] = 10; // Default windows value interval for mOTP
                            }
                            $multiotp->SetUserTokenTimeInterval($all_args[6]);
                            break;
                        // This is the time interval for TOTP
                        case "TOTP":
                            if ("" == $all_args[6])
                            {
                                $all_args[6] = 30; // Default windows value interval for TOTP
                            }
                            $multiotp->SetUserTokenTimeInterval($all_args[6]);
                            break;
                        // This is the next event for HOTP
                        case "HOTP":
                        default:
                            if ("" == $all_args[6])
                            {
                                $all_args[6] = 0; // Default next event
                            }
                            $multiotp->SetUserTokenLastEvent($all_args[6]-1);
                            // -1 because we are saving the last event in the user file database
                            break;
                    }
                    if ($multiotp->WriteUserData())
                    {
                        $result = 11; // INFO: user successfully created or updated
                    }
                    else
                    {
                        $result = 28; // ERROR: Unable to write the changes in the file
                    }
                }
            }
        }
        break;
    case "delete":
        if (!$multiotp->DeleteUser($all_args[1]))
        {
            $result = 21; // ERROR: user doesn't exist.
        }
        else
        {
            $result = 12; // INFO: user successfully deleted.
        }
        break;
    case "lock":
        if (!$multiotp->LockUser($all_args[1]))
        {
            $result = 21; // ERROR: user doesn't exist.
        }
        else
        {
            $result = 19; // INFO: user successfully locked.
        }
        break;
    case "unlock":
        if (!$multiotp->UnlockUser($all_args[1]))
        {
            $result = 21; // ERROR: user doesn't exist.
        }
        else
        {
            $result = 19; // INFO: user successfully unlocked.
        }
        break;
    case "activate":
        if (!$multiotp->SetUserActivated($all_args[1],1))
        {
            $result = 21; // ERROR: user doesn't exist.
        }
        else
        {
            $result = 19; // INFO: user successfully activated.
        }
        break;
    case "desactivate":
        if (!$multiotp->SetUserActivated($all_args[1],0))
        {
            $result = 21; // ERROR: user doesn't exist.
        }
        else
        {
            $result = 19; // INFO: user successfully desactivated.
        }
        break;
    case "requiresms":
        if (!$multiotp->CheckUserExists($all_args[1]))
        {
            $result = 21; // ERROR: user doesn't exist.
        }
        else
        {
            $result = $multiotp->GenerateSmsToken($all_args[1]);
        }
        break;
    case "resync":
        if  ($param_count < 3)
        {
            $result = 30; // ERROR: At least one parameter is missing
        }
        elseif (!$multiotp->ReadUserData($all_args[1]))
        {
            $result = 21; // ERROR: user doesn't exist.
        }
        else
        {
            $result = $multiotp->CheckToken($all_args[2], $all_args[3], $display_status); // Result provided by the MultiOTP class
        }
        break;
    case "seed":
        if  ($param_count < 3)
        {
            $result = 30; // ERROR: At least one parameter is missing
        }
        elseif (!$multiotp->ReadUserData($all_args[1]))
        {
            $result = 21; // ERROR: user doesn't exist.
        }
        else
        {
            $result1 = $multiotp->CheckToken($all_args[2]);
            $result2 = $multiotp->CheckToken($all_args[3]);
            if ($result1 && $result2)
            {
                $result = 19;
            }
            else
            {
                $result = 99;
            }
        }
        break;
    case "update-pin":
        if  ($param_count < 2)
        {
            $result = 30; // ERROR: At least one parameter is missing
        }
        elseif (!$multiotp->ReadUserData($all_args[1]))
        {
            $result = 21; // ERROR: user doesn't exist.
        }
        else
        {
            $multiotp->SetUserPin($all_args[2]);
            if ($multiotp->WriteUserData())
            {
                $result = 13; // INFO: pin successfully changed
            }
        }
        break;
    case "user-info":
        $multiotp->SetUser($all_args[1]);
        echo " Information for user: ".$all_args[1].$crlf;
        echo "               Locked: ".((1 == $multiotp->GetUserLocked())?'yes':'no').$crlf;
        echo "            Activated: ".((1 == $multiotp->GetUserActivated())?'yes':'no').$crlf;
        echo " AD/LDAP synchronized: ".((1 == $multiotp->GetUserSynchronized())?'yes':'no').$crlf;
        echo "    Prefix pin needed: ".((1 == $multiotp->GetUserPrefixPin())?'yes':'no').$crlf;
        echo "          Description: ".$multiotp->GetUserDescription().$crlf;
        echo "                Email: ".$multiotp->GetUserEmail().$crlf;
        echo "         Mobile phone: ".$multiotp->GetUserSms().$crlf;
        echo "                Group: ".$multiotp->GetUserGroup().$crlf;
        echo "             Token id: ".$multiotp->GetUserTokenSerialNumber().$crlf;
        echo "            Algorithm: ".$multiotp->GetUserAlgorithm().$crlf;
        echo "           OTP digits: ".$multiotp->GetUserTokenNumberOfDigits().$crlf;
        if ('hotp' == $multiotp->GetUserAlgorithm())
        {
            echo "  Next token position: ".($multiotp->GetUserTokenLastEvent()+1).$crlf;
        }
        else
        {
            echo "       Token timestep: ".$multiotp->GetUserTokenTimeInterval().$crlf;
        }
        $result = 19;
        break;
    case "set":
        $write_user_data = FALSE;
        if  ($param_count < 2)
        {
            $result = 30; // ERROR: At least one parameter is missing
        }
        elseif (!$multiotp->ReadUserData($all_args[1]))
        {
            $result = 21; // ERROR: user doesn't exist.
        }
        else
        {
            for ($params = 2; $params < count($all_args); $params++)
            {
                $actual_array = explode("=",$all_args[$params],2);
                if (2 == count($actual_array))
                {
                    switch ($actual_array[0])
                    {
                        case 'description':
                            $multiotp->SetUserDescription($actual_array[1]);
                            $write_user_data = TRUE;
                            break;
                        case 'email':
                            $multiotp->SetUserEmail($actual_array[1]);
                            $write_user_data = TRUE;
                            break;
                        case 'pin':
                            $multiotp->SetUserPin($actual_array[1]);
                            $write_user_data = TRUE;
                            break;
                        case 'sms':
                            $multiotp->SetUserSms($actual_array[1]);
                            $write_user_data = TRUE;
                            break;
                        default: // Just in case we need to change additional values that have no related method
                            $internal_user_option = str_replace("-", "_", $actual_array[0]);
                            if ($multiotp->SetUserAttribute($internal_user_option, $actual_array[1]))
                            {
                                $write_user_data = TRUE;
                            }
                            break;
                    }
                }
            }
            if ($write_user_data)
            {
                if ($multiotp->WriteUserData())
                {
                    $result = 19; // INFO: Requested operation successfully done
                }
            }
        }
        break;
    case "config":
        $config_result = TRUE;
        $write_config_data = FALSE;
        if  ($param_count < 1)
        {
            $result = 30; // ERROR: At least one parameter is missing
        }
        else
        {
            for ($params = 1; $params < count($all_args); $params++)
            {
                $actual_array = explode("=",$all_args[$params],2);
                if (2 == count($actual_array))
                {
                    switch ($actual_array[0])
                    {
                        case 'attributes-to-encrypt':
                            $multiotp->SetAttributesToEncrypt($actual_array[1]);
                            $internal_config_option = str_replace("-", "_", $actual_array[0]);
                            if ($multiotp->SetConfigAttribute($internal_config_option, $actual_array[1]))
                            {
                                $write_config_data = TRUE;
                            }
                            break;
                        case 'autoresync':
                            $multiotp->SetAutoResync($actual_array[1]);
                            $write_config_data = TRUE;
                            break;
                        case 'backend-type':
                            $multiotp->SetBackendType($actual_array[1]);
                            $write_config_data = TRUE;
                            break;
                        case 'clear-otp-attribute':
                            $multiotp->SetClearOtpAttribute($actual_array[1]);
                            $write_config_data = TRUE;
                            break;
                        case 'debug':
                            $multiotp->SetDebugOption(intval($actual_array[1]));
                            $write_config_data = TRUE;
                            break;
                        case 'display-log':
                            $multiotp->SetDisplayLogOption(intval($actual_array[1]));
                            $write_config_data = TRUE;
                            break;
                        case 'debug-prefix':
                            $multiotp->SetVerboseLogPrefix($actual_array[1]);
                            $verbose_prefix = $multiotp->GetVerboseLogPrefix();
                            $write_config_data = TRUE;
                            break;
                        case 'default-request-prefix-pin':
                            $multiotp->SetDefaultRequestPrefixPin(intval($actual_array[1]));
                            $write_config_data = TRUE;
                            break;
                        case 'group-attribute':
                            $multiotp->SetGroupAttribute($actual_array[1]);
                            $write_config_data = TRUE;
                            break;
                        case 'ldap-account-suffix':
                            $multiotp->SetLdapAccountSuffix($actual_array[1]);
                            $write_config_data = TRUE;
                            break;
                        case 'ldap-activated':
                            $multiotp->SetLdapActivated($actual_array[1]);
                            $write_config_data = TRUE;
                            break;
                        case 'ldap-base-dn':
                            $multiotp->SetLdapBaseDn($actual_array[1]);
                            $write_config_data = TRUE;
                            break;
                        case 'ldap-bind-dn':
                            $multiotp->SetLdapBindDn($actual_array[1]);
                            $write_config_data = TRUE;
                            break;
                        case 'ldap-cn-identifier':
                            $multiotp->SetLdapCnIdentifier($actual_array[1]);
                            $write_config_data = TRUE;
                            break;
                        case 'ldap-domain-controllers':
                            $multiotp->SetLdapDomainControllers($actual_array[1]);
                            $write_config_data = TRUE;
                            break;
                        case 'ldap-group-attribute':
                            $multiotp->SetLdapGroupAttribute($actual_array[1]);
                            $write_config_data = TRUE;
                            break;
                        case 'ldap-in-group':
                            $multiotp->SetLdapInGroup($actual_array[1]);
                            $write_config_data = TRUE;
                            break;
                        case 'ldap-network-timeout':
                            $multiotp->SetLdapNetworkTimeout(intval($actual_array[1]));
                            $write_config_data = TRUE;
                            break;
                        case 'ldap-port':
                            $multiotp->SetLdapPort(intval($actual_array[1]));
                            $write_config_data = TRUE;
                            break;
                        case 'ldap-server-password':
                            $multiotp->SetLdapServerPassword($actual_array[1]);
                            $write_config_data = TRUE;
                            break;
                        case 'ldap-ssl':
                            $multiotp->SetLdapSsl($actual_array[1]);
                            $write_config_data = TRUE;
                            break;
                        case 'ldap-time-limit':
                            $multiotp->SetLdapTimeLimit(intval($actual_array[1]));
                            $write_config_data = TRUE;
                            break;
                        case 'log':
                            $multiotp->SetLogOption(intval($actual_array[1]));
                            $write_config_data = TRUE;
                            break;
                        case 'radius-reply-attributor':
                            $multiotp->SetRadiusReplyAttributor($actual_array[1]);
                            $write_config_data = TRUE;
                            break;
                        case 'radius-reply-separator':
                            $multiotp->SetRadiusReplySeparator($actual_array[1]);
                            $write_config_data = TRUE;
                            break;
                        case 'self-registration':
                            $multiotp->SetSelfRegistration(intval($actual_array[1]));
                            $write_config_data = TRUE;
                            break;
                        case 'server-cache-level':
                            $multiotp->SetServerCacheLevel(intval($actual_array[1]));
                            $write_config_data = TRUE;
                            break;
                        case 'server-cache-lifetime':
                            $multiotp->SetServerCacheLifetime(intval($actual_array[1]));
                            $write_config_data = TRUE;
                            break;
                        case 'server-secret':
                            $multiotp->SetServerSecret($actual_array[1]);
                            $write_config_data = TRUE;
                            break;
                        case 'server-timeout':
                            $multiotp->SetServerTimeout(intval($actual_array[1]));
                            $write_config_data = TRUE;
                            break;
                        case 'server-type':
                            $multiotp->SetServerType($actual_array[1]);
                            $write_config_data = TRUE;
                            break;
                        case 'server-url':
                            $multiotp->SetServerUrl($actual_array[1]);
                            $write_config_data = TRUE;
                            break;
                        case 'sms-api-id':
                            $multiotp->SetSmsApiId($actual_array[1]);
                            $write_config_data = TRUE;
                            break;
                        case 'sms-message':
                            $multiotp->SetSmsMessage($actual_array[1]);
                            $write_config_data = TRUE;
                            break;
                        case 'sms-originator':
                            $multiotp->SetSmsOriginator($actual_array[1]);
                            $write_config_data = TRUE;
                            break;
                        case 'sms-password':
                            $multiotp->SetSmsPassword($actual_array[1]);
                            $write_config_data = TRUE;
                            break;
                        case 'sms-provider':
                            $multiotp->SetSmsProvider($actual_array[1]);
                            $write_config_data = TRUE;
                            break;
                        case 'sms-userkey':
                            $multiotp->SetSmsUserkey($actual_array[1]);
                            $write_config_data = TRUE;
                            break;
                        case 'sql-server':
                            $multiotp->SetSqlServer($actual_array[1]);
                            $write_config_data = TRUE;
                            break;
                        case 'sql-username':
                            $multiotp->SetSqlUsername($actual_array[1]);
                            $write_config_data = TRUE;
                            break;
                        case 'sql-password':
                            $multiotp->SetSqlPassword($actual_array[1]);
                            $write_config_data = TRUE;
                            break;
                        case 'sql-database':
                            $multiotp->SetSqlDatabase($actual_array[1]);
                            $write_config_data = TRUE;
                            break;
                        case 'sql-config-table':
                            $multiotp->SetSqlTableName('config',$actual_array[1]);
                            $write_config_data = TRUE;
                            break;
                        case 'sql-devices-table':
                            $multiotp->SetSqlTableName('devices',$actual_array[1]);
                            $write_config_data = TRUE;
                            break;
                        case 'sql-log-table':
                            $multiotp->SetSqlTableName('log',$actual_array[1]);
                            $write_config_data = TRUE;
                            break;
                        case 'sql-tokens-table':
                            $multiotp->SetSqlTableName('tokens',$actual_array[1]);
                            $write_config_data = TRUE;
                            break;
                        case 'sql-users-table':
                            $multiotp->SetSqlTableName('users',$actual_array[1]);
                            $write_config_data = TRUE;
                            break;
                        case 'tel-default-country-code':
                            $multiotp->SetTelDefaultCountryCode($actual_array[1]);
                            $write_config_data = TRUE;
                            break;
                        case 'token-serial-number-length':
                            $multiotp->SetTokenSerialNumberLength($actual_array[1]);
                            $write_config_data = TRUE;
                            break;
                        default: // Just in case we need to change additional values that have no related method
                            $internal_config_option = str_replace("-", "_", $actual_array[0]);
                            if ($multiotp->SetConfigAttribute($internal_config_option, $actual_array[1]))
                            {
                                $write_config_data = TRUE;
                            }
                            break;
                    }
                }
            }
            if ($write_config_data)
            {
                if ($multiotp->WriteConfigData())
                {
                    $result = 19; // INFO: Requested operation successfully done
                }
            }
        }
        break;
    case "import":
        if (!file_exists($all_args[1]))
        {
            $result = 31; // ERROR: Tokens definition file doesn't exist.
        }
        else
        {
            if ($multiotp->ImportTokensFile($all_args[1], $all_args[1], $all_args[2]))
            {
                $result = 15; // INFO: Tokens definition file successfully imported
            }
            else
            {
                $result = 32; // ERROR: Tokens definition file not successfully imported.
            }
        }
        break;
    case "import-csv":
        if (!file_exists($all_args[1]))
        {
            $result = 31; // ERROR: Tokens definition file doesn't exist.
        }
        else
        {
            if ($multiotp->ImportTokensFromCsv($all_args[1]))
            {
                $result = 15; // INFO: Tokens definition file successfully imported
            }
            else
            {
                $result = 32; // ERROR: Tokens definition file not successfully imported.
            }
        }
        break;
    case "import-pskc":
        if (!file_exists($all_args[1]))
        {
            $result = 31; // ERROR: Tokens definition file doesn't exist.
        }
        else
        {
            if ($multiotp->ImportTokensFromPskc($all_args[1], $all_args[1], $all_args[2]))
            {
                $result = 15; // INFO: Tokens definition file successfully imported
            }
            else
            {
                $result = 32; // ERROR: Tokens definition file not successfully imported.
            }
        }
        break;
    case "import-xml":
        if (!file_exists($all_args[1]))
        {
            $result = 31; // ERROR: Tokens definition file doesn't exist.
        }
        else
        {
            if ($multiotp->ImportTokensFromXml($all_args[1]))
            {
                $result = 15; // INFO: Tokens definition file successfully imported
            }
            else
            {
                $result = 32; // ERROR: Tokens definition file not successfully imported.
            }
        }
        break;
    case "import-alpine-xml":
        if (!file_exists($all_args[1]))
        {
            $result = 31; // ERROR: Tokens definition file doesn't exist.
        }
        else
        {
            if ($multiotp->ImportTokensFromAlpineXml($all_args[1]))
            {
                $result = 15; // INFO: Tokens definition file successfully imported
            }
            else
            {
                $result = 32; // ERROR: Tokens definition file not successfully imported.
            }
        }
        break;
    case "import-dat":
        if (!file_exists($all_args[1]))
        {
            $result = 31; // ERROR: Tokens definition file doesn't exist.
        }
        else
        {
            if ($multiotp->ImportTokensFromAlpineDat($all_args[1]))
            {
                $result = 15; // INFO: Tokens definition file successfully imported
            }
            else
            {
                $result = 32; // ERROR: Tokens definition file not successfully imported.
            }
        }
        break;
    case "import-sql":
        if (!file_exists($all_args[1]))
        {
            $result = 31; // ERROR: Tokens definition file doesn't exist.
        }
        else
        {
            if ($multiotp->ImportTokensFromAuthenexSql($all_args[1]))
            {
                $result = 15; // INFO: Tokens definition file successfully imported
            }
            else
            {
                $result = 32; // ERROR: Tokens definition file not successfully imported.
            }
        }
        break;
    case "qrcode":
        if  ($param_count < 2)
        {
            $result = 30; // ERROR: At least one parameter is missing
        }
        elseif (!$multiotp->CheckUserExists($all_args[1]))
        {
            $result = 21; // ERROR: user doesn't exist.
        }
        else
        {
            if ($multiotp->GetUserTokenQrCode($all_args[1], '', $all_args[2]))
            {
                $result = 16; // INFO: QRcode successfully created.
            }
            else
            {
                $result = 50; // INFO: QRcode not created.
            }
        }
        break;
    case "urllink":
        if  ($param_count < 1)
        {
            $result = 30; // ERROR: At least one parameter is missing
        }
        elseif (!$multiotp->CheckUserExists($all_args[1]))
        {
            $result = 21; // ERROR: user doesn't exist.
        }
        else
        {
            if (FALSE !== ($url_result = $multiotp->GetUserTokenUrlLink($all_args[1])))
            {
                echo $url_result.$crlf;
                $result = 17; // INFO: UrlLink successfully created.
            }
            else
            {
                $result = 51; // INFO: UrlLink not created.
            }
        }
        break;
    case "scratchlist":
        echo str_replace("\t",$crlf,$multiotp->GetUserScratchPasswordsList($all_args[1])).$crlf;
        $result = 19;
        break;
    case "userslist":
        echo str_replace("\t",$crlf,$multiotp->GetUsersList()).$crlf;
        $result = 19;
        break;
    case "tokenslist":
        echo str_replace("\t",$crlf,$multiotp->GetTokensList()).$crlf;
        $result = 19;
        break;
    case "ldap-users-list":
        if ('' != $multiotp->_config_data['ldap_domain_controllers'])
        {
            $ldap_users_list = $multiotp->GetLdapUsersList();
            if ('' != $ldap_users_list)
            {
                echo str_replace("\t",$crlf,$ldap_users_list).$crlf;
                $result = 19;
            }
            else
            {
                $result = 39;
            }
        }
        else
        {
            $result = 39;
        }
        break;
    case "ldap-user-info":
        print_r($multiotp->GetLdapUsersInfoArray($all_args[1], TRUE, TRUE));
        $result = 19;
        break;
    case "ldap-users-sync":
        $result = (($multiotp->SyncLdapUsers())?19:99);
        break;
    case "showlog":
        $multiotp->ShowLog();
        $result = 19;
        break;
    case "ldap-check":
        $result = (($multiotp->CheckLdapAuthentication())?19:99);
        break;
    case "fastcreate":
        if ($multiotp->CheckUserExists($all_args[1]))
        {
            $result = 22; // ERROR: user already exists.
        }
        elseif  ($param_count < 1)
        {
            $result = 30; // ERROR: At least one parameter is missing
        }
        else
        {
            if ($multiotp->CreateUser($all_args[1], $prefix_pin?1:0, "TOTP", '', (''!=$all_args[2])?$all_args[2]:''))
            {
                $result = 11; // INFO: user successfully created or updated
            }
            else
            {
                $result = 35; // ERROR: user not created
            }
        }
        break;
    case "createga":
        if ($multiotp->ReadUserData($all_args[1], TRUE))
        {
            $result = 22; // ERROR: user already exists.
        }
        elseif  ($param_count < 2)
        {
            $result = 30; // ERROR: At least one parameter is missing
        }
        else
        {
            if ($multiotp->CreateUser($all_args[1], 0, "TOTP", bin2hex(base32_decode($all_args[2])), (''!=$all_args[3])?$all_args[3]:''))
            {
                $result = 11; // INFO: user successfully created or updated
            }
            else
            {
                $result = 35; // ERROR: user not created
            }
        }
        break;
    case "phpinfo":
        phpinfo();
        break;
    case "libhash":
        echo $multiotp->GetLibraryHash($all_args[1], $all_args[2])."\n";
        $result = 19;
        break;
    case "custominfo":
        echo $multiotp->GetCustomInfo()."\n";
        $result = 19;
        break;
    case "error":
        break;
    case "help":
    default:
        // Help or others, except the -initialize-backend option.
        if (!$initialize_backend)
        {
            $result = 999; // Info only
            echo $multiotp->GetClassName()." ".$multiotp->GetVersion()." (".$multiotp->GetDate().")";
            echo ", running with embedded PHP version ".phpversion().$crlf;
            echo $multiotp->GetCopyright().$crlf;
            echo $multiotp->GetWebsite()."   (you can try the [Donate] button ;-)".$crlf;
            echo $crlf;
            if ($multiotp->GetVerboseFlag())
            {
                echo "Detected script folder: ".$multiotp->GetScriptFolder().$crlf;
                echo $crlf;
            }
            echo "multiotp will check if the token of a user is correct, based on a specified".$crlf;
            echo "algorithm (currently Mobile-OTP (http://motp.sf.net), OATH/HOTP (RFC 4226) ".$crlf;
            echo "and OATH/TOTP (RFC 6238) are implemented). PSKC format supported (RFC 6030).".$crlf;
            echo "Supported encryption methods are PAP and CHAP.".$crlf;
            echo "SMS-code are supported (current providers: aspsms,clickatell,intellisms).".$crlf;
            echo "Customized SMS sender program supported by specifying exec as SMS provider.".$crlf;
            echo $crlf;
            echo "Google Authenticator base32_seed tokens must be of n*8 characters.".$crlf;
            echo "Google Authenticator TOTP tokens must have a 30 seconds interval.".$crlf;
            echo "Available characters in base32 are only ABCDEFGHIJKLMNOPQRSTUVWXYZ234567".$crlf;
            echo $crlf;
            echo "To quickly create a user, use the -fascreate option with the name of the user.".$crlf;
            echo "A quickly created user is compatible with Google Auth (30 seconds, 6 digits).".$crlf;
            echo $crlf;
            echo "If a token is locked (return code 24), you have to resync the token to unlock.".$crlf;
            echo "Requesting an SMS token (put sms as the password), and typing the received".$crlf;
            echo " token correctly will also unlock the token.".$crlf;
            if (!function_exists('ImageCreate'))
            {
                echo $crlf;
                echo "!!! You need to enable the gd2 library in order to create QRcode !!!".$crlf;
            }
            echo $crlf;
            echo "The check will return 0 for a correct token, and the other return code means:".$crlf;
            echo $crlf;
            echo "Return codes:".$crlf;
            echo $crlf;
            
            reset($multiotp->_errors_text);
            while(list($key, $value) = each($multiotp->_errors_text))
            {
                echo substr('  '.$key,-2)." ".$value.$crlf;
            }
            echo $crlf;
            echo $crlf;
            echo "Usage:".$crlf;
            echo $crlf;
            echo " multiotp user token (to check if the token is accepted)".$crlf;
            echo " multiotp -checkpam (to check with pam-script, using PAM_USER and PAM_AUTHTOK)".$crlf;
            echo $crlf;
            echo " multiotp -requiresms user (generate and send an SMS token to the user)".$crlf;
            echo " multiotp user sms (send an SMS token to the user)".$crlf;
            echo $crlf;
            echo " multiotp user [-chap-id=0x..] -chap-challenge=0x... -chap-password=0x...".$crlf;
            echo "   (the first byte of the chap-password value can contain the chap-id value)".$crlf;
            echo $crlf;
            echo " multiotp -fastcreate user [pin] (create a Google Auth compatible token)".$crlf;
            echo " multiotp -createga user base32_seed [pin] (create Google Authenticator user)".$crlf;
            echo " multiotp -create [-prefix-pin] user algo seed pin digits [pos|interval]".$crlf;
            echo " multiotp -create -token-id [-prefix-pin] user token-id pin".$crlf;
            echo $crlf;
            echo "  token-id: id of the previously imported token to attribute to the user".$crlf;
            echo "      user: name of the user (should be the account name)".$crlf;
            echo "      algo: available algorithms are mOTP, HOTP and TOTP".$crlf;
            echo "      seed: hexadecimal seed of the token".$crlf;
            echo "       pin: private pin code of the user".$crlf;
            echo "    digits: number of digits given by the token".$crlf;
            echo "       pos: for HOTP algorithm, position of the next awaited event".$crlf;
            echo "  interval: for mOTP and TOTP algorithms, token interval time in seconds".$crlf;
            echo $crlf;
            echo " multiotp -import tokens_definition_file [key|pass] (auto-detect format)".$crlf;
            echo " multiotp -import-csv csv_tokens_file.csv (tokens definition in a file)".$crlf;
            echo "   (serial_number;manufacturer;algorithm;seed;digits;interval_or_event)".$crlf;
            echo " multiotp -import-pskc pskc_tokens_file.pskc [key|pass] (PSKC format, RFC 6030)".$crlf;
            echo " multiotp -import-dat importAlpine.dat (SafeWord/Aladdin/SafeNet tokens)".$crlf;
            echo " multiotp -import-alpine-xml alpineXml.xml (SafeWord/Aladdin/SafeNet)".$crlf;
            echo " multiotp -import-xml xml_tokens_definition_file.xml (old Feitian)".$crlf;
            echo " multiotp -import-sql tokens_definition_file.sql (ZyXEL/Authenex)".$crlf;
            echo $crlf;
            echo " multiotp -qrcode user png_file_name.png (only for TOTP and HOTP)".$crlf;
            echo " multiotp -urllink user (only for TOTP and HOTP, generate provisioning URL)".$crlf;
            echo $crlf;
            echo " multiotp -scratchlist user (generate & display scratch passwords for the user)".$crlf;
            echo $crlf;
            echo " multiotp -resync [-status] user token1 token2 (two consecutive tokens)".$crlf;
            echo " multiotp -update-pin user pin".$crlf;
            echo $crlf;
            echo " multiotp -[des]activate user".$crlf;
            echo " multiotp -[un]lock user".$crlf;
            echo $crlf;
            echo " multiotp -delete user".$crlf;
            echo $crlf;
            echo " multiotp -user-info user".$crlf;
            echo $crlf;
            echo " multiotp -config option1=value1 option2=value2 ... optionN=valueN".$crlf;
            echo "  options are  ";
            echo                "  autoresync: [0|1] enable/disable autoresync during login".$crlf;
            echo "      attributes-to-encrypt: specific attributes list to encrypt, must be".$crlf;
            echo "                             surrounded by *, like '*token_seed*user_pin*'".$crlf;
            echo "               backend-type: backend storage type (files|mysql)".$crlf;
            echo "        clear-otp-attribute: attribute to return for the clear OTP".$crlf;
            echo "                             (for example 'ietf|2' for TekRADIUS)".$crlf;
            echo "                      debug: [0|1] enable/disable enhanced log information".$crlf;
            echo "                             (code result are also displayed on the console)".$crlf;
            echo "               debug-prefix: add a prefix when using the debug mode".$crlf;
            echo "                             (for example 'Reply-Message := ' for FreeRADIUS)".$crlf;
            echo " default-request-prefix-pin: [0|1] prefix PIN is by default enabled or disabled".$crlf;
            echo "                display-log: [0|1] enable/disable log display on the console".$crlf;
            echo "            group-attribute: attribute to return for the group membership".$crlf;
            echo "                             (for example 'Filter-Id' for FreeRADIUS)".$crlf;
            echo "        ldap-account-suffix: LDAP/AD account suffix".$crlf;
            echo "             ldap-activated: [0|1] enable/disable LDAP/AD support".$crlf;
            echo "               ldap-base-dn: LDAP/AD base".$crlf;
            echo "               ldap-bind-dn: LDAP/AD bind ".$crlf;
            echo "         ldap-cn-identifier: LDAP/AD cn identifier (default is sAMAccountName)".$crlf;
            echo "    ldap-domain-controllers: LDAP/AD domain controller(s), comma separated".$crlf;
            echo "       ldap-group-attribute: LDAP/AD group identifier (default is memberOf)".$crlf;
            echo "              ldap-in-group: LDAP/AD group(s) in which users should be in".$crlf;
            echo "       ldap-network-timeout: LDAP/AD network timeout (in seconds)".$crlf;
            echo "                  ldap-port: LDAP/AD port (default is set to 389)".$crlf;
            echo "       ldap-server-password: LDAP/AD server password".$crlf;
            echo "                   ldap-ssl: [0|1] enable/disable LDAP/AD SSL connection".$crlf;
            echo "            ldap-time-limit: LDAP/AD number of sec. to wait for search results".$crlf;
            echo "                        log: [0|1] enable/disable log permanently".$crlf;
            echo"     radius-reply-attributor: [ = |=] how to attribute a value".$crlf;
            echo "                             ('=' for TekRADIUS, ' = ' for FreeRADIUS)".$crlf;
            echo "     radius-reply-separator: [,|:|;|cr|crlf] returned attributes separator".$crlf;
            echo "                             ('crlf' for TekRADIUS, ',' for FreeRADIUS)".$crlf;
            echo "          self-registration: [0|1] enable/disable self-registration of tokens".$crlf;
            echo "         server-cache-level: [0|1] enable/allow cache from server to client".$crlf;
            echo "      server-cache-lifetime: lifetime in seconds of the cached information".$crlf;
            echo "              server-secret: shared secret used for client/server operation".$crlf;
            echo "             server-timeout: timeout value for the connection to the server".$crlf;
            echo "                server-type: [xml] type of the server".$crlf;
            echo "                             (only xml server are able to do caching)".$crlf;
            echo "                 server-url: full url of the server for client/server mode".$crlf;
            echo "                             (server_url_1;server_url_2 is accepted)".$crlf;
            echo "                 sms-api-id: SMS API id (clickatell only, give your XML API id)".$crlf;
            echo "                             with exec as provider, define the script to call".$crlf;
            echo "                             (available variables: %from, %to, %message)".$crlf;
            echo "                sms-message: SMS message to display before the OTP".$crlf;
            echo "             sms-originator: SMS sender (if authorized by provider)".$crlf;
            echo "               sms-password: SMS account password".$crlf;
            echo "               sms-provider: SMS provider (aspsms,clickatell,intellisms,exec)".$crlf;
            echo "                sms-userkey: SMS account username or userkey".$crlf;
            echo "                 sql-server: SQL server (FQDN or IP)".$crlf;
            echo "               sql-username: SQL username".$crlf;
            echo "               sql-password: SQL password".$crlf;
            echo "               sql-database: SQL database".$crlf;
            echo "           sql-config-table: SQL config table, default is multiotp_config".$crlf;
            echo "          sql-devices-table: SQL devices table, default is multiotp_devices".$crlf;
            echo "              sql-log-table: SQL log table, default is multiotp_log".$crlf;
            echo "           sql-tokens-table: SQL tokens table, default is multiotp_tokens".$crlf;
            echo "            sql-users-table: SQL users table, default is multiotp_users".$crlf;
            echo "   tel-default-country-code: Default country code for phone number".$crlf;
            echo " token-serial-number-length: Length of the serial number of the tokens".$crlf;
            echo "                             (used for self-registration)".$crlf;
            echo $crlf;
            echo " multiotp -initialize-backend (when all options are set, it will initialize".$crlf;
            echo "                               the backend, including creating the tables)".$crlf;
            echo $crlf;
            echo " multiotp -set user option1=value1 option2=value2 ... optionN=valueN".$crlf;
            echo "  options are  email: update the email of the user".$crlf;
            echo "         description: set a description to the user, used for example during".$crlf;
            echo "                      the QRcode generation as the description of the account".$crlf;
            echo "               group: set/update the group of the user".$crlf;
            echo "          prefix-pin: [0|1] the pin and the token must by merged by the user".$crlf;
            echo "                      (if your pin is 1234 and your token displays 5556677,".$crlf;
            echo "                      you will have to type 1234556677)".$crlf;
            echo "                 pin: set/update the private pin code of the user".$crlf;
            echo "                 sms: set/update the sms phone number of the user".$crlf;
            echo $crlf;
            echo $crlf;
            echo "LDAP/AD integration:".$crlf;
            echo $crlf;
            echo " multiotp -ldap-check".$crlf;
            echo " multiotp -ldap-user-info user".$crlf;
            echo " multiotp -ldap-users-list".$crlf;
            echo " multiotp -ldap-users-sync".$crlf;
            echo $crlf;
            echo $crlf;
            echo "Other commands:".$crlf;
            echo $crlf;
            echo " multiotp -phpinfo".$crlf;
            echo " multiotp -showlog".$crlf;
            echo " multiotp -tokenslist".$crlf;
            echo " multiotp -userslist".$crlf;
            echo $crlf;
            echo $crlf;
            echo "Other parameters:".$crlf;
            echo $crlf;
            echo " -base-dir=/full/path/to/the/main/folder/of/multiotp/".$crlf;
            echo "           (if the script folder is wrongly detected, this will fix the issue)".$crlf;
            echo $crlf;
            echo $crlf;
            echo "Switches:".$crlf;
            echo $crlf;
            echo " -debug          Enhanced log information activated and code result on console".$crlf;
            echo "                 (the permanent state of debug can be set with -config debug=1)".$crlf;
            echo " -display-log    Log information will also be displayed on the console".$crlf;
            echo "                 (the permanent state can be set with -config display-log=1)".$crlf;
            echo " -help           Display this help page".$crlf;
            echo " -keep-local     Keep local user even if the server doesn't have it".$crlf;
            echo "                 (if the server doesn't have it, the local one will be checked)".$crlf;
            echo " -log            Log operation in the log subdirectory or in the database".$crlf;
            echo "                 (the permanent state of log can be set with -config log=1)".$crlf;
            echo " -mysql          MySQL connection information, comma separated (server,user,".$crlf;
            echo "                 password,database[,log_table[,users_table[,tokens_table]]])".$crlf;
            echo "                 (this switch is DEPRECATED, use the -config switch instead)".$crlf;
            echo " -param          All parameters are logged for debugging purposes".$crlf;
            echo " -prefix-pin     The pin and the token must be typed merged by the user".$crlf;
            echo "                 (if you pin is 1234 and your token displays 5556677,".$crlf;
            echo "                  you will have to type 1234556677)".$crlf;
            echo "                 (this switch is DEPRECATED, use the -set switch instead)".$crlf;
            echo " -request-nt-key This will return the NT_KEY to the radius server".$crlf;
            echo " -status         Display a status bar during resynchronization".$crlf;
            echo " -version        Display the current version of the library".$crlf;
            echo " -php-version    Display the current version of the running PHP interpreter".$crlf;
            echo $crlf;
            echo $crlf;
            echo "Examples:".$crlf;
            echo $crlf;
            echo " multiotp -display-log -log -debug jimmy ea2315".$crlf;
            echo " multiotp -display-log -log anna 546078".$crlf;
            echo " multiotp -display-log -log -checkpam".$crlf;
            echo " multiotp john 5678124578".$crlf;
            echo $crlf;
            echo " multiotp jimmy sms".$crlf;
            echo $crlf;
            echo " multiotp -fastcreate gademo".$crlf;
            echo " multiotp -debug -createga gauser 2233445566777733".$crlf;
            echo " multiotp -debug -create -prefix-pin alan TOTP 3683453456769abc3452 2233 6 60".$crlf;
            echo " multiotp -debug -create -prefix-pin anna TOTP 56821bac24fbd2343393 4455 6 30".$crlf;
            echo " multiotp -debug -create -prefix-pin john HOTP 31323334353637383930 5678 6 137".$crlf;
            echo " multiotp -debug -create -token-id -prefix-pin rick 2010090201901 2345".$crlf;
            echo " multiotp -log -create jimmy mOTP 004f5a158bca13984d349a7f23 1234 6 10".$crlf;
            echo $crlf;
            echo " multiotp -scratchlist gademo".$crlf;
            echo $crlf;
            echo " multiotp -set gademo description=\"VPN code for gademo\"".$crlf;
            echo " multiotp -set gademo sms=41791234567".$crlf;
            echo $crlf;
            echo " multiotp -debug -import tokens.pskc \"1234 5678 9012 3456 7890 1234 5678 9012\"".$crlf;
            echo " multiotp -debug -import-pskc tokens.pskc \"qwerty\"".$crlf;
            echo " multiotp -debug -import 10OTP_data01_upgrade.sql".$crlf;
            echo " multiotp -debug -import-dat importAlpine.dat".$crlf;
            echo $crlf;
            echo " multiotp -debug -qrcode gademo gademo.png".$crlf;
            echo " multiotp -debug -urllink john".$crlf;
            echo $crlf;
            echo " multiotp -resync john 5678456789 5678345231".$crlf;
            echo " multiotp -resync -status anna 4455487352 4455983513".$crlf;
            echo " multiotp -update-pin alan 4417".$crlf;
            echo $crlf;
            echo " multiotp -config debug-prefix=\"Reply-Message := \"".$crlf;
            echo $crlf;
            echo " multiotp -config server-cache-level=1 server-cache-lifetime=15552000".$crlf;
            echo " multiotp -config server-secret=MySharedSecret server-type=xml".$crlf;
            echo " multiotp -config server-timeout=3".$crlf;
            echo " multiotp -config server-url=http://my.server/multiotp/;my.server2:8112/secure/".$crlf;
            echo $crlf;
            echo " multiotp -config sms-provider=clickatell sms-userkey=CL1 sms-password=PASS".$crlf;
            echo " multiotp -config sms-api-id=1234567".$crlf;
            echo " multiotp -config sms-message=\"Your SMS-code is:\" sms-originator=Company".$crlf;
            echo " multiotp -config sms-message=\"Type %s as code\" sms-originator=0041797654321".$crlf;
            echo $crlf;
            echo " multiotp -config sms-provider=exec sms-api-id=/path/to/myapp %from %to \"%msg\"".$crlf;
            echo $crlf;
            echo " multiotp -config token-serial-number-length=10,12".$crlf;
            echo $crlf;
            echo " multiotp -config backend-type=mysql sql-server=fqdn.or.ip sql-database=dbname".$crlf;
            echo " multiotp -config sql-username=user sql-password=pass".$crlf;
            echo " multiotp -initialize-backend".$crlf;
            echo $crlf;
            echo $crlf;
            echo "multiOTP web service is working fine with any web server supporting PHP.".$crlf;
            echo " - nginx is a light one under Linux (http://nginx.org/)".$crlf;
            echo " - Mongoose is a light one under Windows (http://code.google.com/p/mongoose/)".$crlf;
            echo " - and many others like Apache HTTP Server (http://httpd.apache.org/)".$crlf;
            echo $crlf;
            echo "multiOTP is working fine with FreeRADIUS under Linux (http://freeradius.org/)".$crlf;
            echo "multiOTP is also working fine with the last Windows port of FreeRADIUS".$crlf;
            echo "(http://sourceforge.net/projects/freeradius/)".$crlf;
            echo $crlf;
            echo "multiOTP can be combined with a Raspberry Pi (http://www.raspberrypi.org/) in".$crlf;
            echo "order to have a very low budget strong authentication device. Please look at".$crlf;
            echo "the readme file in order to learn how to set it up in a few steps.".$crlf;
            echo $crlf;
            echo "When used with TekRADIUS (http://www.tekradius.com) the External-Executable".$crlf;
            echo "must be called like this: C:\multiotp\multiotp.exe %ietf|1% %ietf|2%".$crlf;
            echo $crlf;
            echo "Other products and services based on multiOTP are :".$crlf;
            echo " multiOTP Pro       an extended library with additional features".$crlf;
            echo "                    (http://www.multiOTP.com)".$crlf;
            echo " multiOTP Pro 405V  a Pro version with full web GUI in a tiny virtual appliance".$crlf;
            echo "                    (http://www.multiOTP.com)".$crlf;
            echo " multiOTP Pro 420B  a Pro version with full web GUI in a tiny hardware device".$crlf;
            echo "                    (http://www.multiOTP.com)".$crlf;
            echo " secuPASS.net       a simple SMS trusting service for free WLAN Hotspot".$crlf;
            echo "                    (http://www.secuPASS.net)".$crlf;
            echo " mOTP-CP            an Open-Source Credential Provider for the Windows Logon".$crlf;
            echo "                    (http://goo.gl/BZAhKR)".$crlf;
            echo " ownCloud OTP       a One Time Password app for ownCloud (http://owncloud.org)".$crlf;
            echo "                    (http://apps.owncloud.com/content/show.php/?content=159196)".$crlf;
            echo $crlf;
            echo "Visit http://forum.multiotp.net/ for additional support".$crlf;
            echo $crlf;
            echo $crlf;
        }
        break;
}


if ($command != "libhash")
{
    if ($initialize_backend)
    {
        $result = $multiotp->InitializeBackend();
    }


    if ($param_info_debug)
    {
        $param_info = '';
        foreach ($all_args as $one_arg)
        {
            if ('' != $one_arg)
            {
                $param_info .= $one_arg.' ';
            }
        }
        $multiotp->WriteLog('Debug: Parameters are: '.trim($param_info), FALSE, FALSE, 8888, 'Debug', '');
    }


    if (999 == $result) // Help information only, we don't want to display the result code in this case
    {
        $result = 19;
    }
    else
    {
        $reply_message = '';
        // Log the result
        $result_log = $result.' '.(isset($multiotp->_errors_text[$result])?$multiotp->_errors_text[$result]:'');
        if ($multiotp->GetVerboseFlag())
        {
            $reply_message = $result.' '.(isset($multiotp->_errors_text[$result])?$multiotp->_errors_text[$result]:'');
        }
        if ($verbose_prefix != '')
        {
            $reply_message = $result;
            if ($multiotp->GetVerboseFlag())
            {
                $reply_message.=' '.(isset($multiotp->_errors_text[$result])?$multiotp->_errors_text[$result]:'');
            }
            $reply_message = $verbose_prefix."\"".$reply_message."\"";
            $result_log = $verbose_prefix."\"".$result_log."\"";
        }
        if ($multiotp->GetVerboseFlag())
        {
            $multiotp->WriteLog('Debug: '.$result_log, FALSE, TRUE, 8888, 'Debug', '');
        }
        if ($multiotp->GetDisplayLogFlag())
        {
            echo $reply_message.$crlf;
        }

        $radius_additional = '';
        $radius_separator = '';

        if (count($multiotp->GetReplyArrayForRadius()) > 0)
        {
            $ignore_radius_array = explode(";","xxxx;yyyy");
            foreach ($multiotp->GetReplyArrayForRadius() as $one_radius_message)
            {
                $ignore_attribute = FALSE;
                $current_attribute = trim(substr($one_radius_message, 0, strpos($one_radius_message, trim($multiotp->GetRadiusReplyAttributor()))));
                foreach ($ignore_radius_array as $one_ignore_attribute)
                {
                    if (FALSE !== strpos(strtoupper($current_attribute),strtoupper($one_ignore_attribute)))
                    {
                        $ignore_attribute = TRUE;
                    }
                }
                if (!$ignore_attribute)
                {
                    $radius_additional.= $radius_separator.$one_radius_message;
                    $radius_separator = $multiotp->GetRadiusReplySeparator();
                }
            }
        }
        if ($request_nt_key)
        {
            $nt_key = trim($multiotp->GetNtKey());
            if ('' != $nt_key)
            {
                $radius_additional.= $radius_separator."NT_KEY: ".$nt_key."\n";
            }
        }
        if (0 < strlen($radius_additional))
        {
            if ($multiotp->GetVerboseFlag())
            {
                $multiotp->WriteLog('Debug: Attributes sent to the RADIUS server: '.$radius_additional, FALSE, FALSE, 8888, 'Debug', '');
            }
          echo $radius_additional."\r\n";
        }
    }
}
exit(intval($result));
?>