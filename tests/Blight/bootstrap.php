<?php
global $root_path;
$root_path	= realpath(__DIR__.'/../../').'/';

require($root_path.'/blight/src/autoload.php');

global $config;
$config	= array(
	'root_path'	=>	$root_path,

	'name'	=> 'Test Blog',
	'url'	=> 'http://www.example.com/',
	'description'	=> 'Test blog description',

	'paths'	=> array(
		'posts'			=> 'blog-data/posts/',
		'drafts'		=> 'blog-data/drafts/',
		'templates'		=> 'blog-data/templates/',
		'web'			=> 'www/_blog/',
		'drafts-web'	=> 'www/_drafts/',
		'cache'			=> 'cache/'
	),

	'limits'	=> array(
		'page'	=> 10,
		'home'	=> 20,
		'feed'	=> 20
	),

	'linkblog'	=> array(
		'linkblog'	=> false,
		'link_character'	=> '→',
		'post_character'	=> '★'
	)
);
