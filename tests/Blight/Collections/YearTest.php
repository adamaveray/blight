<?php
namespace Blight\Models\Collections;

require_once(__DIR__.'/CollectionTest.php');

class YearTest extends CollectionTest {
	protected $collection_name	= 2013;
	protected $collection_slug	= 2013;

	static public function setUpBeforeClass(){
		static::$class	= '\Blight\Models\Collections\Year';
	}

	/**
	 * @covers \Blight\Models\Collections\Year::getURL
	 */
	public function testGetURL(){
		$y	= date('Y');
		$year	= new \Blight\Models\Collections\Year($this->blog, $y);
		$this->assertEquals($year->getURL(), $this->blog->getURL().'archive/'.$y);
	}
};