<?php
/**
 * A wrapper class for common access to User information, including
 *   - useranme
 *   - display name
 *   - email
 *   - disk quota
 * Questions:
 *   - probably needs to modify user info collection gui to include things like email
 */
namespace OCP;

use OC_Preferences;

class MC_User
{
    /**
     * @brief returns metadata about userid
     * @return array_map of metadata with the following keys
     *  userId - user's id
     *  displayName - user's display name
     *  homePath - full path to directory on server where user's files are stored
     *  email - user's email
     *  group - the first group id this user belongs to
     *  groups - an array containing the group ids this user belongs to
     *  type - the type of the first group this user is (TA or FFI)
     *  types - an array containing the types this user is (TA or FFI)
     *          Note that for all "normal" users, doing a $var['types'][0] on the returned array map should get the user's type
     *          since users do not normally belong to more than 1 type; but we're storing this as an array
     *          because it is possible for a user to be in more than one group
     */
    public static function getUserInfo()
    {
        $userid = "";
        if (func_num_args() == 0)
            $userid = \OC_User::getUser(); // assume current user
        else {
            $userid = func_get_arg(0);
        } // use 0th parameter

        $groups = \OC_Group::getUserGroups($userid);
        $groupOfUser = \OCP\MC_Group::getGroupInfo($groups[0]);
        return array(
            "userId" => $userid,
            "displayName" => \OC_User::getDisplayName($userid),
            "homePath" => \OC_User::getHome($userid),
            "country" => $groupOfUser['country'],
            "email" => \OC_Preferences::getValue($userid, "settings", "email"),
//			"quota"         => \OC_Preferences::getValue( $userid, "files", "quota" )
            "group" => $groupOfUser['groupId'],
            "groups" => $groups,
            "type" => $groupOfUser['type'],
            "types" => MC_Group::getAllGroupTypes($groups),
            "igaPartners" => self::getIGAPartners(),
        );
    }


    /**
     * @brief returns metadata about notification options, ie, which notifications the user wants to receive emails from
     *
     * @param userId - user id (optional); if left out, it returns the currently logged in user
     *
     * @return array of OCA\MailNotify\NotificationTypes
     */
    public static function getNotificationOptions()
    {
        $userid = "";
        if (func_num_args() == 0)
            $userid = \OC_User::getUser(); // assume current user
        else
            $userid = func_get_arg(0); // use 0th parameter

        $str = OC_Preferences::getValue($userid, 'settings', 'notificationTypes');
        $arr = explode(',', $str);
        return $arr;
    }


    /**
     * @brief returns emails notifications should be sent
     *
     * @param userId - user id (optional); if left out, it uses the currently logged in user
     *
     * @return array of OCA\MailNotify\NotificationTypes
     */
    public static function getNotificationEmails()
    {
        $userid = "";
        if (func_num_args() == 0)
            $userid = \OC_User::getUser(); // assume current user
        else
            $userid = func_get_arg(0); // use 0th parameter

        $str = OC_Preferences::getValue($userid, 'settings', 'notificationEmail');
        $arr = explode(',', $str);
        return $arr;
    }


    /**
     * @brief get the user id of the user currently logged in.
     * @return string user id
     */
    public static function getUserId()
    {
        return \OC_User::getUser();
    }


    /**
     * @brief get the display name of the user currently logged in.
     * @return string display name
     */
    public static function getDisplayName()
    {
        return \OC_User::getDisplayName(MC_User::getUserId());
    }


    /**
     * @brief returns the path to the home directory of the user currently logged in
     * @returns string the path to the users home directory
     */
    public static function getHomePath()
    {
        return \OC_User::getHome(MC_User::getUserId());
    }


    /**
     * @brief returns the email of the user currently logged in
     * @returns string current user's email
     */
    public static function getEmail()
    {
        return \OC_Preferences::getValue(MC_User::getUserId(), "settings", "email");
    }


    /**
     * @brief returns the quota of the user currently logged in
     * @returns string current user's quota
     */
    public static function getQuota()
    {
        return \OC_Preferences::getValue(MC_User::getUserId(), "files", "quota");
    }


    /**
     * @brief returns the groups that this user belongs to
     * @returns array of groups
     */
    public static function getGroups()
    {
        return \OC_Group::getUserGroups(MC_User::getUserId());
    }

    /**
     * @brief returns the countries with which this user's country has an existing IGA
     * @returns array of countries (groups) with which this user's country has an existing IGA
     */

    public static function getIGAPartners(){
        $myGroups = self::getGroups();
	    $myGroup = $myGroups[0];

        $myGroupInfo = \OCP\MC_Group::getGroupInfo($myGroup);
        $myGiin = $myGroupInfo['giin'];
        Util::writeLog('mc_user', "TDM: myGroup = $myGroup and myGiin = $myGiin", 4);
        $stmt = \OC_DB::prepare('select foreign_country from *PREFIX*iga where local_country = ?');
        $params = array($myGiin);
        $result = $stmt->execute($params);

        $rows = array();

        //Collect all the arrays returned by the query
        while($row = $stmt->fetchRow()){
            //Util::writeLog('mc_user',"TDM: row=$row",4);
            $rows[] = $row['foreign_country'];
        }
        return $rows;
    }
}
