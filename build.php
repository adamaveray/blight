<?php
$dir	= 'blight';
$name	= 'Blight.phar';

$pathSource	= __DIR__.'/'.$dir;
$pathBuild		= __DIR__;

try {
	if(!is_dir($pathBuild)){
		mkdir($pathBuild);
	}

	$phar = new Phar($pathBuild.'/'.$name, 0, $name);
	$phar->buildFromDirectory($pathSource);

	// Update Composer to handle Phar
	$path	= 'vendor/composer/autoload_classmap.php';
	if(file_exists(__DIR__.'/'.$dir.'/'.$path)){
		$content	= file_get_contents(__DIR__.'/'.$dir.'/'.$path);
		$content	= str_replace('/'.$dir.'/', '/'.$name.'/', $content);
		$phar->addFromString($path, $content);
	}

	$phar->setStub(file_get_contents($pathSource.'/phar-stub.php'));
	unset($phar);

} catch(\Exception $e){
	exit('Exception: '.$e->getMessage().' ('.$e->getFile().'#'.$e->getLine().')');
}
