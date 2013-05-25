<?php

include_once("Zone.php");

/*
 * Utilities for view part of system
 *
 */
class ViewUtils {
	public static $regexp_username = "/^[A-Za-z0-9_]+$/";
	public static $regexp_password = "/^[A-Za-z0-9_]+$/";	
	public static $regexp_id = "/^[0-9]+$/";
	public static $regexp_zonename = "/^[A-Za-z0-9\.-]+$/";
	public static $regexp_filename = "/^[A-Za-z0-9\._\-\/]+$/";
	public static $regexp_recname = "/^[A-Za-z0-9\.\-@]+$/";

	/*
	 * Checks if the username and the password are correct
	*/
	static public /* array */ function checkLoginFields($username, $password) {
		$messages = array();
		$doRegExp = true;

		// Data length
		if (strlen($username) < 3 || strlen($username) > 255) {
			$messages[] = "Bad username: 3-255 characters allowed";
			$doRegExp = false;
		}
		if (strlen($password) < 3 || strlen($password) > 255) {
			$messages[] = "Bad password: 3-255 characters allowed";
			$doRegExp = false;	
		}

		// SQL-injections? Bad symbols?
		if ($doRegExp) {
			if (!preg_match(ViewUtils::$regexp_username, $username)) {
				$messages[] = "Bad username: latin letters, numbers and _ are the only allowed symbols";
			}

			if (!preg_match(ViewUtils::$regexp_password, $password)) {
				$messages[] = "Bad password: latin letters, numbers and _ are the only allowed symbols";
			}
		}
		return $messages;
	}
	
	static public /* boolean */ function checkID($id) {
		return preg_match(ViewUtils::$regexp_id, $id);
	}
	
	/*
	 * Checks if zone data is correct
	 */	
	static public /* boolean */ function checkZoneData($type, $name, $fileName) {
		$res = false;
		foreach (Zone::$types as $key => $value) {
			if ($key == $type)
				$res = true;
		}

		if (!preg_match(ViewUtils::$regexp_zonename, $name))
			$res = false;

		if (!preg_match(ViewUtils::$regexp_filename, $fileName))
			$res = false;

		return $res;
	}	
	
	/*
	 * Checks if record data is correct
	 */
	static public /* boolean */ function checkRecordData($name, $ttl, $type, $data)  {
		$res = false;
		foreach (Record::$types as $key => $value) {
			if ($key == $type)
				$res = true;
		}

		if ($ttl != "" && !is_numeric($ttl))
			$res = false;

		if ($name != "" && !preg_match(ViewUtils::$regexp_recname, $name))
			$res = false;

		return $res;
	}

	/*
	 * Checks if directive data is correct
	 */
	static public /* boolean */ function checkDirectiveData($type, $data)  {
		$res = false;
		foreach (Record::$types as $key => $value) {
			if ($key == $type)
				$res = true;
		}

		return $res;
	}
}
?>
