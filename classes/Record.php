<?php

require_once("Utils.php");
require_once("StringTokenizer.php");

class Record {
	/**
	 * Class
	 */
	private static $IN = 1;

	/**
	 * Type to string static mapping
	 */			
	public static $types = array(
		1 => "SOA",
		2 => "A",
		3 => "CNAME",
		4 => "AAAA",
		5 => "MX",
		6 => "NS",
		7 => "PTR"
	);

	private /* string */ $name;
	private /* long */ $ttl;
	private /* int */ $rclass;
	private /* int */ $type;
	private /* string */ $data;

	/**
	 * If there is a record type for given string, returns its internal
	 * representation
	 * 
	 */
	private static /* int */ function strToType(/* string */ $type) {
		return Utils::strToType(Record::$types, $type);
	}

	/**
	 * Returns human-understandable representation of the record type
	 * 
	 */
	private static /* string */ function typeToStr(/* int */ $type) {
		return Utils::typeToStr(Record::$types, $type);
	}

	private /* void */ function setValues(/* string */ $name,
		/* long */ $ttl,
		/* int */ $type,
		/* string */ $data) {
		$this->name = $name;
		$this->ttl = $ttl;
		$this->rclass = $IN;
		$this->type = $type;
		$this->data = $data;
	}

	/**
	 * Generic constructor, class will always be IN
	 * 
	 */
	private function __construct(/* string */ $name,
		/* long */ $ttl,
		/* int */ $type,
		/* string */ $data) {
		$this->setValues($name, $ttl, $type, $data);
	}
	
	public static function __callStatic($name, $arguments) {
		if ($name == "Record") {
			if (count($arguments) == 1)
				return Record::Record1($arguments[0]);
			if (count($arguments) == 2)
				return Record::Record2($arguments[0], $arguments[1]);
			if (count($arguments) == 3)
				return Record::Record3($arguments[0], $arguments[1], $arguments[2]);
		}
		return false;
	}

	public static /* Record */ function Record2(/* string */ $type,
		/* string */ $data) {
		$r = new Record("", -1, Record::strToType($type), $data);
		return $r;
	}

	public static /* Record */ function Record3(/* string */ $name,
		/* string */ $type,
		/* string */ $data) {
		$r = new Record($name, -1, Record::strToType($type), $data);
		return $r;
	}

	/**
	 * Deserializes record data and construct new object
	 * 
	 */
	public static /* Record */ function Record1(/* string */ $record) {
		// Any name here?
		/* boolean */ $isName = true;
		if (ctype_space($record[0]))
			$isName = false;
		
		/* StringTokenizer */ $st = new StringTokenizer($record);
		/* int */ $count = $st->countTokens();

		// Wrong data
		if (!$isName && $count < 2)
			throw new InvalidArgumentException("Invalid record string");
		if ($isName && $count < 3)
			throw new InvalidArgumentException("Invalid record string");

		// Parsing
		/* boolean */ $isTTL = false;
		/* boolean */ $isClass = false;
		/* string */ $name = "";
		/* string */ $ttl = "";
		/* string */ $type = "";
		/* string */ $data = "";
		
		if ($isName) {
			$name = $st->nextToken();
		}
		/* string */ $next = $st->nextToken();

		// TTL and Class come unnecessary and in any order
		while ($st->hasMoreTokens()) {
			if ($next == "IN") {
				$next = $st->nextToken();
				$isClass = true;
				if (!$isTTL)
					continue;
				else
					break;
			}
			if (ctype_digit($next[0])) {
				$ttl = $next;
				$isTTL = true;
				$next = $st->nextToken();
				if (!$isClass)
					continue;
				else
					break;
			}
			break;
		}

		// Strict type place
		$type = $next;

		// Data contains whatever it needs, spaces included
		while ($st->hasMoreTokens()) {
			$next = $st->nextToken();
			// Comments ignored
			if ($next[0] == ';')
				break;
			$data .= $next;
			if ($st->hasMoreTokens())
				$data .= "  ";
		}

		// TTL conversion
		/* long */ $longTTL = -1;

		if ($isTTL)
			$longTTL = Record::makeTTL($ttl);

		/* Record */ $r = new Record($name, $longTTL, Record::strToType($type), $data);
		return $r;
	}

	/**
	 * Serializes record
	 * 
	 */
	public /* string */ function toString(/* string */ $tab) {
		/* string */ $res = "%s".$tab."%s".$tab."%s".$tab."%s".$tab."%s";

		/* string */ $ttl = "";

		if ($this->ttl != -1)
			$ttl .= $this->ttl;

		/* string */ $rclass = "";
		if ($this->rclass == $IN)
			$rclass = "IN";

		$res = sprintf($res, $this->name,
			$ttl, $rclass, Record::typeToStr($this->type), $this->data);
		return $res;
	}

	public /* string */ function getName() {
		return $this->name;
	}

	public /* string */ function getType() {
		return Record::typeToStr($this->type);
	}

	public /* int */ function getIntType() {
		return $this->type;
	}

	public /* string */ function getData() {
		return $this->data;
	}

	public /* void */ function setName(/* string */ $name) {
		$this->name = $name;
	}
	
	public /* void */ function setType(/* string */ $type) {
		$this->type = Record::strToType($type);
	}

	public /* void */ function setData(/* string */ $data) {
		$this->data = $data;
	}
	
	public /* int */ function getTTL() {
		return $this->ttl;
	}

	public function setTTL(/* string */ $ttl) {
		$this->ttl = Record::makeTTL($ttl);
	}

	private static /* long */ function makeTTL(/* string */ $ttl) {
		/* long */ $longTTL = -1;

		if ($ttl == "")
			return $longTTL;	
	
		if (!is_numeric($ttl))
			throw new InvalidArgumentException("Illegal TTL value");
		
		$longTTL = (int) $ttl;
		return $longTTL;
	}

	public /* string */ function __toString() {
		return toString("  ");
	}
}
?>
