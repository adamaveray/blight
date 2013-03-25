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

function debugOutput($message){
	if(!IS_CLI || !VERBOSE){
		return;
	}

	$timestamp	= number_format(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 4, '.', '');
	$memstamp	= floor(memory_get_usage()/1024).'k';

	echo $timestamp.'-'.$memstamp.': '.vsprintf($message, array_slice(func_get_args(), 1)).PHP_EOL;
}

$rootPath	= str_replace('phar://', '', dirname(__DIR__)).'/';
$lockFile	= $rootPath.'blight-update.lock';

// Setup locking
if(file_exists($lockFile)){
	// Process running
	if(IS_CLI){
		echo 'Already running'.PHP_EOL;
	}
	exit;
}

$result	= touch($lockFile);
if(!$result){
	// Cannot create lock
	if(IS_CLI){
		echo 'Cannot create lock file'.PHP_EOL;
	}

	// Try running anyway
}

register_shutdown_function(function() use($lockFile){
	try {
		unlink($lockFile);
	} catch(Exception $e){
		// Cannot remove lock file
	}
});

$configFile	= $rootPath.'config.json';
if(!file_exists($configFile) || isset($_COOKIE[\Blight\Controllers\Install::COOKIE_NAME])){
	// Blog not installed
	if(!isset($_SERVER['REQUEST_URI'])){
		echo 'Blog not installed - view on web to install'.PHP_EOL;
		exit;
	}

	$controller	= new \Blight\Controllers\Install($rootPath, __DIR__.'/', $webPath.'/', $configFile);

	if(!isset($_COOKIE[\Blight\Controllers\Install::COOKIE_NAME])){
		$controller->getPage($_SERVER['REQUEST_URI']);
		exit;
	}
	
	// Finished setup - teardown
	$controller->teardown();
}

// Initialise blog
$parser	= new \Blight\Config();
$config	= $parser->unserialize(file_get_contents($configFile));
$config['root_path']	= $rootPath;
$blog	= new Blog($config);

// Load posts
$manager	= new Manager($blog);
debugOutput('Manager initialised');
$archive	= $manager->getPostsByYear();
debugOutput('Archive built');

// Begin rendering
$renderer	= new Renderer($blog, $manager, $blog->getTheme());
debugOutput('Renderer initialised');

	// Render pages
	$renderer->renderPages();
	debugOutput('Pages rendered');

	// Render draft posts
	$renderer->renderDrafts();
	debugOutput('Drafts rendered');

	// Render posts and archives
	foreach($archive as $year){
		/** @var \Blight\Collections\Year $year */
		// Render posts
		$posts		= $year->getPosts();
		$noPosts	= count($posts);
		for($i = 0; $i < $noPosts; $i++){
			$prev	= (isset($posts[$i+1]) ? $posts[$i+1] : null);
			$next	= (isset($posts[$i-1]) ? $posts[$i-1] : null);
			$renderer->renderPost($posts[$i], $prev, $next);
		}
		debugOutput('Year "%s" posts rendered', $year->getName());

		// Render archive
		$renderer->renderYear($year, array(
			'per_page'	=> $blog->get('page', 'limits', 0)
		));
		debugOutput('Year "%s" archive rendered', $year->getName());
	}

	// Render RSS-only posts
	$posts	= $manager->getPosts(array(
		'rss'	=> true
	));
	foreach($posts as $post){
		$renderer->renderPost($post);
	}
	debugOutput('RSS-only posts rendered');

	// Render tag pages
	$renderer->renderTags(array(
		'per_page'	=> $blog->get('page', 'limits', 0)
	));
	debugOutput('Tags rendered');

	// Render category pages
	$renderer->renderCategories(array(
		'per_page'	=> $blog->get('page', 'limits', 0)
	));
	debugOutput('Categories rendered');

	// Render home page
	$renderer->renderHome(array(
		'limit'	=> $blog->get('home', 'limits', $blog->get('page', 'limits', 10))
	));
	debugOutput('Home rendered');

	// Render feeds
	$renderer->renderFeeds(array(
		'limit'	=> $blog->get('feed', 'limits', $blog->get('page', 'limits', 15))
	));
	debugOutput('Feeds rendered');

	// Render sitemap
	$renderer->renderSitemap(array(
	));
	debugOutput('Sitemap rendered');

	// Rendering completed

// Copy theme assets
$renderer->updateThemeAssets();
debugOutput('Theme assets updated');

// Copy user assets
$renderer->updateUserAssets();
debugOutput('User assets updated');

// Remove old draft files
$manager->cleanupDrafts();

debugOutput('Build time: '.(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']).'s');
debugOutput('Peak Memory: '.floor(memory_get_usage()/1024).'KB');
if(IS_CLI){
	echo 'Blog built'.PHP_EOL;
}
