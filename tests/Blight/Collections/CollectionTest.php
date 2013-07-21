<?php
namespace Blight\Models\Collections;

class CollectionTest extends \PHPUnit_Framework_TestCase {
	protected static $class;

	/** @var \Blight\Interfaces\Blog */
	protected $blog;

	protected $collection;
	protected $collectionName	= 'Test Name';
	protected $collectionSlug	= 'test-name';

	public function setUp(){
		global $config;

		$this->blog	= new \Blight\Blog($config);

		$this->collection	= new static::$class($this->blog, $this->collectionName);
	}

	/**
	 * @covers \Blight\Models\Collections\Collection::__construct
	 */
	public function testConstruct(){
		$name	= 'Test';
		$collection	= new static::$class($this->blog, $name);
		$this->assertInstanceOf(static::$class, $collection);
	}

	/**
	 * @covers \Blight\Models\Collections\Collection::getName
	 */
	public function testGetName(){
		$this->assertEquals($this->collection->getName(), $this->collectionName);
	}

	/**
	 * @covers \Blight\Models\Collections\Collection::getSlug
	 */
	public function testGetSlug(){
		$this->assertEquals($this->collection->getSlug(), $this->collectionSlug);
	}

	/**
	 * @covers \Blight\Models\Collections\Collection::getURL
	 */
	public function testGetURL(){
		$this->assertEquals($this->collection->getURL(), $this->blog->getURL().$this->collectionSlug);
	}

	/**
	 * @covers \Blight\Models\Collections\Collection::setPosts
	 */
	public function testSetPosts(){
		$content	= <<<EOD
Test Post
=========

Test Content
EOD;
		$slug	= 'test-post';

		$posts	= array(
			new \Blight\Models\Post($this->blog, $content, $slug)
		);

		$this->collection->setPosts($posts);
	}

	/**
	 * @covers \Blight\Models\Collections\Collection::setPosts
	 * @expectedException \InvalidArgumentException
	 */
	public function testInvalidSetPosts(){
		$posts	= array(
			'Not a post'
		);

		$this->collection->setPosts($posts);
	}

	/**
	 * @covers \Blight\Models\Collections\Collection::getPosts
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
			new \Blight\Models\Post($this->blog, $content, $slug)
		);

		$this->collection->setPosts($posts);

		// After adding posts
		$this->assertEquals($this->collection->getPosts(), $posts);
	}

	/**
	 * @covers \Blight\Models\Collections\Collection::addPost
	 * @depends testGetPosts
	 */
	public function testAddPost(){
		$content	= <<<EOD
Test Post
=========

Test Content
EOD;
		$slug	= 'test-post';

		$post	= new \Blight\Models\Post($this->blog, $content, $slug);

		$this->collection->addPost($post);

		$this->assertEquals($this->collection->getPosts(), array($post));
	}
};
