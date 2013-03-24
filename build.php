<?php
$dir	= 'blight';
$name	= 'Blight.phar';

$path_source	= __DIR__.'/'.$dir;
$path_build		= __DIR__;

try {
	if(!is_dir($path_build)){
		mkdir($path_build);
	}

	$phar = new Phar($path_build.'/'.$name, 0, $name);
	$phar->buildFromDirectory($path_source);

	// Update Composer to handle Phar
	$path	= 'vendor/composer/autoload_classmap.php';
	if(file_exists(__DIR__.'/'.$dir.'/'.$path)){
		$content	= file_get_contents(__DIR__.'/'.$dir.'/'.$path);
		$content	= str_replace('/'.$dir.'/', '/'.$name.'/', $content);
		$phar->addFromString($path, $content);
	}

	$phar->setStub(file_get_contents($path_source.'/phar-stub.php'));
	unset($phar);

} catch(\Exception $e){
	exit('Exception: '.$e->getMessage().' ('.$e->getFile().'#'.$e->getLine().')');
}
