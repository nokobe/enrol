<?php
/*
 * this template requires:
 *		$t->isAdmin
 *		$t->adminView
 *		$t->hideClosedSessions
 *		$t->notices
 *		$t->sessions
 */

require "templates/header.php";
if ($c->get('debug')) {
	echo "<pre>";
	echo "Session:";
	print_r($_SESSION);
	echo "Post:";
	print_r($_POST);
	echo "</pre>";
}

if ($t->isAdmin) {
	echo '<form class="form-inline" method="post" action="admin.php">';
	if ($t->adminView) {
		# show adminview "toggle" as ON
		echo <<<EOT
		<label>Show Admin Functions</label>
		<div class="btn-group adminView">
		<button class="btn btn-small btn-success disabled">On</button>
		<button class="btn btn-small" name="adminView" value="0">Off</button>
		</div>
		<div class="well">
EOT;
		if ($t->hideClosedSessions) {
			# ON
			echo <<<EOF
			<label>Hide Closed Sessions</label>
			<div class="btn-group hideClosedSessions">
			<button class="btn btn-small btn-success disabled">On</button>
			<button class="btn btn-small" name="hideClosedSessions" value="0">Off</button>
			</div>
EOF;
		} else {
			# OFF
			echo <<<EOF
			<label>Hide Closed Sessions</label>
			<div class="btn-group hideClosedSessions">
			<button class="btn btn-small" name="hideClosedSessions" value="1">On</button>
			<button class="btn btn-small btn-danger disabled">Off</button>
			</div>
EOF;
		}
		echo <<<LINE
		&nbsp; &nbsp;
		<a href="createSession.php" class="btn btn-small" type="submit" name="Action" value="create-session">Create New Session</a>
		&nbsp; &nbsp;
		<a href="manageNotices.php" class="btn btn-small" type="submit" name="Action" value="edit-notices">Edit Notices</a>
		</div>
LINE;
	} else {
		# show adminview "toggle" as OFF
		echo <<<EOT
		<label>Show Admin Functions</label>
		<div class="btn-group adminView">
		<button class="btn btn-small" name="adminView" value="1">On</button>
		<button class="btn btn-small btn-danger disabled">Off</button>
		</div>
EOT;
	}
	echo '</form>';
}
echo <<<EOF
	<div class="tabbable"> <!-- Only required for left/right tabs -->
		<div class="tab-content">
			<div class="tab-pane" id="information">
				<div class="well">
					$t->notices
				</div>
			</div>
			<div class="tab-pane" id="sessions">
				<!-- begin the big long list of sessions!! -->
EOF;
$prevWeekNumber = "";
foreach ($t->sessions as $s) {
	$thisWeekNumber = date("W", $s->when);
	if ($thisWeekNumber != $prevWeekNumber) {
		$mondaystr = weeknumber2monday($this_week_number, $s->when);
		echo <<<EOT
<!--<div class="well well-small alert alert-info">Sessions for week starting $mondaystr</div> -->
<hr>
<h3 class="text-center">Sessions for week starting $mondaystr</h3>
EOT;
	}
	$prevWeekNumber = $thisWeekNumber;
	echo <<<EOS
				<div class="heading">
					<div class="sessioninfo">
						$s->whenstr - $s->location ($s->maxClassSize places)
					</div>
					<div class="status">
						$s->sessionStatus
					</div>
					<div class="sessionops">
						<form method="post" action="manageSessions.php">
EOS;
	foreach ($s->sessionops as $button) {
		echo $button;
		echo "&nbsp;";
	}
	echo <<<EOS
							<!-- <input name="sessionID" value="$s->usid"> -->
							<input type="hidden" name="USID" value="$s->usid">
						</form>
					</div>
				</div>
				<table class="attendance table-bordered table-condensed" border="0" cellspacing="5" cellpadding="5">
EOS;
	$index = 0;
	echo "<tr>";
	for ($i = 0; $i < $s->numelements; $i++) {
		if ($i < $s->classSize) {
			echo $s->users[$i];
		}
		else if ( $i < $s->maxClassSize ) {
			echo '<td class="place free"><div class="muted"><small>Available</small></div></td>';
		}
		else {
			echo '<td class="place disabled"><div class="muted"><small>Available</small></div></td>';
		}
		if (($i+1) % 6 == 0 and $i + 1 < $s->maxClassSize) {
			echo "</tr>";
			echo "<tr>";
		}
	}
	echo "</tr>";
	echo <<<EOS
				</table>
				<br />
EOS;
}
echo <<<EOF
			</div>
		</div>
	</div>

EOF;
require 'templates/footer.php';
# vim:filetype=html:ts=2:sw=2
?>
