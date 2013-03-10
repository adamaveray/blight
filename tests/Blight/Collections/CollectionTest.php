<?php
namespace Blight\Collections;

class CollectionTest extends \PHPUnit_Framework_TestCase {
	protected static $class;

	/** @var \Blight\Interfaces\Blog */
	protected $blog;

	protected $collection;
	protected $collection_name	= 'Test Name';
	protected $collection_slug	= 'test-name';

	public function setUp(){
		global $config;

		$this->blog	= new \Blight\Blog($config);

		$this->collection	= new static::$class($this->blog, $this->collection_name);
	}

	/**
	 * @covers \Blight\Collections\Collection::__construct
	 */
	public function testConstruct(){
		$name	= 'Test';
		$collection	= new static::$class($this->blog, $name);
		$this->assertInstanceOf(static::$class, $collection);
	}

	/**
	 * @covers \Blight\Collections\Collection::get_name
	 */
	public function testGetName(){
		$this->assertEquals($this->collection->get_name(), $this->collection_name);
	}

	/**
	 * @covers \Blight\Collections\Collection::get_slug
	 */
	public function testGetSlug(){
		$this->assertEquals($this->collection->get_slug(), $this->collection_slug);
	}

	/**
	 * @covers \Blight\Collections\Collection::get_url
	 */
	public function testGetURL(){
		$this->assertEquals($this->collection->get_url(), $this->blog->get_url().$this->collection_slug);
	}

	/**
	 * @covers \Blight\Collections\Collection::set_posts
	 */
	public function testSetPosts(){
		$content	= <<<EOD
Test Post
=========

Test Content
EOD;
		$slug	= 'test-post';

		$posts	= array(
			new \Blight\Post($this->blog, $content, $slug)
		);

		$this->collection->set_posts($posts);
	}

	/**
	 * @covers \Blight\Collections\Collection::set_posts
	 * @expectedException \InvalidArgumentException
	 */
	public function testInvalidSetPosts(){
		$posts	= array(
			'Not a post'
		);

		$this->collection->set_posts($posts);
	}

	/**
	 * @covers \Blight\Collections\Collection::get_posts
	 * @depends testSetPosts
	 */
	public function testGetPosts(){
		// No posts initially
		$this->assertEquals($this->collection->get_posts(), array());

		$content	= <<<EOD
Test Post
=========

Test Content
EOD;
		$slug	= 'test-post';

		$posts	= array(
			new \Blight\Post($this->blog, $content, $slug)
		);

		$this->collection->set_posts($posts);

		// After adding posts
		$this->assertEquals($this->collection->get_posts(), $posts);
	}

	/**
	 * @covers \Blight\Collections\Collection::add_post
	 * @depends testGetPosts
	 */
	public function testAddPost(){
		$content	= <<<EOD
Test Post
=========

Test Content
EOD;
		$slug	= 'test-post';

		$post	= new \Blight\Post($this->blog, $content, $slug);

		$this->collection->add_post($post);

		$this->assertEquals($this->collection->get_posts(), array($post));
	}
};
