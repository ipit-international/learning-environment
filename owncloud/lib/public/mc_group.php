<?php
/**
 * We are using Groups as though they are Organizations, i.e., a TA or FFI are both represented as an extended
 * version of ownCloud's Group.  This is a wrapper class for common access to Group information as Organization, including
 *   - type
 *   - display name
 *   - country
 *   - model/option
 *   - public (and private) PKI keys
 * Questions:
 *   - needs to modify group info collection gui
 */
namespace OCP;
class MC_Group
{
    /**
     * @brief returns metadata about groupid
     * @return array_map of metadata with the following keys
     *  groupId - group's id
     *  displayName - group's display name
     *  type - the type of this group (TA or FFI)
     *  country - the country this group represents
     *  model - the model of this group
     *  option - the option of this group
     *  publicKey - the public key of this group
     *  users - array with display names (value) and user ids(key)
     */
    public static function getGroupInfo($groupId)
    {
        if (is_null($groupId)) {
            return null;
        }
//		$queryStr =  'SELECT * FROM `*PREFIX*groups` WHERE `gid` = \'' . $groupId . '\'';
        $query = \OC_DB::prepare('SELECT * FROM `*PREFIX*groups` WHERE `gid` = ? ');
//		$query = OC_DB::prepare( $queryStr );
        $result = $query->execute(array($groupId));
        $info = $result->fetchRow();
        $users = \OC_Group::displayNamesInGroup($groupId);
        return array(
            "groupId" => $groupId,
            "displayName" => $info['displayname'],
            "type" => $info['type'],
            "country" => $info['country'],
//            "model" => $info['model'],
//            "option" => $info['option'],
            "publicKey" => $info['publicKey'],
            "users" => $users,
	    "suEmail" => $info['suEmail'],
	    "encryption"=>$info['encryption'],
	    "issuer"=>$info['issuer'],
	    "subject"=>$info['subject'],
	    "giin"=>$info['giin'],
	    "certVersion"=>$info['certVersion'],
	    "compression"=>$info['compression'],
	    "antivirus"=>$info['antivirus']
        );
    }


    /**
     * @brief get a list of all groups
     * @returns array with group ids
     *
     * Returns a list with all groups
     */
    public static function getGroups($search = '', $limit = null, $offset = null)
    {
//        return "groups";
        return \OC_Group::getGroups($search);
    }

    public static function getGroupNameFromGiin($giin){
        if (is_null($giin)) {
            return null;
        }
        $query = \OC_DB::prepare('SELECT * FROM `*PREFIX*groups` WHERE `giin` = ? ');
        $result = $query->execute(array($giin));
        $info = $result->fetchRow();
        return $info['gid'];
    }

    /**
     * @brief gets all types in groups
     * @returns array with all types in named group
     */
    public static function getAllGroupTypes($groups)
    {
        $query = \OC_DB::prepare('SELECT type FROM `*PREFIX*groups` WHERE `gid` IN (' . MC_Utils::arrayAsSqlString($groups) . ')');
        $result = $query->execute();
        $types = $result->fetchAll();
        $allTypesMap = array();
        // put everything in as map (so if there are multiple groups of the same type, it only appears once
        //  and later if necessary, we can count the number of each type
        foreach ($types as $type) {
            $typeVal = $type['type'];
            $allTypesMap[$typeVal] = $typeVal;
        }
        $allTypes = array();
        foreach ($allTypesMap as $type) {
            if ($type != '') // default value for column is '', so if this is encountered
                //  then the group was not set up correctly, and we'll ignore
                array_push($allTypes, $type);
        }
        return $allTypes;
    }


    /**
     * @brief is user in a group of type type?
     * @param array mygroups - the groups to test the type for (usually the result of a MC_User::getGroups()
     * @param string type - the type to test for (e.g., 'FFI', 'TA')
     * @return bool
     *
     * Checks whether the user is member of an group of type type
     */
    public static function isType($mygroups, $type)
    {
        // check
        $query = \OC_DB::prepare('SELECT `gid` FROM `*PREFIX*groups` WHERE `gid` IN (' . MC_Utils::arrayAsSqlString($mygroups) . ') AND type = ?');
        $result = $query->execute(array($type))->fetchOne();
        if ($result) {
            return true;
        }
        return false;
    }


}
