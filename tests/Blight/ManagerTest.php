<?php
namespace Blight\Tests;

class ManagerTest extends \PHPUnit_Framework_TestCase {
	protected static $pagesCount		= 2;
	protected static $postsCount		= 5;
	protected static $draftsCount		= 3;
	protected static $collectionCountYear		= 2;
	protected static $collectionCountTag		= 1;
	protected static $collectionCountCategory	= 1;

	static public function setUpBeforeClass(){
		$dir	= __DIR__.'/files/posts/';

		$now	= new \DateTime();
		$date	= $now->format('Y-m-d H:i:s');
		$pageContent	= <<<EOD
Test Page
=========
Date: {DATE} 12:00:00

Test content.
EOD;
		$postContent	= <<<EOD
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
		foreach($dirs as $childDir){
			$childDir	= $dir.$childDir;
			if(!is_dir($childDir)){
				mkdir($childDir);
			}
		}

		for($i = 0; $i < self::$pagesCount; $i++){
			file_put_contents($dir.$dirs['pages'].'test-page-'.$i.'.md', str_replace('{DATE}', '2013-02-0'.$i, $pageContent));
		}

		for($i = 0; $i < self::$postsCount; $i++){
			file_put_contents($dir.$dirs['posts'].'test-post-2013-'.$i.'.md', str_replace('{DATE}', '2013-02-0'.$i, $postContent));
		}
		for($i = 0; $i < self::$postsCount; $i++){
			file_put_contents($dir.$dirs['posts'].'test-post-2012-'.$i.'.md', str_replace('{DATE}', '2012-02-0'.$i, $postContent));
		}
		self::$postsCount	*= 2;

		for($i = 0; $i < self::$draftsCount; $i++){
			file_put_contents($dir.$dirs['drafts'].'test-post-'.$i.'.md', str_replace('{DATE}', '2013-02-0'.$i, $postContent));
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

		$testConfig	= $config;
		$testConfig['paths']['pages']		= $this->dir.'pages/';
		$testConfig['paths']['posts']		= $this->dir.'posts/';
		$testConfig['paths']['drafts']		= $this->dir.'drafts/';
		$testConfig['paths']['drafts_web']	= $this->dir.'drafts_web/';
		$this->blog	= new \Blight\Blog($testConfig);

		$this->blog->setFileSystem(new \Blight\FileSystem($this->blog));

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
		$testConfig	= $config;
		$testConfig['paths']['posts']	= 'nonexistent';
		$blog	= new \Blight\Blog($testConfig);

		new \Blight\Manager($blog);
	}

	/**
	 * @covers \Blight\Manager::getPages
	 */
	public function testGetPages(){
		$pages	= $this->manager->getPages();
		$this->assertTrue(is_array($pages));
		$this->assertEquals(count($pages), self::$pagesCount);
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
		$this->assertEquals(count($posts), self::$draftsCount);
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
		$this->assertEquals(count($posts), self::$postsCount);
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
		$this->assertEquals(self::$collectionCountYear, count($archive));
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
		$this->assertEquals(self::$collectionCountTag, count($tags));
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
		$this->assertEquals(self::$collectionCountCategory, count($categories));
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