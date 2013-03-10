<?php
namespace Blight\Collections;

require_once(__DIR__.'/CollectionTest.php');

class CategoryTest extends CollectionTest {
	static public function setUpBeforeClass(){
		static::$class	= '\Blight\Collections\Category';
	}

	/**
	 * @covers \Blight\Collections\Category::get_url
	 */
	public function testGetURL(){
		$name	= 'Test Name';
		$category	= new \Blight\Collections\Category($this->blog, $name);
		$this->assertEquals($category->get_url(), $this->blog->get_url().'category/test-name');
	}
};