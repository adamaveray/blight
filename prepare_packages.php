<?php
$base_dir	= __DIR__.'/blog-data/plugins/';
$dirs		= glob($base_dir.'/*');

$phar_dirs	= array();

foreach($dirs as $dir){
	if(!is_dir($dir)){
		continue;
	}

	$name	= basename($dir).'.phar';

	try {
		$phar = new Phar($base_dir.'/'.$name, 0, $name);
		$phar->buildFromDirectory($dir);
		$phar->setStub('<?php __HALT_COMPILER();');
		unset($phar);

	} catch(\Exception $e){
		echo 'Could not package `'.$name.'` (Exception: '.$e->getMessage().' ['.$e->getFile().'#'.$e->getLine().'])'.PHP_EOL;
		continue;
	}

	echo 'Packaged `'.$name.'`'.PHP_EOL;
}
