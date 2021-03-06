<?php
/**
 * Blight
 * v0.9
 */

define('IS_CLI', (PHP_SAPI === 'cli'));
define('VERBOSE', isset($argv[0]) && in_array('-v', $argv));
if(!isset($_SERVER['REQUEST_TIME_FLOAT'])) $_SERVER['REQUEST_TIME_FLOAT'] = microtime(true);

// Set up environment
date_default_timezone_set('UTC');

require('vendor/autoload.php');
require('src/autoload.php');

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
if(!isset($config)){
	throw new \Exception('Config invalid');
}
$config['root_path']	= $rootPath;
$blog	= new \Blight\Blog($config);

if(IS_CLI){
	if(isset($_SERVER['argv'][1])){
		$command	= $_SERVER['argv'][1];

		if(preg_match('~config:(.*)~', $command, $matches)){
			$config	= explode('.', $matches[1], 2);
			if(!isset($config[1])){
				array_unshift($config, null);
			}

			$value	= $blog->get($config[1], $config[0]);

			echo $value;

			exit;
		}
	}
}

// Set dependencies
$blog->setFileSystem(new \Blight\FileSystem($blog));
$blog->setPackageManager(new \Blight\PackageManager($blog));
	$logger	= new \Monolog\Logger('Blight');
	$logger->pushHandler(new \Blight\EchoHandler(), \Monolog\Logger::DEBUG);

	$logPath	= $blog->get('paths.log');
	if(isset($logPath)){
		$logger->pushHandler(new \Monolog\Handler\StreamHandler($blog->getPathRoot($logPath), \Monolog\Logger::INFO));
	}
$blog->setLogger($logger);
$blog->setCache(new \Blight\Cache($blog));


// Load posts
$manager	= new \Blight\Manager($blog);
$blog->getLogger()->debug('Manager initialised');

$updateManager	= new \Blight\UpdateManager($blog);
$updateManager->setManager($manager);

if(!$updateManager->needsUpdate()){
	// No changes
	$blog->getLogger()->debug('Blog current');
	if(IS_CLI) echo 'Blog current'.PHP_EOL;
	exit;
}


// Prepare for rendering
$renderer	= new \Blight\Renderer($blog, $manager, $blog->getTheme());
$blog->getLogger()->debug('Renderer initialised');


if($updateManager->needsUpdate('drafts')){
	// Render draft posts
	$renderer->renderDrafts($updateManager->getChangedDraftPosts());
	$blog->getLogger()->debug('Drafts rendered');

	// Remove old draft files
	$manager->cleanupDrafts();
}


if($updateManager->needsUpdate('posts')){
	$archive	= $manager->getPostsByYear();
	$blog->getLogger()->debug('Archive built');

	// Render posts and archives
	foreach($archive as $year){
		/** @var \Blight\Models\Collections\Year $year */
		// Render posts
		$posts		= $year->getPosts();
		$noPosts	= count($posts);
		for($i = 0; $i < $noPosts; $i++){
			$prev	= (isset($posts[$i+1]) ? $posts[$i+1] : null);
			$next	= (isset($posts[$i-1]) ? $posts[$i-1] : null);
			$renderer->renderPost($posts[$i], $prev, $next);
		}
		$blog->getLogger()->debug(sprintf('Year "%s" posts rendered', $year->getName()));

		// Render archive
		$renderer->renderYear($year, array(
			'per_page'	=> $blog->get('limits.page', 0)
		));
		$blog->getLogger()->debug(sprintf('Year "%s" archive rendered', $year->getName()));
	}

	// Render RSS-only posts
	$posts	= $manager->getPosts(array(
		'rss'	=> true
	));
	foreach($posts as $post){
		$renderer->renderPost($post);
	}
	$blog->getLogger()->debug('RSS-only posts rendered');

	// Render tag pages
	$renderer->renderTags(array(
		'per_page'	=> $blog->get('limits.page', 0)
	));
	$blog->getLogger()->debug('Tags rendered');

	// Render category pages
	$renderer->renderCategories(array(
		'per_page'	=> $blog->get('limits.page', 0)
	));
	$blog->getLogger()->debug('Categories rendered');

	// Render home and sequential list pages
	$renderer->renderSequential(array(
		'per_page'	=> $blog->get('limits.home', $blog->get('limits.page', 10))
	));
	$blog->getLogger()->debug('Home and sequential pages rendered');

	// Render feeds
	$renderer->renderFeeds(array(
		'limit'	=> $blog->get('limits.feed', $blog->get('limits.page', 15))
	));
	$blog->getLogger()->debug('Feeds rendered');
}


if($updateManager->needsUpdate('pages')){
	// Render pages
	$renderer->renderPages();
	$blog->getLogger()->debug('Pages rendered');

	// Render sitemap
	$renderer->renderSitemap(array(
	));
	$blog->getLogger()->debug('Sitemap rendered');
}


if($updateManager->needsUpdate('supplementary')){
	// Render additional pages
	$renderer->renderSupplementaryPages(array(
		'limit'	=> $blog->get('limits.supplementary', $blog->get('limits.page', 5))
	));
	$blog->getLogger()->debug('Supplementary pages rendered');
}

// Rendering completed

if($updateManager->needsUpdate('theme')){
	// Copy theme assets
	$renderer->updateThemeAssets();
	$blog->getLogger()->debug('Theme assets updated');
}


if($updateManager->needsUpdate('assets')){
	// Copy user assets
	$renderer->updateUserAssets();
	$blog->getLogger()->debug('User assets updated');
}

$blog->getLogger()->info('Blog built', array(
	'Build Time'	=> (microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']).'s',
	'Peak Memory'	=> floor(memory_get_peak_usage()/1024).'KB'
));


// Cache
$updateManager->saveState();

if(IS_CLI) echo 'Blog built'.PHP_EOL;
