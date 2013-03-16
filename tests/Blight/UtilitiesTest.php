<?php
namespace Blight\Tests;

class UtilitiesTest extends \PHPUnit_Framework_TestCase {
	/**
	 * @covers \Blight\Utilities::array_multi_merge
	 */
	public function testArrayMultiMerge(){
		$array_one	= array(
			'one'	=> 1,
			'two'	=> 2
		);

		$array_two	= array(
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
		), \Blight\Utilities::array_multi_merge($array_one, $array_two));


		$array_one['more']	= 'not an array';

		$this->assertEquals(array(
			'one'	=> 1,
			'two'	=> 'TWO',
			'five'	=> 5,
			'more'	=> array(
				'not an array',
				'six'	=> 6
			)
		), \Blight\Utilities::array_multi_merge($array_one, $array_two));


		$array_three	= array(
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
		), \Blight\Utilities::array_multi_merge($array_one, $array_two, $array_three));
	}
};