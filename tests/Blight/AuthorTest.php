<?php
namespace Blight\Tests;

class AuthorTest extends \PHPUnit_Framework_TestCase {
	/** @var \Blight\Interfaces\Blog */
	protected $blog;

	protected $author;
	protected $authorName;
	protected $authorEmail;
	protected $authorURL;

	public function setUp(){
		global $config;
		$this->blog	= new \Blight\Blog($config);

		$this->authorName	= 'Test Author';
		$this->authorEmail	= 'test@example.com';
		$this->authorURL	= 'http://www.example.com';

		$this->author	= new \Blight\Models\Author($this->blog, array(
			'name'	=> $this->authorName,
			'email'	=> $this->authorEmail,
			'url'	=> $this->authorURL
		));
	}

	/**
	 * @covers \Blight\Models\Author::__construct
	 */
	public function testConstruct(){
		$author	= new \Blight\Models\Author($this->blog, array(
			'name'	=> $this->authorName,
			'email'	=> $this->authorEmail,
			'url'	=> $this->authorURL
		));

		$this->assertInstanceOf('\\Blight\\Models\\Author', $author);
	}

	/**
	 * Test missing `name` field
	 *
	 * @covers \Blight\Models\Author::__construct
	 * @expectedException \InvalidArgumentException
	 */
	public function testInvalidConstruct(){
		new \Blight\Models\Author($this->blog, array(
			'email'	=> $this->authorEmail,
			'url'	=> $this->authorURL
		));
	}

	/**
	 * @covers \Blight\Models\Author::getName
	 */
	public function testGetName(){
		$this->assertEquals($this->authorName, $this->author->getName());
	}

	/**
	 * @covers \Blight\Models\Author::getEmail
	 */
	public function testGet(){
		$this->assertEquals($this->authorEmail, $this->author->getEmail());
	}

	/**
	 * @covers \Blight\Models\Author::getURL
	 */
	public function testGetURL(){
		$this->assertEquals($this->authorURL, $this->author->getURL());
	}

	/**
	 * @covers \Blight\Models\Author::getAttribute
	 */
	public function testGetAttribute(){
		$attributeName	= 'Something';
		$attributeValue	= 'The Value';

		$author	= new \Blight\Models\Author($this->blog, array(
			'name'	=> $this->authorName,
			$attributeName	=> $attributeValue
		));
		$this->assertEquals($attributeValue, $author->getAttribute($attributeName));
		$this->assertEquals($attributeValue, $author->getAttribute(strtolower($attributeName)));
	}

	/**
	 * @covers \Blight\Models\Author::getEmail
	 * @expectedException \RuntimeException
	 */
	public function testInvalidGetEmail(){
		$author	= new \Blight\Models\Author($this->blog, array(
			'name'	=> $this->authorName
		));

		$author->getEmail();
	}

	/**
	 * @covers \Blight\Models\Author::getURL
	 * @expectedException \RuntimeException
	 */
	public function testInvalidGetURL(){
		$author	= new \Blight\Models\Author($this->blog, array(
			'name'	=> $this->authorName
		));

		$author->getURL();
	}

	/**
	 * @covers \Blight\Models\Author::getAttribute
	 * @expectedException \RuntimeException
	 */
	public function testInvalidGetAttribute(){
		$author	= new \Blight\Models\Author($this->blog, array(
			'name'	=> $this->authorName
		));
		$author->getAttribute('(nonexistent)');
	}

	/**
	 * @covers \Blight\Models\Author::hasEmail
	 */
	public function testHasEmail(){
		$this->assertTrue($this->author->hasEmail());
		
		$author	= new \Blight\Models\Author($this->blog, array(
			'name'	=> $this->authorName
		));
		$this->assertFalse($author->hasEmail());
	}

	/**
	 * @covers \Blight\Models\Author::hasURL
	 */
	public function testHasURL(){
		$this->assertTrue($this->author->hasURL());

		$author	= new \Blight\Models\Author($this->blog, array(
			'name'	=> $this->authorName
		));
		$this->assertFalse($author->hasEmail());
	}


	/**
	 * @covers \Blight\Models\Author::hasAttribute
	 */
	public function testHasAttribute(){
		$attributeName	= 'Something';
		$attributeValue	= 'The Value';

		$this->assertFalse($this->author->hasAttribute($attributeName));
		$this->assertFalse($this->author->hasAttribute(strtolower($attributeName)));

		$author	= new \Blight\Models\Author($this->blog, array(
			'name'	=> $this->authorName,
			$attributeName	=> $attributeValue
		));
		$this->assertTrue($author->hasAttribute($attributeName));
		$this->assertTrue($author->hasAttribute(strtolower($attributeName)));
	}
};