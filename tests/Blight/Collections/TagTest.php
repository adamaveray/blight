<?php
namespace Blight\Collections;

require_once(__DIR__.'/CollectionTest.php');

class TagTest extends CollectionTest {
	static public function setUpBeforeClass(){
		static::$class	= '\Blight\Collections\Tag';
	}

	/**
	 * @covers \Blight\Collections\Tag::get_url
	 */
	public function testGetURL(){
		$name	= 'Test Name';
		$tag	= new \Blight\Collections\Tag($this->blog, $name);
		$this->assertEquals($tag->get_url(), $this->blog->get_url().'tag/test-name');
	}
};