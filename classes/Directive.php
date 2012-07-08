<?php

require_once("Utils.php");
require_once("StringTokenizer.php");

class Directive {

	private /* int */ $type;
	private /* string */ $data;

	/**
	 * Type to string static mapping
	 */
	public static $types = array(
		1 => "\$TTL",
		2 => "\$ORIGIN"
	);

	/**
	 * If there is a directive type for given string, get its internal
	 * representation
	 * 
	 */
	private static /* int */ function strToType(/* string */ $type) {
		return Utils::strToType($types, $type);
	}

	/**
	 * Get human-understandable representation of the directive type
	 * 
	 */
	private static /* string */ function typeToStr(/* int */ $type) {
		return Utils::typeToStr(types, type);
	}

	public function __construct(/* string */ $type,
		/* string */ $data) {
		$this->type = strToType($type);
		$this->data = $data;
	}

	/**
	 * Deserializes directive data and constructs new object
	 * 
	 * @param directive
	 *            Directive represented as defined in RFC-1035
	 */
	public static /* Directive */ function Directive(/* string */ $directive) {
		/* StringTokenizer */ $st = new StringTokenizer($directive);
		/* int */ $count = $st->countTokens();
		if ($count < 2)
			throw new InvalidArgumentException("Invalid directive string");
		/* string */ $type = $st->nextToken();
		$this->type = strToType($type);
		/* string */ $data = "";
		while ($st->hasMoreTokens()) {
			$data .= $st->nextToken();
			if ($st->hasMoreTokens())
				$data .= "  ";
		}
		$this->data = $data;
	}

	/**
	 * Directive serializer
	 * 
	 */
	public /* string */ function toString(/* string */ $tab) {
		/* string */ $res = typeToStr($this->type).$tab.$this->data;
		return res;
	}

	public /* void */ function setType(/* string */ $type) {
		$this->type = strToType($type);
	}

	public /* void */ function setData(/* string */ $data) {
		$this->data = $data;
	}

	public /* string */ function __toString() {
		return toString("  ");
	}
}
?>