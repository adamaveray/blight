<?php
function build_package($source, $target_dir){
	$name	= basename($source).'.phar';

	try {
		$phar = new Phar($target_dir.'/'.$name, 0, $name);
		$phar->buildFromDirectory($source);
		$phar->setStub('<?php __HALT_COMPILER();');
		unset($phar);

	} catch(\Exception $e){
		echo 'Could not package `'.$name.'` (Exception: '.$e->getMessage().' ['.$e->getFile().'#'.$e->getLine().'])'.PHP_EOL;
		return;
	}

	echo 'Packaged `'.$name.'`'.PHP_EOL;
}

// Build default theme
$default_theme	= __DIR__.'/default-theme/Basic/';
if(is_dir($default_theme)){
	build_package($default_theme, __DIR__.'/blog-data/themes/');
}

// Build additional packages
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

		build_package($dir, $base_dir);
	}
}
