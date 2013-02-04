<?php

/*
 * Sessions: manage the Enrol (class) sessions data
 *
 * Provides:
 *	debugDump()	- prints the currently loads xml sessions file
 *	load()		- load the sessions (called by the constructor)
 *	save()		- throws exception if save fails
 *	getSession($sid)	- NYI
 *	addSession($sid)	- NYI
 *	removeSession($sid)	- NYI
 *	getAttr($sid, $attr)	- NYI
 *	setAttr($sid, $array)	- NYI
 *	addUser($sid, $name)	- NYI
 *	rmUser($sid, $name)	- NYI
 * 	
 */

class Sessions {
	protected $xml;
	protected $datafile;
	protected $lastMod;

	function __construct($file) {
		$this->datafile = $file;
		$this->nextID = -1;
		$this->xml = "";
		$this->lastMod = "";

		$this->load();
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
		// SORT!
		$this->lastMod = filemtime($this->datafile);

		// NOTE: load file and get file mod time really should be an ATOMIC operation
	}

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

	function getSession($sid) {
		list($s) = $this->xml->xpath("/sessions/session[usid=$sid]");
		return $s;
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
