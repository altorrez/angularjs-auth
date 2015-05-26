<?php 

// Simpliest autoloader ever
function __autoload($class_name) {

	$core_file = ROOT.DS.str_replace('\\', DS, $class_name).'.php';
	require $core_file;
}