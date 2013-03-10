<?php
$name	= 'Blight.phar';

$path_source	= __DIR__.'/blight';
$path_build		= __DIR__;

try {
	if(!is_dir($path_build)){
		mkdir($path_build);
	}

	$phar = new Phar($path_build.'/'.$name, 0, $name);
	$phar->buildFromDirectory($path_source);
	$phar->setStub(file_get_contents($path_source.'/phar-stub.php'));
	unset($phar);

} catch(\Exception $e){
	exit('Exception: '.$e->getMessage().' ('.$e->getFile().'#'.$e->getLine().')');
}
