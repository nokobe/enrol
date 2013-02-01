<?php

require "includes/SessionsClass.php";

foreach (array('empty', 'none', 'one', 'big') as $value) {
	print "init for $value\n";
	$s = new Sessions("testing/$value.xml");
	print "load for $value\n";
	$s->load();
#	print "dump for $value\n";
#	$s->debugDump();
	print "save for $value\n";
	$s->save();
}
print "done\n";
exit(0);
$sessions = new Sessions("/Users/mark/Sites/enrol/data/sessions.xml");
$sessions->debugDump();
$sessions->load();
$sessions->debugDump();

?>
