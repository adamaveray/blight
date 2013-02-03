<?php
/** @var $blog \Blight\Blog */

$file_system	= $blog->get_file_system();

// Set up directories
$dirs	= array(
	$blog->get_path_posts(),
	$blog->get_path_templates()
);
foreach($dirs as $dir){
	$file_system->create_dir($dir);
}

// Copy .htaccess
$file_system->create_file(rtrim($web_path,'/').'/.htaccess', $file_system->load_file($blog->get_path_app('src/default.htaccess')));

// Update config with domain
$path		= $blog->get_path_root('config.ini');
$content	= $file_system->load_file($path);
$find	= 'http://www.example.com/';
if(strstr($find) !== false){
	$file_system->create_file($path, str_replace($find, $_SERVER['SERVER_NAME'], $content));
}

echo 'Blog set up. Create templates and reload.';
exit;
