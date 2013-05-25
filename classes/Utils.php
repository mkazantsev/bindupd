<?php

require_once("StringTokenizer.php");

class IOException extends Exception { }

class Utils {
	/**
	 * If there is a type for a given string, returns its internal
	 * representation
	 * 
	 */
	 
	public static /* int */ function strToType(/* map */ $types, /* String */ $type) {
		/* int */ $res = -1;
		foreach ($types as $key => $value) {
			if($value == strtoupper($type))
				$res = $key;
		}

		if ($res == -1)
			throw new InvalidArgumentException($type." is unsupported type");
			
		return $res;
	}

	/**
	 * Returns human-understandable representation of a type
	 * 
	 */
	public static /* string */ function typeToStr(/* map */ $types, /* int */ $type) {
		return $types[$type];
	}

	/**
	 * Checks if line has spaces, tabs, new line and carriage return characters
	 * only
	 *
	 */
	public static /* boolean */ function isBlank(/* string */ $line) {
		/* StringTokenizer */ $st = new StringTokenizer($line);
		return (!$st->hasMoreTokens());
	}
	
	public static /* boolean */ function strStartsWith(/* string */ $line,
		/* string */ $subline) {
		$pos = strpos($line, $subline);
		if ($pos === false)
			return false;
		if ($pos != 0)
			return false;
		return true;
	}
}
?>
