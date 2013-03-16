<?php
date_default_timezone_set('UTC');

global $root_path;
$root_path	= realpath(__DIR__.'/../../').'/';

require('phar://'.$root_path.'Blight.phar/src/autoload.php');

global $config;
$config	= array(
	'root_path'	=>	$root_path,

	'site'	=> array(
		'name'	=> 'Test Blog',
		'url'	=> 'http://www.example.com/',
		'description'	=> 'Test blog description',
	),

	'paths'	=> array(
		'pages'			=> 'blog-data/pages/',
		'posts'			=> 'blog-data/posts/',
		'drafts'		=> 'blog-data/drafts/',
		'templates'		=> 'blog-data/templates/',
		'plugins'		=> 'blog-data/plugins/',
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
