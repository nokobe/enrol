<?php
$notices = htmlentities(file_get_contents("/Users/mbates/Sites/EnrolDemo/data/notices.txt"));
$title = "ITS Yoga";
$base = "..";
$loggedIn = 1;
$username = "Mark";
$admin = 1;

require "header.php";
echo <<<EOF
	Admin View
	<div class="btn-group adminView">
	<button class="btn btn-success disabled">On</button>
	<button class="btn">Off</button>
	</div>
	<div class="btn-group adminView">
	<button class="btn">On</button>
	<button class="btn btn-danger disabled">Off</button>
	</div>
	<div class="tabbable"> <!-- Only required for left/right tabs -->
		<div class="tab-content">
			<div class="tab-pane" id="information">
				$notices
			</div>
			<div class="tab-pane active" id="sessions">
				<!-- begin the big long list of sessions!! -->
				<div class="well well-small">Sessions for week starting Monday 14th January</div>
				<div class="heading">
					<h3>
					3 Feb at 4pm - ITS Building
					</h3>
					<div class="status">
						<button class="btn btn-small btn-success disabled" type=button>Open</button>
					</div>
					<div class="info">
						<!--
						<button class="btn btn-small btn-success" type=button>Open Session</button>
						<button class="btn btn-small btn-warning" type=button>Edit Session</button>
						<button class="btn btn-small btn-warning" type=button>Close Session</button>
						<button class="btn btn-small btn-danger" type=button>Delete Session</button>
						-->
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
						<!--
						<button class="btn btn-small btn-success" type=button>Enrol</button>
						-->
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

				<br />
				<div class="well well-small">Sessions for week starting Monday 14th January</div>

				<div class="heading">
				<h3>
				3 Feb at 4pm - ITS Building
				</h3>
				<div class="info">
				<!--
				<button class="btn btn-small btn-danger" type=button>Un-enrol</button>
				-->
				<button class="btn btn-small btn-link" type=button>Un-enrol</button>
				</div>
				</div>
				<table class="attendance" border="0" cellspacing="5" cellpadding="5">
					<tr>
					<td class="place occupied self">
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
				<br />
				<div class="heading">
				<h3>
				3 Feb at 4pm - ITS Building
				</h3>
				<div class="info">
				<!--
				<button class="btn btn-small btn-danger" type=button>Un-enrol</button>
				-->
				<button class="btn btn-small btn-link" type=button>Un-enrol</button>
				</div>
				</div>
				<table class="attendance" border="0" cellspacing="5" cellpadding="5">
					<tr>
					<td class="place occupied self">
					Mark <i class="icon-trash"></i>
					</td>
					<td class="place occupied">
					Rod <i class="icon-trash"></i>
					</td>
					<td class="place occupied">
					Yogi <i class="icon-trash"></i>
					</td>
					<td class="place occupied">
					Michael <i class="icon-trash"></i>
					</td>
					<td class="place occupied">
					Charlie <i class="icon-trash"></i>
					</td>
					</tr>
					<tr>
					<td class="place occupied">
					Guy <i class="icon-trash"></i>
					</td>
					<td class="place occupied">
					Andrew <i class="icon-trash"></i>
					</td>
					<td class="place occupied">
					Raena <i class="icon-trash"></i>
					</td>
					<td class="place occupied">
					Jo <i class="icon-trash"></i>
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
				<div class="heading">
				<h3>
				3 Feb at 4pm - ITS Building
				</h3>
				<div class="info">
				add me! | edit | delete
				</div>
				</div>
				<table class="attendance" border="0" cellspacing="5" cellpadding="5">
					<tr>
					<td class="place occupied self">
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

				<!-- end the list of sessions -->
			</div>
			<div class="tab-pane" id="admin">
				Some admin content
			</div>
		</div>
	</div>

EOF;
require 'footer.php';
# vim:filetype=html
?>
