<?php

class Sessions {
	protected $xml;
	protected $datafile;
	protected $lastMod;

	function __construct($file) {
		$this->datafile = $file;
		$this->nextID = -1;
		$this->xml = "";
		$this->lastMod = "";
	}

	function debugDump() {
		print "datafile = $this->datafile\n";
		print_r($this->xml);
	}

	function load() {
		if (!file_exists($this->datafile)) {
			die ("fatal: missing sessions file ($this->datafile)");
		}
		if (filesize($this->datafile) == 0) {
			// simplexmlelement() doesn't like empty files, so let's handle this case.
			$this->xml = new SimpleXMLElement("<sessions><nextID>1</nextID></sessions>");
		} else {
			try {
				$this->xml = simplexml_load_file($this->datafile) or die ("Unable to load XML file ($this->datafile!");
			} catch (Exception $e) {
				echo "saved from ", $e->getMessage(), "\n";
				die("but die anyway\n");
			}
		}
		$this->lastMod = filemtime($this->datafile);

		// NOTE: load file and get file mod time really should be an ATOMIC operation
	}

	/*
	 * throws exception if save fails
	 */

	function save() {
		if (filemtime($this->datafile) != $this->lastMod) {
			throw new Exception('Sessions file has changed since being loaded');
		}

		$bakfile = "$this->datafile.bak";
		if (! copy ( $this->datafile, $bakfile ) ) {
			log_event("WARNING: backup sessions file failed");
		}
		if (! $this->xml->asXML( $this->datafile )) {
		       	throw new Exception("Unable to save Sessions file");
		}
	}

	function addSession($sid) {
	}

	function removeSession($sid) {
	}

	function getAttr($sid, $attr) {
	}
	
	function setAttr($sid, $array) {
	}

	function addUser($sid, $name) {
	}

	function rmUser($sid, $name) {
	}
}

?>
