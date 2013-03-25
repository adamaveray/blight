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
	 * @covers \Blight\Collections\Collection::getName
	 */
	public function testGetName(){
		$this->assertEquals($this->collection->getName(), $this->collection_name);
	}

	/**
	 * @covers \Blight\Collections\Collection::getSlug
	 */
	public function testGetSlug(){
		$this->assertEquals($this->collection->getSlug(), $this->collection_slug);
	}

	/**
	 * @covers \Blight\Collections\Collection::getURL
	 */
	public function testGetURL(){
		$this->assertEquals($this->collection->getURL(), $this->blog->getURL().$this->collection_slug);
	}

	/**
	 * @covers \Blight\Collections\Collection::setPosts
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

		$this->collection->setPosts($posts);
	}

	/**
	 * @covers \Blight\Collections\Collection::setPosts
	 * @expectedException \InvalidArgumentException
	 */
	public function testInvalidSetPosts(){
		$posts	= array(
			'Not a post'
		);

		$this->collection->setPosts($posts);
	}

	/**
	 * @covers \Blight\Collections\Collection::getPosts
	 * @depends testSetPosts
	 */
	public function testGetPosts(){
		// No posts initially
		$this->assertEquals($this->collection->getPosts(), array());

		$content	= <<<EOD
Test Post
=========

Test Content
EOD;
		$slug	= 'test-post';

		$posts	= array(
			new \Blight\Post($this->blog, $content, $slug)
		);

		$this->collection->setPosts($posts);

		// After adding posts
		$this->assertEquals($this->collection->getPosts(), $posts);
	}

	/**
	 * @covers \Blight\Collections\Collection::addPost
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

		$this->collection->addPost($post);

		$this->assertEquals($this->collection->getPosts(), array($post));
	}
};
