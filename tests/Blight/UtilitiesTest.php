<?php
namespace Blight\Tests;

class UtilitiesTest extends \PHPUnit_Framework_TestCase {
	/**
	 * @covers \Blight\Utilities::arrayMultiMerge
	 */
	public function testArrayMultiMerge(){
		$arrayOne	= array(
			'one'	=> 1,
			'two'	=> 2
		);

		$arrayTwo	= array(
			'two'	=> 'TWO',
			'five'	=> 5,
			'more'	=> array(
				'six'	=> 6
			)
		);

		$this->assertEquals(array(
			'one'	=> 1,
			'two'	=> 'TWO',
			'five'	=> 5,
			'more'	=> array(
				'six'	=> 6
			)
		), \Blight\Utilities::arrayMultiMerge($arrayOne, $arrayTwo));


		$arrayOne['more']	= 'not an array';

		$this->assertEquals(array(
			'one'	=> 1,
			'two'	=> 'TWO',
			'five'	=> 5,
			'more'	=> array(
				'not an array',
				'six'	=> 6
			)
		), \Blight\Utilities::arrayMultiMerge($arrayOne, $arrayTwo));


		$arrayThree	= array(
			'seven'	=> true
		);

		$this->assertEquals(array(
			'one'	=> 1,
			'two'	=> 'TWO',
			'five'	=> 5,
			'more'	=> array(
				'not an array',
				'six'	=> 6
			),
			'seven'	=> true
		), \Blight\Utilities::arrayMultiMerge($arrayOne, $arrayTwo, $arrayThree));
	}
};