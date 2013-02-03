<?php
spl_autoload_register(function($class){
	$components	= preg_split('/([\\\_])/', $class);

	if($components[0] === 'Blight'){
		$components[0]	= 'src';
	} else {
		array_unshift($components, 'libs');
	}

	$path	= dirname(__DIR__).'/'.implode('/', $components).'.php';
	if(!file_exists($path)){
		$dir	= dirname($path);
		$file	= pathinfo($path, \PATHINFO_FILENAME);
		$new_path	= $dir.'/'.$file.'/'.$file.'.php';

		if(!file_exists($new_path)){
			throw new \Exception('Class "'.$class.'" not found at '.$new_path);
		}

		$path	= $new_path;
	}

	require_once($path);
});

