<?php
$version = $t->version;
echo <<<EOF
	</div>
</div>
<hr>
<footer>
	<div class="container">
		<p class="muted credit">Enrol Software $version by <a href="http://github.com/nokobe">Mark Bates</a></p>
	</div>
</footer>
<script src="$t->base/js/bootstrap.min.js" type="text/javascript" charset="utf-8"></script>
EOF;

/* ======================= SHOW ANY STATUS ALERTS ======================= */
if (isset($status)) {
	if ($status) {
		foreach ($status as $m) {
			#echo "<div class='info'>$m</div>";
			echo "<script language=\"javascript\" type=\"text/javascript\">";
			echo "alert('$m')\n";
			echo "</script>";
		}
	}
}
/* ======================= SHOW ANY MESSAGES ======================= */
while (($m = SessionMgr::getMessage()) != "") {
	echo "<script language=\"javascript\" type=\"text/javascript\">";
	echo "alert('$m')\n";
	echo "</script>";
}

echo <<<EOF
</body>
</html>
EOF;

# vim:filetype=html
?>
