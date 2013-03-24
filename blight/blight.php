<?php
/**
 * Blight
 * v0.6
 */
namespace Blight;

define('IS_CLI', (PHP_SAPI === 'cli'));
define('VERBOSE', isset($argv[0]) && in_array('-v', $argv));
if(!isset($_SERVER['REQUEST_TIME_FLOAT'])) $_SERVER['REQUEST_TIME_FLOAT'] = microtime(true);

// Set up environment
date_default_timezone_set('UTC');

require('vendor/autoload.php');
require('src/autoload.php');

function debug_output($message){
	if(!IS_CLI || !VERBOSE){
		return;
	}

	$timestamp	= number_format(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 4, '.', '');
	$memstamp	= floor(memory_get_usage()/1024).'k';

	echo $timestamp.'-'.$memstamp.': '.vsprintf($message, array_slice(func_get_args(), 1)).PHP_EOL;
}

$root_path	= str_replace('phar://', '', dirname(__DIR__)).'/';
$lock_file	= $root_path.'blight-update.lock';

// Setup locking
if(file_exists($lock_file)){
	// Process running
	if(IS_CLI){
		echo 'Already running'.PHP_EOL;
	}
	exit;
}

$result	= touch($lock_file);
if(!$result){
	// Cannot create lock
	if(IS_CLI){
		echo 'Cannot create lock file'.PHP_EOL;
	}

	// Try running anyway
}

register_shutdown_function(function() use($lock_file){
	try {
		unlink($lock_file);
	} catch(Exception $e){
		// Cannot remove lock file
	}
});

$config_file	= $root_path.'config.json';
if(!file_exists($config_file) || isset($_COOKIE[\Blight\Controllers\Install::COOKIE_NAME])){
	// Blog not installed
	if(!isset($_SERVER['REQUEST_URI'])){
		echo 'Blog not installed - view on web to install'.PHP_EOL;
		exit;
	}

	$controller	= new \Blight\Controllers\Install($root_path, __DIR__.'/', $web_path.'/', $config_file);

	if(!isset($_COOKIE[\Blight\Controllers\Install::COOKIE_NAME])){
		$controller->get_page($_SERVER['REQUEST_URI']);
		exit;
	}
	
	// Finished setup - teardown
	$controller->teardown();
}

// Initialise blog
$parser	= new \Blight\Config();
$config	= $parser->unserialize(file_get_contents($config_file));
$config['root_path']	= $root_path;
$blog	= new Blog($config);

// Load posts
$manager	= new Manager($blog);
debug_output('Manager initialised');
$archive	= $manager->get_posts_by_year();
debug_output('Archive built');

// Begin rendering
$renderer	= new Renderer($blog, $manager, $blog->get_theme());
debug_output('Renderer initialised');

	// Render pages
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

	// Render RSS-only posts
	$posts	= $manager->get_posts(array(
		'rss'	=> true
	));
	foreach($posts as $post){
		$renderer->render_post($post);
	}
	debug_output('RSS-only posts rendered');

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

	// Render feeds
	$renderer->render_feeds(array(
		'limit'	=> $blog->get('feed', 'limits', $blog->get('page', 'limits', 15))
	));
	debug_output('Feeds rendered');

	// Render sitemap
	$renderer->render_sitemap(array(
	));
	debug_output('Sitemap rendered');

	// Rendering completed

// Copy theme assets
$renderer->update_theme_assets();
debug_output('Theme assets updated');

// Copy user assets
$renderer->update_user_assets();
debug_output('User assets updated');

// Remove old draft files
$manager->cleanup_drafts();

debug_output('Build time: '.(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']).'s');
debug_output('Peak Memory: '.floor(memory_get_usage()/1024).'KB');
if(IS_CLI){
	echo 'Blog built'.PHP_EOL;
}
