<?php
/**
 * Blight
 * v0.2
 */
namespace Blight;

define('IS_CLI', (PHP_SAPI === 'cli'));
define('VERBOSE', isset($argv[0]) && in_array('-v', $argv));
if(!isset($_SERVER['REQUEST_TIME_FLOAT'])) $_SERVER['REQUEST_TIME_FLOAT'] = microtime(true);

// Set up environment
date_default_timezone_set('UTC');
require('src/autoload.php');

function debug_output($message){
	if(!IS_CLI || !VERBOSE){
		return;
	}

	$timestamp	= number_format(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 4, '.', '');
	$memstamp	= floor(memory_get_usage()/1024).'k';

	echo $timestamp.'-'.$memstamp.': '.vsprintf($message, array_slice(func_get_args(), 1)).PHP_EOL;
}

// Initialise blog
$root_path	= dirname(__DIR__).'/';
$config	= array_merge(parse_ini_file($root_path.'config.ini', true), array(
	'root_path'	=> $root_path
));
$blog	= new Blog($config);


// Check install
if(!is_dir($blog->get_path_posts())){
	require('src/setup.php');
}


// Load posts
$manager	= new Manager($blog);
debug_output('Manager initialised');
$archive	= $manager->get_posts_by_year();
debug_output('Archive built');

// Begin rendering
$renderer	= new Renderer($blog, $manager);
debug_output('Renderer initialised');

	// Render posts and archives
	foreach($archive as $year){
		/** @var \Blight\Collections\Year $year */
		// Render posts
		foreach($year as $post){
			$renderer->render_post($post);
		}
		debug_output('Year "%s" posts rendered', $year->get_name());

		// Render archive
		$renderer->render_year($year, array(
			'per_page'	=> $blog->get('page', 'limits', 0)
		));
		debug_output('Year "%s" archive rendered', $year->get_name());
	}

	// Render tag pages
	$renderer->render_tags(array(
		'per_page'	=> $blog->get('page', 'limits', 0)
	));
	debug_output('Tags rendered');

	// Render category pages
	$renderer->render_categories(array(
		'per_page'	=> $blog->get('page', 'limits', 0)
	));
	debug_output('Categories rendered');

	// Render home page
	$renderer->render_home(array(
		'limit'	=> $blog->get('home', 'limits', $blog->get('page', 'limits', 10))
	));
	debug_output('Home rendered');

	// Render feed
	$renderer->render_feed(array(
		'limit'	=> $blog->get('feed', 'limits', $blog->get('page', 'limits', 15))
	));
	debug_output('Feed rendered');

// Rendering completed
debug_output('Build time: '.(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']).'s');
debug_output('Peak Memory: '.floor(memory_get_usage()/1024).'KB');
if(IS_CLI){
	echo 'Blog built'.PHP_EOL;
}
