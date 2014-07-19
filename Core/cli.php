<?php
// $_GET
$cli_argv = $_SERVER['argv'];
for ( $i = 2, $l = $_SERVER['argc']; $i < $l; $i += 2 ) {
	$_REQUEST[$cli_argv[$i - 1]] = $_GET[$cli_argv[$i - 1]] = $cli_argv[$i];
}
