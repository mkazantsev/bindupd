<?php

require_once("Utils.php");
require_once("Record.php");
require_once("Directive.php");

class Zone {

	public static $types = array(
		1 => "MASTER",
		2 => "SLAVE",
		3 => "HINT",
		4 => "FORWARD"
	);

	private /* string */ $name;
	private /* int */ $type;
	private /* string */ $fileName;
	private /* string */ $path;
	private /* array of Records */ $records;
	private /* array of Directives */ $directives;

	/**
	 * If there is a zone type for given string, returns its internal
	 * representation
	 * 
	 */
	private static /* int */ function strToType(/* string */ $type) {
		return Utils::strToType(Zone::$types, $type);
	}

	/**
	 * Returns human-understandable representation of the zone type
	 * 
	 */
	private static /* string */ function typeToStr(/* int */ $type) {
		return Utils::typeToStr(Zone::$types, $type);
	}

	private /* string */ function readTillTheEnd(/* resource */ &$handle) {
		/* string */ $res = "";
		/* boolean */ $endReached = false;
		while (!feof($handle)) {
			/* string */ $line = fgets($handle);
			if ($line === false)
				break;

			// Cut comments if any present
			$tmp = strstr($line, ";", true);
			if ($tmp) {
				$line = $tmp;
			}

			$res .= $line."\n";

			// End?
			if (strstr($line, ")")) {
				$endReached = true;
				break;
			}
		}

		if (!$endReached)
			throw new InvalidArgumentException("Closing parentheses expected");

		return $res;
	}

	private /* void */ function fillRecsAndDecs() {
		$file = $this->path.$this->fileName;
		$fp = fopen($file, 'r');

		if (!$fp)
			throw new Exception("File ".$file." not found");

		while (!feof($fp)) {
			/* string */ $line = fgets($fp);
			if ($line === false)
				break;
			if (Utils::isBlank($line))
				continue;

			// Comments
			if ($line[0] == ';')
				continue;

			// Multiline statements
			if (strstr($line, "(")) {
				if (!strstr($line, ")"))
					$line .= readTillTheEnd($fp);
				else if (strpos($line, "(") > strpos($line, ")")) {
					fclose($fp);
					throw new InvalidArgumentException(
							"Unexpected closing parentheses");
				}
			}

			// Directives
			if ($line[0] == '$') {
				try {
					/* Directive */ $dec = new Directive($line);
					$this->directives[] = $dec;
				} catch (InvalidArgumentException $iae) {
					fclose($fp);
					// Wrong file data, something wrong instead of valid
					// directive
					throw new InvalidArgumentException(
						"Directive expected: ".$iae->getMessage()
					);
				}
				continue;
			}

			// Records
			try {
				/* Record */ $r = Record::Record($line);
				$this->records[] = $r;
			} catch (InvalidArgumentException $iae) {
				fclose($fp);
				// Something wrong instead of valid record
				throw new InvalidArgumentException(
					"Record expected:  ".$iae->getMessage()
				);
			}
		}
	}

	private function setValues(/* int */ $type,
		/* string */ $name,
		/* string */ $fileName,
		/* string */ $path,
		/* boolean */ $newZone) {
		$this->type = $type;
		$this->name = $name;
		$this->fileName = $fileName;
		$this->path = $path;
		$this->records = array();
		$this->directives = array();
		if (!$newZone)
			$this->fillRecsAndDecs();
	}

	public /* Zone */ function __construct(/* string */ $type,
		/* string */ $name,
		/* string */ $fileName,
		/* string */ $path,
		/* boolean */ $newZone) {
		$this->setValues(Zone::strToType($type), $name, $fileName, $path, $newZone);
	}

	/**
	 * Serializes zone for config file. Data is delimited with predefined
	 * symbols
	 * 
	 */
	public /* string */ function toConfig(/* string */ $tab) {
		/* string */ $res = "";
		$res .= "zone \"".$this->name."\" {\n";
		$res .= $tab."type ".Zone::typeToStr($this->type).";\n";
		$res .= $tab."file \"".$this->fileName."\";\n";
		$res .= "};\n";
		return $res;
	}

	/**
	 * Serializes zone for a zone file. Data is delimited with predefined
	 * symbols
	 * 
	 */
	public /* string */ function toFile(/* string */ $tab) {
		/* string */ $res = "";
		foreach ($this->directives as $dir) {
			$res .= $dir->toString($tab)."\n";
		}
		foreach ($this->records as $rec) {
			$res .= $rec->toString($tab)."\n";
		}		
		return $res;
	}

	public function addDirective(/* Directive */ $directive) {
		$this->directives[] = $directive;
	}

	public function addRecord(/* Record */ $record) {
		$this->records[] = $record;
	}
	
	public /* array of Directives */ function getDirectives() {
		return $this->directives;
	}
	
	public /* array of Records */ function getRecords() {
		return $this->records;
	}	

	public /* string */ function getName() {
		return $this->name;
	}

	public /* string */ function getFileName() {
		return $this->fileName;
	}

	public /* string */ function getType() {
		return $this->typeToStr($this->type);
	}

	public /* void */ function setName(/* string */ $name) {
		$this->name = $name;
	}

	public /* void */ function setFileName(/* string */ $fileName) {
		$oldFile = $path.$this->fileName;
		$this->fileName = $fileName;
		$newFile = $path.$this->fileName;
		/* boolean */ $changed = rename($oldFile, $newFile);
		if (!$changed)
			throw new Exception("Rename failed");
	}

	public /* void */ function setType(/* string */ $type) {
		$this->type = strToType($type);
	}

	public /* Record */ function getRecord(int $index) {
		return $this->records[$index];
	}
	
	public /* Directive */ function getDirective(int $index) {
		return $this->directives[$index];
	}	

	public /* int */ function recordsSize() {
		return count($this->records);
	}

	public /* void */ function removeRecord(int $index) {
		unset($this->records[$index]);
	}

	public /* int */ function directivesSize() {
		return count($this->directives);
	}

	public /* void */ function removeDirective(int $index) {
		unset($this->directives[$index]);
	}

	public /* void */ function save() {
		$zoneFile = $path.$fileName;
		$fp = fopen($zoneFile, 'w');
		if (!$fp)
			throw new IOException("Unable to open ".$zoneFile." for zone saving");
		$ret = fputs($fp, $this->toFile("  "));
		if (!$ret)
			throw new IOException("Unable to save zone in ".$zoneFile);
		fclose($fp);
	}

	public /* string */ function __toString() {
		return $this->toFile("  ");
	}
}
?>