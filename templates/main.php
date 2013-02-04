<?php
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
	if ($t->adminView) {
		# show adminview "toggle" as ON
		echo <<<EOT
		<form class="form-inline" method="post" action="admin.php">
		<label>Admin View</label>
		<div class="btn-group adminView">
		<button class="btn btn-success disabled">On</button>
		<button class="btn" name="adminView" value="0">Off</button>
		</div>
EOT;
		if ($t->hideClosedSessions) {
			# ON
			echo <<<EOF
			<label>Hide Closed Sessions</label>
			<div class="btn-group hideClosedSessions">
			<button class="btn btn-success disabled">On</button>
			<button class="btn" name="hideClosedSessions" value="0">Off</button>
			</div>
			</form>
EOF;
		} else {
			# OFF
			echo <<<EOF
			<label>Hide Closed Sessions</label>
			<div class="btn-group hideClosedSessions">
			<button class="btn" name="hideClosedSessions" value="1">On</button>
			<button class="btn btn-danger disabled">Off</button>
			</div>
			</form>
EOF;
		}
	} else {
		# show adminview "toggle" as OFF
		echo <<<EOT
		<form class="form-inline" method="post" action="admin.php">
		<label>Admin View</label>
		<div class="btn-group adminView">
		<button class="btn" name="adminView" value="1">On</button>
		<button class="btn btn-danger disabled">Off</button>
		</div>
EOT;
	}
}
echo <<<EOF
	<div class="tabbable"> <!-- Only required for left/right tabs -->
		<div class="tab-content">
			<div class="tab-pane" id="information">
				$t->notices
			</div>
			<div class="tab-pane active" id="sessions">
				<!-- begin the big long list of sessions!! -->
				<div class="well well-small">Sessions for week starting Monday 14th January</div>

EOF;
foreach ($t->sessions as $s) {
	echo <<<EOS
				<!-- SESSION TEMPLATE -->
				<div class="heading">
					<div class="sessioninfo">
						$s->when - $s->location
					</div>
					<div class="status">
						$s->sessionStatus
					</div>
					<div class="sessionops">
						<form method="post" action="manageSessions.php">
EOS;
	foreach ($s->sessionops as $button) {
		echo $button;
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
	for ($i = 0; $i < $s->maxClassSize; $i++) {
		if ($i < $s->classSize) {
			echo $s->users[$i];
		}
		else {
		echo '<td class="place free"><div class="muted"><small><i>Available</i></small></div></td>';
		}
		if (($i+1) % 5 == 0 and $i < $s->maxClassSize) {
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
				<!-- END SESSION TEMPLATE -->

				<!-- begin the big long list of sessions!! -->
<!--
				<div class="well well-small">Sessions for week starting Monday 14th January</div>
				<div class="heading">
					<h3>
					3 Feb at 4pm - ITS Building
					</h3>
					<div class="status">
						<button class="btn btn-small btn-success disabled" type=button>Open</button>
					</div>
					<div class="info">
						<button class="btn btn-small btn-link" type=button>Open Session</button>
						<button class="btn btn-small btn-link" type=button>Edit Session</button>
						<button class="btn btn-small btn-link" type=button>Close Session</button>
						<button class="btn btn-small btn-link" type=button>Delete Session</button>
					</div>
				</div>
				<table class="attendance table-bordered" border="0" cellspacing="5" cellpadding="5">
					<tr>
						<td class="place occupied self">Mark <i class="icon-trash"></i></td>
						<td class="place occupied">Rod <i class="icon-trash"></i></td>
						<td class="place occupied">Yogi <i class="icon-trash"></i></td>
						<td class="place occupied">Michael <i class="icon-trash"></i></td>
						<td class="place occupied">Charlie <i class="icon-trash"></i></td>
					</tr>
					<tr>
						<td class="place occupied">Guy <i class="icon-trash"></i></td>
						<td class="place occupied">Andrew <i class="icon-trash"></i></td>
						<td class="place occupied">Raena <i class="icon-trash"></i></td>
						<td class="place occupied">Jo <i class="icon-trash"></i></td>
						<td class="place free">Free <i class="icon-plus pull-right"></i></td>
					</tr>
					<tr>
						<td class="place free">
						free
						</td>
						<td class="place free">
						free
						</td>
						<td class="place disabled"></td>
						<td class="place disabled"></td>
						<td class="place disabled"></td>
					</tr>
				</table>
				<br />
				<div class="heading">
					<h3>
					3 Feb at 4pm - ITS Building
					</h3>
					<div class="status">
						<button class="btn btn-small btn-danger disabled" type=button>Closed</button>
					</div>
					<div class="info">
-->
						<!--
						<button class="btn btn-small btn-success" type=button>Enrol</button>
						-->
<!--
						<button class="btn btn-small btn-link" type=button>Enrol</button>
					</div>
				</div>
				<table class="attendance" border="0" cellspacing="5" cellpadding="5">
					<tr>
					<td class="place occupied">
					Mark
					</td>
					<td class="place occupied">
					Rod
					</td>
					<td class="place occupied">
					Yogi
					</td>
					<td class="place occupied">
					Michael
					</td>
					<td class="place occupied">
					Charlie
					</td>
					</tr>
					<tr>
					<td class="place occupied">
					Guy
					</td>
					<td class="place occupied">
					Andrew
					</td>
					<td class="place occupied">
					Raena
					</td>
					<td class="place occupied">
					Jo
					</td>
					<td class="place free">
					Free
					</td>
					</tr>
					<tr>
					<td class="place free">
					free
					</td>
					<td class="place free">
					free
					</td>
					<td class="place disabled"></td>
					<td class="place disabled"></td>
					<td class="place disabled"></td>
					</tr>
				</table>
-->
				<!-- end the list of sessions -->
			</div>
			<div class="tab-pane" id="admin">
				Some admin content
			</div>
		</div>
	</div>

EOF;
require 'templates/footer.php';
# vim:filetype=html:ts=2:sw=2
?>
