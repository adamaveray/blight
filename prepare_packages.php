<?php
$base_dirs	= array(
	__DIR__.'/blog-data/plugins/',
	__DIR__.'/blog-data/themes/'
);

foreach($base_dirs as $base_dir){
	$dirs	= glob($base_dir.'/*');

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
}
