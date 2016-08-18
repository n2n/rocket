<?php
$pubPath = realpath(dirname(__FILE__));
$appPath = realpath($pubPath . '/../app');
$n2nPath = realpath($pubPath . '/../lib');
$varPath = realpath($pubPath . '/../var');

set_include_path(implode(PATH_SEPARATOR, 
	array($appPath, $n2nPath, get_include_path())));

if (isset($_SERVER['N2N_STAGE'])) {
	define('N2N_STAGE', $_SERVER['N2N_STAGE']);
}

require_once 'n2n/N2N.php';

n2n\N2N::initialize($pubPath, $varPath, array($appPath, $n2nPath));

n2n\N2N::getBatchJobRegistry()->trigger();
$mainController = n2n\N2N::getContextControllerRegistry()->createMainController(); 
$mainController->execute();

n2n\N2N::finalize();

function test($value) {
	if (n2n\N2N::isLiveStageOn()) return;
	echo "\r\n<pre>\r\n";
	var_dump($value);
	echo "</pre>\r\n";
}

// n2n\N2N::getDbhPool()->getDbh()->getLogger()->dump();