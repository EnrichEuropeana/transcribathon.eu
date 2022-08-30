<?php
if ((strpos(shell_exec('/usr/local/apache/bin/apachectl -l'), 'mod_rewrite') !== false) == 0) {
	echo '0';
}
?>