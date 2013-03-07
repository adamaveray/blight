<?php
namespace Blight;

class ManagerTest extends \PHPUnit_Framework_TestCase {
	protected static $posts_count		= 5;
	protected static $drafts_count		= 3;
	protected static $collection_count_year		= 2;
	protected static $collection_count_tag		= 1;
	protected static $collection_count_category	= 1;

	static public function setUpBeforeClass(){
		$dir	= __DIR__.'/files/posts/';

		$now	= new \DateTime();
		$date	= $now->format('Y-m-d H:i:s');
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

		$dir	= __DIR__.'/files/posts/';
		delete_dir($dir);
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
	 * @covers \Blight\Manager::get_draft_posts
	 */
	public function testGetDraftPosts(){
		$posts	= $this->manager->get_draft_posts();
		$this->assertTrue(is_array($posts));
		$this->assertEquals(count($posts), self::$drafts_count);
		foreach($posts as $post){
			$this->assertInstanceOf('\Blight\Interfaces\Post', $post);
			$this->assertTrue($post->is_draft());
		}
	}

	/**
	 * @covers \Blight\Manager::get_posts
	 */
	public function testGetPosts(){
		$posts	= $this->manager->get_posts();
		$this->assertTrue(is_array($posts));
		$this->assertEquals(count($posts), self::$posts_count);
		foreach($posts as $post){
			$this->assertInstanceOf('\Blight\Interfaces\Post', $post);
			$this->assertFalse($post->is_draft());
		}
	}

	/**
	 * @covers \Blight\Manager::get_posts_by_year
	 */
	public function testGetPostsByYear(){
		$archive	= $this->manager->get_posts_by_year();
		$this->assertTrue(is_array($archive));
		$this->assertEquals(count($archive), self::$collection_count_year);
		foreach($archive as $year){
			$this->assertInstanceOf('\Blight\Collections\Year', $year);
		}
	}

	/**
	 * @covers \Blight\Manager::get_posts_by_tag
	 */
	public function testGetPostsByTag(){
		$tags	= $this->manager->get_posts_by_tag();
		$this->assertTrue(is_array($tags));
		$this->assertEquals(count($tags), self::$collection_count_tag);
		foreach($tags as $tag){
			$this->assertInstanceOf('\Blight\Collections\Tag', $tag);
		}
	}

	/**
	 * @covers \Blight\Manager::get_posts_by_category
	 */
	public function testGetPostsByCategory(){
		$categories	= $this->manager->get_posts_by_category();
		$this->assertTrue(is_array($categories));
		$this->assertEquals(count($categories), self::$collection_count_category);
		foreach($categories as $category){
			$this->assertInstanceOf('\Blight\Collections\Category', $category);
		}
	}

	/**
	 * @covers \Blight\Manager::cleanup_drafts
	 */
	public function testCleanupDrafts(){
		$dir	= $this->blog->get_path_drafts_web();
		file_put_contents($dir.'test.html', 'Test file');

		$this->manager->cleanup_drafts();

		// Directory should be empty
		$this->assertEquals(count(glob($this->blog->get_path_drafts_web('*'))), 0);
	}
};