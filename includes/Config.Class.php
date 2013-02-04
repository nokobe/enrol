<?php
class ConfigClass {
	protected $self = array();

	function set($var, $value) {
		$this->$var = $value;
	}
	function get($var) {
		if (isset($this->$var)) {
			return $this->$var;
		} else {
			die ("config::get($var) Not found\n");
		}
	}
}
?>
