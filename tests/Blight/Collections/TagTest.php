<?php
namespace Blight\Models\Collections;

require_once(__DIR__.'/CollectionTest.php');

class TagTest extends CollectionTest {
	static public function setUpBeforeClass(){
		static::$class	= '\Blight\Models\Collections\Tag';
	}

	/**
	 * @covers \Blight\Models\Collections\Tag::getURL
	 */
	public function testGetURL(){
		$name	= 'Test Name';
		$tag	= new \Blight\Models\Collections\Tag($this->blog, $name);
		$this->assertEquals($tag->getURL(), $this->blog->getURL().'tag/test-name');
	}
};