<?php
function exception_handler($e) {
	errorPage($e->getMessage());
}

set_exception_handler('exception_handler');
?>
