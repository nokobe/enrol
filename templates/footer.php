<?php
$version = $c->get('version');
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
</body>
</html>
EOF;

# vim:filetype=html
?>
