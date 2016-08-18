<?php

use n2n\core\TypeLoader;
use n2n\io\fs\AbstractPath;

$pubPath = realpath(dirname(__FILE__));
$appPath = realpath($pubPath . '/../app');
$n2nPath = realpath($pubPath . '/../lib');
$varPath = realpath($pubPath . '/../var');

set_include_path(implode(PATH_SEPARATOR,
		array($appPath, $n2nPath, get_include_path())));

require_once 'n2n/N2N.php';

n2n\N2N::initialize($pubPath, $varPath, array($appPath, $n2nPath));

//

load();

n2n\N2N::finalize();

function test($value) {
	if (n2n\N2N::isDevelopmentModeOn()){
		echo "\r\n<pre>\r\n";
		print_r($value);
		if (is_scalar($value)) echo "\r\n";
		echo "</pre>\r\n";
	}
}


function load() {
	foreach (TypeLoader::getIncludePaths() as $includePath) {
		
		$abstractIncludePath = new AbstractPath($includePath);
		foreach ($abstractIncludePath->getDecendents('*.php') as $path) {
			$path = (string)$path;
			
			$typeName = TypeLoader::pathToTypeName($path);
			
			if (TypeLoader::isTypeLoaded($typeName)) continue;
			
			@include_once $path;
			if (TypeLoader::isTypeLoaded($typeName)) {
				$class = new ReflectionClass($typeName);
				if (!$class->isInterface() && $class->isSubclassOf('n2n\\http\\Controller')) {
					test($typeName);
				}
			}
			
		}
	}
}