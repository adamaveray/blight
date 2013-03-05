<?php
/**
 * Blight
 * v0.2
 */
namespace Blight;

define('IS_CLI', (PHP_SAPI === 'cli'));
if(!isset($_SERVER['REQUEST_TIME_FLOAT'])) $_SERVER['REQUEST_TIME_FLOAT'] = microtime(true);

// Set up environment
date_default_timezone_set('UTC');
require('src/autoload.php');

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
$archive	= $manager->get_posts_by_year();

// Begin rendering
$renderer	= new Renderer($blog, $manager);

	// Render posts
	foreach($archive as $year){
		foreach($year as $post){
			$renderer->render_post($post);
		}
	}

	// Render archive pages
	$renderer->render_archives(array(
		'per_page'	=> $blog->get('page', 'limits', 0)
	));

	// Render tag pages
	$renderer->render_tags(array(
		'per_page'	=> $blog->get('page', 'limits', 0)
	));

	// Render category pages
	$renderer->render_categories(array(
		'per_page'	=> $blog->get('page', 'limits', 0)
	));

	// Render home page
	$renderer->render_home(array(
		'limit'	=> $blog->get('home', 'limits', $blog->get('page', 'limits', 10))
	));

	// Render feed
	$renderer->render_feed(array(
		'limit'	=> $blog->get('feed', 'limits', $blog->get('page', 'limits', 15))
	));

// Rendering completed
if(IS_CLI){
	echo 'Build time: '.(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']).'s'.PHP_EOL;
}
