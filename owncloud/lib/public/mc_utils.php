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
class MC_Utils
{
    /**
     * @brief returns an array in a SQL-compliant string
     */
	public static function arrayAsSqlString( $array ) {
		$str = "";
		$i = 1;
		$count = count( $array );
		foreach ( $array as $item ) {
			$str .= "'" . $item . "'";
			if ( $i < $count )
				$str .= ", ";
			$i++;
		}
		return $str;
	}


	/**
	 * @brief removes element whose value is $needle from $array and returns the resulting array
	 * @note code copied from
	 */
	public static function removeElement( $needle, $array )
	{
		$key = array_search($needle,$array);
		if($key!==false){
			unset($array[$key]);
		}
		return $array;
	}
}