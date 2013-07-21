<?php
function buildPackage($source, $targetDir){
	$name	= basename($source).'.phar';

	try {
		$phar = new Phar($targetDir.'/'.$name, 0, $name);
		$phar->buildFromDirectory($source);
		$phar->setStub('<?php __HALT_COMPILER();');
		unset($phar);

	} catch(\Exception $e){
		echo 'Could not package `'.$name.'` (Exception: '.$e->getMessage().' ['.$e->getFile().'#'.$e->getLine().'])'.PHP_EOL;
		return;
	}

	echo 'Packaged "'.$name.'"'.PHP_EOL;
}

// Build default theme
$defaultTheme	= __DIR__.'/default-theme/Basic/';
if(is_dir($defaultTheme)){
	buildPackage($defaultTheme, __DIR__.'/blog-data/themes/');
}

// Build additional packages
$baseDirs	= array(
	__DIR__.'/blog-data/plugins/',
	__DIR__.'/blog-data/themes/'
);

foreach($baseDirs as $baseDir){
	$dirs	= glob($baseDir.'/*');

	foreach($dirs as $dir){
		if(!is_dir($dir)){
			continue;
		}

		buildPackage($dir, $baseDir);
	}
}
