<?php
namespace Blight\Collections;

require_once(__DIR__.'/CollectionTest.php');

class YearTest extends CollectionTest {
	protected $collection_name	= 2013;
	protected $collection_slug	= 2013;

	static public function setUpBeforeClass(){
		static::$class	= '\Blight\Collections\Year';
	}

	/**
	 * @covers \Blight\Collections\Year::get_url
	 */
	public function testGetURL(){
		$y	= date('Y');
		$year	= new \Blight\Collections\Year($this->blog, $y);
		$this->assertEquals($year->get_url(), $this->blog->get_url().'archive/'.$y);
	}
};