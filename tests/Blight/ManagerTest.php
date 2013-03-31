<?php
namespace Blight\Tests;

class ManagerTest extends \PHPUnit_Framework_TestCase {
	protected static $pages_count		= 2;
	protected static $posts_count		= 5;
	protected static $drafts_count		= 3;
	protected static $collection_count_year		= 2;
	protected static $collection_count_tag		= 1;
	protected static $collection_count_category	= 1;

	static public function setUpBeforeClass(){
		$dir	= __DIR__.'/files/posts/';

		$now	= new \DateTime();
		$date	= $now->format('Y-m-d H:i:s');
		$page_content	= <<<EOD
Test Page
=========
Date: {DATE} 12:00:00

Test content.
EOD;
		$post_content	= <<<EOD
Test Post
=========
Date: {DATE} 12:00:00
Tags: Test Tag
Category: General

Test content.
EOD;

		if(!is_dir($dir)){
			mkdir($dir);
		}

		$dirs	= array(
			'pages'			=> 'pages/',
			'posts'			=> 'posts/',
			'drafts'		=> 'drafts/',
			'drafts_web'	=> 'drafts_web/'
		);
		foreach($dirs as $child_dir){
			$child_dir	= $dir.$child_dir;
			if(!is_dir($child_dir)){
				mkdir($child_dir);
			}
		}

		for($i = 0; $i < self::$pages_count; $i++){
			file_put_contents($dir.$dirs['pages'].'test-page-'.$i.'.md', str_replace('{DATE}', '2013-02-0'.$i, $page_content));
		}

		for($i = 0; $i < self::$posts_count; $i++){
			file_put_contents($dir.$dirs['posts'].'test-post-2013-'.$i.'.md', str_replace('{DATE}', '2013-02-0'.$i, $post_content));
		}
		for($i = 0; $i < self::$posts_count; $i++){
			file_put_contents($dir.$dirs['posts'].'test-post-2012-'.$i.'.md', str_replace('{DATE}', '2012-02-0'.$i, $post_content));
		}
		self::$posts_count	*= 2;

		for($i = 0; $i < self::$drafts_count; $i++){
			file_put_contents($dir.$dirs['drafts'].'test-post-'.$i.'.md', str_replace('{DATE}', '2013-02-0'.$i, $post_content));
		}
	}

	static public function tearDownAfterClass(){
		function delete_dir($dir){
			$dir	= rtrim($dir, '/');

			foreach(glob($dir.'/*') as $file){
				if(is_dir($file)){
					delete_dir($file);
				} else {
					unlink($file);
				}
			}

			rmdir($dir);
		}

		$dir	= __DIR__.'/files/';
		delete_dir($dir.'posts/');
	}

	/** @var \Blight\Interfaces\Blog */
	protected $blog;
	/** @var \Blight\Interfaces\Manager */
	protected $manager;

	protected $dir;

	public function setUp(){
		global $config;

		$this->dir	= __DIR__.'/files/posts/';

		$test_config	= $config;
		$test_config['paths']['pages']		= $this->dir.'pages/';
		$test_config['paths']['posts']		= $this->dir.'posts/';
		$test_config['paths']['drafts']		= $this->dir.'drafts/';
		$test_config['paths']['drafts_web']	= $this->dir.'drafts_web/';
		$this->blog	= new \Blight\Blog($test_config);

		$this->manager	= new \Blight\Manager($this->blog);
	}

	/**
	 * @covers \Blight\Manager::__construct
	 */
	public function testConstruct(){
		$manager	= new \Blight\Manager($this->blog);
		$this->assertInstanceOf('\Blight\Manager', $manager);
	}

	/**
	 * @covers \Blight\Manager::__construct
	 * @expectedException \RuntimeException
	 */
	public function testInvalidConstruct(){
		global $config;
		$test_config	= $config;
		$test_config['paths']['posts']	= 'nonexistent';
		$blog	= new \Blight\Blog($test_config);

		new \Blight\Manager($blog);
	}

	/**
	 * @covers \Blight\Manager::getPages
	 */
	public function testGetPages(){
		$pages	= $this->manager->getPages();
		$this->assertTrue(is_array($pages));
		$this->assertEquals(count($pages), self::$pages_count);
		foreach($pages as $page){
			$this->assertInstanceOf('\Blight\Interfaces\Models\Page', $page);
		}
	}

	/**
	 * @covers \Blight\Manager::getDraftPosts
	 */
	public function testGetDraftPosts(){
		$posts	= $this->manager->getDraftPosts();
		$this->assertTrue(is_array($posts));
		$this->assertEquals(count($posts), self::$drafts_count);
		foreach($posts as $post){
			$this->assertInstanceOf('\Blight\Interfaces\Models\Post', $post);
			$this->assertTrue($post->isDraft());
		}
	}

	/**
	 * @covers \Blight\Manager::getPosts
	 */
	public function testGetPosts(){
		$posts	= $this->manager->getPosts();
		$this->assertTrue(is_array($posts));
		$this->assertEquals(count($posts), self::$posts_count);
		foreach($posts as $post){
			$this->assertInstanceOf('\Blight\Interfaces\Models\Post', $post);
			$this->assertFalse($post->isDraft());
		}
	}

	/**
	 * @covers \Blight\Manager::getPostsByYear
	 */
	public function testGetPostsByYear(){
		$archive	= $this->manager->getPostsByYear();
		$this->assertTrue(is_array($archive));
		$this->assertEquals(self::$collection_count_year, count($archive));
		foreach($archive as $year){
			$this->assertInstanceOf('\Blight\Models\Collections\Year', $year);
		}
	}

	/**
	 * @covers \Blight\Manager::getPostsByTag
	 */
	public function testGetPostsByTag(){
		$tags	= $this->manager->getPostsByTag();
		$this->assertTrue(is_array($tags));
		$this->assertEquals(self::$collection_count_tag, count($tags));
		foreach($tags as $tag){
			$this->assertInstanceOf('\Blight\Models\Collections\Tag', $tag);
		}
	}

	/**
	 * @covers \Blight\Manager::getPostsByCategory
	 */
	public function testGetPostsByCategory(){
		$categories	= $this->manager->getPostsByCategory();
		$this->assertTrue(is_array($categories));
		$this->assertEquals(self::$collection_count_category, count($categories));
		foreach($categories as $category){
			$this->assertInstanceOf('\Blight\Models\Collections\Category', $category);
		}
	}

	/**
	 * @covers \Blight\Manager::cleanupDrafts
	 */
	public function testCleanupDrafts(){
		$dir	= $this->blog->getPathDraftsWeb();
		if(!is_dir($dir)){
			mkdir($dir);
		}
		file_put_contents($dir.'test.html', 'Test file');

		$this->manager->cleanupDrafts();

		// Directory should be empty
		$this->assertEquals(0, count(glob($this->blog->getPathDraftsWeb('*'))));
	}
};