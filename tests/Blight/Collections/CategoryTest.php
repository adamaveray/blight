<?php
namespace Blight\Models\Collections;

require_once(__DIR__.'/CollectionTest.php');

class CategoryTest extends CollectionTest {
	static public function setUpBeforeClass(){
		static::$class	= '\Blight\Models\Collections\Category';
	}

	/**
	 * @covers \Blight\Models\Collections\Category::getURL
	 */
	public function testGetURL(){
		$name	= 'Test Name';
		$category	= new \Blight\Models\Collections\Category($this->blog, $name);
		$this->assertEquals($category->getURL(), $this->blog->getURL().'category/test-name');
	}
};