<?php
date_default_timezone_set('UTC');

global $rootPath;
$rootPath	= realpath(__DIR__.'/../../').'/';

require('phar://'.$rootPath.'Blight.phar/vendor/autoload.php');
require('phar://'.$rootPath.'Blight.phar/src/autoload.php');

global $config;
$config	= array(
	'root_path'	=>	$rootPath,

	'author'	=> 'Sam Pell',

	'site'	=> array(
		'name'	=> 'Test Blog',
		'url'	=> 'http://www.example.com/',
		'description'	=> 'Test blog description',
		'timezone'		=> 'America/New_York'
	),

	'paths'	=> array(
		'pages'			=> 'blog-data/pages/',
		'posts'			=> 'blog-data/posts/',
		'drafts'		=> 'blog-data/drafts/',
		'themes'		=> 'blog-data/themes/',
		'plugins'		=> 'blog-data/plugins/',
		'assets'		=> 'blog-data/assets/',
		'web'			=> 'www/_blog/',
		'drafts-web'	=> 'www/_drafts/',
		'cache'			=> 'cache/'
	),

	'limits'	=> array(
		'page'	=> 10,
		'home'	=> 20,
		'feed'	=> 20
	),

	'posts'	=> array(
		'default_extension'	=> 'md',
		'allow_txt'	=> false
	),

	'linkblog'	=> array(
		'linkblog'	=> false,
		'link_character'	=> '→',
		'post_character'	=> '★'
	)
);
