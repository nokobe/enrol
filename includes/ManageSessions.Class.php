<?php

/*
 * ManageSessions: manage the Enrol (class) sessions data
 *
 * Provides:
 *	debugDump()		- prints the currently loads xml sessions file
 *	load()			- load the sessions (called by the constructor)
 *	save()			- throws exception if save fails
 * $sessions = getSessions($sid)	- get all sessions (sorted by time)
 * $obj = getSession($sid)	- return the session object
 * $txt = describeSession($sid)	- return a text description of the session
 * $sid = addSession()
 *	removeSession($sid)	- etc
 *	getAttr($sid, $attr)	- NYI
 *	setAttr($sid, $array)	- set the attributes in the given session
 *	enrolUser($sid, $name)	- enrol user in given session
 *	unenrolUser($sid, $name)	- unenrol user in given session
 *	userIsEnrolled($session, $user)	- etc
 *	getClassSize($session)	- etc
 *	classIsFull($session)	- etc
 *	getEnrolled($session)	- etc
 *	resetUSID		- NYI
 * 	
 */

class ManageSessions {
	protected $xml;
	protected $datafile;
	protected $lastMod;

	function __construct($file) {
		$this->datafile = $file;
		$this->xml = "";
		$this->lastMod = "";

//		$this->load();
	}

	private static function _sort_sessions_by_time($a, $b) {
		if ((int)$a->when == (int)$b->when) {
			return 0;
		}
		return ((int)$a->when < (int)$b->when) ? -1 : 1;
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
				$this->xml = simplexml_load_file($this->datafile) or die ("Unable to load XML file ($this->datafile!)");
			} catch (Exception $e) {
				echo "saved from ", $e->getMessage(), "\n";
				die("but die anyway\n");
			}
		}
		$this->lastMod = filemtime($this->datafile);

		// NOTE: load file and get file mod time really should be an ATOMIC operation
	}

	function save() {
		if (filemtime($this->datafile) != $this->lastMod) {
			throw new Exception('Sessions file has changed since being loaded');
		}

		$bakfile = "$this->datafile.bak";
		if (! copy ( $this->datafile, $bakfile ) ) {
			Logger::logWarn("backup sessions file failed");
		}
		if (! $this->xml->asXML( $this->datafile )) {
		       	throw new Exception("Unable to save Sessions file");
		}
	}

	function getSessions() {
		$this->load();
		$all = $this->xml->xpath('/sessions/session');
		usort($all, 'ManageSessions::_sort_sessions_by_time');
		return $all;
	}

	function getSession($sid) {
		$this->load();
		list($s) = $this->xml->xpath("/sessions/session[usid=$sid]");
		return $s;
	}

	function describeSession($sid) {
		$this->load();
		$s = $this->getSession($sid);
		return "when => ".date('g:ia \o\n l jS F, Y', (int)$s->when). ", location => $s->location, maxusers => $s->maxusers, active => $s->active";
	}

	function addSession($attributes) {
		$this->load();

		$newID = (int) $this->xml->nextID;
		$new = $this->xml->addChild('session');
		$new->addChild("usid", $newID);
		$new->addChild("active", $attributes["active"]);
		$new->addChild("when", $attributes["when"]);
		$new->addChild("location", $attributes["location"]);
		$new->addChild("maxusers", $attributes["maxusers"]);
		$this->xml->nextID = $newID + 1;

		$this->save();
		Logger::logDebug("[session: $newID] create-session: [ when => '$attributes[when]', location => '".$attributes['location']."', max => '".$attributes['maxusers']."' ]");
		return $newID;
	}

	function removeSession($sid) {
		$s = $this->getSession($sid);
		$details = "when => $s->when, location => $s->location, max => $s->maxusers";
		$this->load();

		// ==================== convert to DOM ====================
		$dom = new DOMDocument();
		$dom->loadXML($this->xml->asXML());

		// ==================== select the session to delete ====================
		$xpath = new DomXpath($dom);
		$session = $xpath->query("//sessions/session[usid=$sid]");

		// ==================== delete element ====================
		$session->item(0)->parentNode->removeChild($session->item(0));

		// ==================== convert back to XML object ====================
		$this->xml = new SimpleXMLElement($dom->saveXML());

		$this->save();
		Logger::logDebug("[session: $sid] delete-session [ $details ]");
	}

	function getAttr($sid, $attr) {
	}
	
	function setAttr($sid, $array) {
		$this->load();

		$s = $this->getSession($sid);
		foreach ($array as $key => $value) {
			$s->$key = $value;
			Logger::logDebug("Set Attr: $key => $value");
		}
		$this->save();
		$details = implodeAssoc(' => ', ', ', $array);
		Logger::logDebug("[session: $sid] edit-session [ $details ]");
	}

	/*
	 * throws Exception on failure
	 */
	function enrolUser($sid, $name) {
		$this->load();

		$s = $this->getSession($sid);
		if ($this->userIsEnrolled($s, $name)) {
			throw new Exception('already enrolled');
		}
		if ($this->classIsFull($s)) {
			throw new Exception('class is full');
		}
		$who = $this->getEnrolled($s);
		$who[] = $name;
		$s->userlist = implode('|', $who);

		$this->save();
		Logger::logDebug("[session: $sid] enrol $name");
	}

	/*
	 * throws Exception on failure
	 */
	function unenrolUser($sid, $name) {
		$this->load();

		$s = $this->getSession($sid);
		if (! $this->userIsEnrolled($s, $name)) {
			throw new Exception('not enrolled');
		}
		$who = $this->getEnrolled($s);
		if ($who === FALSE) {
			throw new Exception("couldn't find $name in $who");
		}
		Logger::logDebug("ok. unenrol $name from $sid");
		$index = array_search($name, $who);
		unset ( $who[$index] );
		Logger::logDebug("unsetting who[$index]");
		$s->userlist = implode('|', $who);

		$this->save();
		Logger::logDebug("[session: $sid] unenrol $name");
	}

	function resetUSID() {
		$this->load();

		if (count( $xml->xpath('/sessions/session') ) > 0) {
			errorPage("Cannot reset SessionID whilst there are still sessions");
		} else {
			$xml->nextID = 1;
			$save_changes = 1;
		}
	}

	/* The session "object" itself doesn't have it's own class (for legacy reasons),
	 * so we just provide a bunch of static functions to mimic a real class :) */

	static function userIsEnrolled($session, $user) {
		if ($session->userlist == "") {
			return FALSE;
		}
		$users = explode("|", $session->userlist);
		return array_search($user, $users) === FALSE ? FALSE : TRUE;
	}

	static function getClassSize($session) {
		if ($session->userlist == "") {
			return 0;
		}
		return count(explode("|", $session->userlist));
	}

	static function classIsFull($session) {
		return $session->maxusers == ManageSessions::getClassSize($session);
	}

	static function getEnrolled($session) {
		if ($session->userlist == "") {
			return array();
		}
		return explode("|", $session->userlist);
	}
}

?>
