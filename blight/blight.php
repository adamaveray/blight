<?php
/**
 * Blight
 * v0.4
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

$root_path		= str_replace('phar://', '', dirname(__DIR__)).'/';
$config_file	= $root_path.'config.json';
if(!file_exists($config_file)){
	// Blog not installed
	if(isset($_SERVER['REQUEST_URI'])){
		$controller	= new \Blight\Controllers\Install($root_path, $config_file);

		$controller->get_page($_SERVER['REQUEST_URI']);
	} else {
		echo 'Blog not installed - view on web to install'.PHP_EOL;
	}
	exit;
}

// Initialise blog
$parser	= new \Blight\Config();
$config	= $parser->parse(file_get_contents($config_file));
$config['root_path']	= $root_path;
$blog	= new Blog($config);


// Load posts
$manager	= new Manager($blog);
debug_output('Manager initialised');
$archive	= $manager->get_posts_by_year();
debug_output('Archive built');

// Begin rendering
$renderer	= new Renderer($blog, $manager);
debug_output('Renderer initialised');

	// Render draft posts
	$renderer->render_pages();
	debug_output('Pages rendered');

	// Render draft posts
	$renderer->render_drafts();
	debug_output('Drafts rendered');

	// Render posts and archives
	foreach($archive as $year){
		/** @var \Blight\Collections\Year $year */
		// Render posts
		$posts		= $year->get_posts();
		$no_posts	= count($posts);
		for($i = 0; $i < $no_posts; $i++){
			$prev	= (isset($posts[$i+1]) ? $posts[$i+1] : null);
			$next	= (isset($posts[$i-1]) ? $posts[$i-1] : null);
			$renderer->render_post($posts[$i], $prev, $next);
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

	// Render sitemap
	$renderer->render_sitemap(array(
	));
	debug_output('Sitemap rendered');

// Rendering completed

$manager->cleanup_drafts();

debug_output('Build time: '.(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']).'s');
debug_output('Peak Memory: '.floor(memory_get_usage()/1024).'KB');
if(IS_CLI){
	echo 'Blog built'.PHP_EOL;
}
