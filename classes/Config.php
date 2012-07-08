<?php

require_once("Utils.php");
require_once("StringTokenizer.php");

require_once("Record.php");
require_once("Directive.php");
require_once("Zone.php");

class Config {
	private /* string */ $fileName;
	private /* string */ $path;
	private /* array of Zones */ $zones;

	private /* string */ function readZone(/* string */ $line,
		/* resource */ &$handle) {
		/* string */ $zoneName = "";
		/* string */ $zoneType = "";
		/* string */ $zoneFileName = "";

		/* boolean */ $endReached = false;

		// read till the end of block
		if (strstr($line, "}")) {
			$endReached = true;
		} else {
			while (!feof($handle)) {
				$next = fgets($handle);
				if ($next === false)
					break;
				$line .= $next." ";
				if (strstr($line, "}")) {
					$endReached = true;
					break;
				}
			}
		}

		if (!$endReached)
			throw new InvalidArgumentException("Brace expected");

		// spaces
		/* string */ $tmp = "";
		for ($i = 0; $i < strlen($line); $i++) {
			$c = $line[$i];
			if ($c == ";" || $c == "{" || $c == "}") {
				$tmp .= " ";
				$tmp .= $c;
				$tmp .= " ";
			} else {
				$tmp .= $c;
			}
		}
		$line = $tmp;

		/* StringTokenizer */ $st = new StringTokenizer($line);

		// zone
		$st->nextToken();

		// "name"
		/* string */ $token = $st->nextToken();
		if ($token[0] != '"')
			throw new InvalidArgumentException("Quotation mark expected");
		$zoneName = substr($token, 1, -1);

		// class (always in or not at all)
		$token = $st->nextToken();
		if (strtolower($token) == "in")
			$token = $st->nextToken();

		// opening brace
		if ($token[0] != '{')
			throw new InvalidArgumentException("Brace or class expected");
		$token = substr($token, 1);

		// statements
		/* boolean */ $filed = false;
		/* boolean */ $typed = false;
		while ($st->hasMoreTokens()) {
			if (strlen($token) == 0)
				$token = $st->nextToken();
			// file statement
			if ($token == "file") {
				if ($filed)
					throw new InvalidArgumentException("Duplicate statement");
				$token = $st->nextToken();
				if ($token[0] != '"')
					throw new InvalidArgumentException(
							"Quotation mark expected");
				/* int */ $pos = strrpos($token, '"');
				if ($pos == 0)
					throw new InvalidArgumentException(
							"Quotation mark expected");
				$zoneFileName = substr($token, 1, $pos-1);
				$token = $st->nextToken();
				if ($token != ";") {
					throw new InvalidArgumentException("Semicolon expected");
				}
				$filed = true;
				$token = "";
				continue;
			}
			// type statement
			if ($token == "type") {
				if ($typed)
					throw new InvalidArgumentException("Duplicate statement");
				$zoneType = $st->nextToken();
				$token = $st->nextToken();
				if ($token != ";")
					throw new InvalidArgumentException("Semicolon expected");
				$typed = true;
				$token = "";
				continue;
			}
			if ($filed && $typed)
				break;
			throw new InvalidArgumentException("Unsupported statement");
		}

		if (!$filed)
			throw new IllegalArgumentException("No file statement found");
		if (!$typed)
			throw new IllegalArgumentException("No type statement found");

		/* string */ $rest = "";

		if ($token != "}") // no }
			throw new InvalidArgumentException("Brace expected");
		$token = $st->nextToken();
		if ($token != ";") // no ;
			throw new InvalidArgumentException("Semicolon expected");

		/* Zone */ $zone = new Zone($zoneType,
			$zoneName,
			$zoneFileName,
			$this->path,
			false);

		$this->zones[] = $zone;

		// something after ;
		while ($st->hasMoreTokens()) {
			$rest .= $st->nextToken();
			if ($st->hasMoreTokens())
				$rest .= " ";
		}

		return $rest;
	}

	private /* void */ function readCfgFile() {
		$file = $this->fileName;
		$fp = fopen($file, 'r');
		if (!$fp)
			throw new IOException("Unable to open ".$file." to read config");
		/* string */ $line = "";
		while (!feof($fp)) {
			/* string */ $next = fgets($fp);
			if ($next === false)
				break;
			
			// // Comments
			if (Utils::strStartsWith(trim($next), "//"))
				continue;
			// # Comments
			if (Utils::strStartsWith(trim($next), "#"))
				continue;
			$line .= $next;
			if (Utils::isBlank($line))
				continue;
			if (Utils::strStartsWith(trim($line), "zone")) {
				$line = $this->readZone($line, $fp)." ";
			}
		}
	}

	public function __construct(/* string */ $fileName,
		/* string */ $path) {
		$this->fileName = $fileName;
		$this->path = $path;
		$this->zones = array();
		$this->readCfgFile();
	}

	public /* boolean */ function isEmpty() {
		return (count($this->zones) == 0);
	}
	
	public /* array */ function getZones() {
		return $this->zones;
	}
	
	public function addZone(/* Zone */ $zone) {
		$this->zones[] = $zone;
	}

	public /* int */ function zonesSize() {
		return count($this->zones);
	}

	public /* Zone */ function getZone(/* int */ $index) {
		return $this->zones[$index];
	}

	public function removeZone(/* int */ $index) {
		unset($this->zones[$index]);
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
		
		/* string */ $res = "";
		$configFile = $fileName;
		$fp = fopen($fileName, 'w');
		if (!$fp)
			throw new IOException("Unable to open ".$configFile." for config saving");
		foreach ($this->zones as $zone) {
			$ret = fputs($fp, $zone->toConfig(" "));
			if (!$ret)
				throw new IOException("Unable to save config in ".$configFile);
			$zone->save();
		}
		fclose($fp);
	}

	public /* string */ function __toString() {
		/* string */ $res = "";
		foreach ($this->zones as $zone) {
			$res .= $zone->toConfig(" ")."\n";
		}
		return $res;
	}
}
?>