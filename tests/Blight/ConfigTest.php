<?php
namespace Blight\Tests;

class ConfigTest extends \PHPUnit_Framework_TestCase {
	/**
	 * @covers \Blight\Config::__construct
	 */
	public function testConstruct(){
		$config	= new \Blight\Config();
	}

	/**
	 * @covers \Blight\Config::unserialize
	 */
	public function testUnserialize(){
		$config	= new \Blight\Config();

		$raw	= <<<EOD
{
    "test": {
        "value": true
    },
    "other": "value"
}
EOD;

		$converted	= array(
			'test'	=> array(
				'value'	=> true
			),
			'other'	=> 'value'
		);

		$this->assertEquals($converted, $config->unserialize($raw));
	}

	/**
	 * @covers \Blight\Config::serialize
	 */
	public function testSerialize(){
		$config	= new \Blight\Config();

		$original	= array(
			'test'	=> array(
				'value'	=> true
			),
			'other'	=> 'value'
		);

		$built	= <<<EOD
{
    "test": {
        "value": true
    },
    "other": "value"
}
EOD;

		$this->assertEquals($built, $config->serialize($original));
	}

	/**
	 * @covers \Blight\Config::unserialize
	 * @covers \Blight\Config::serialize
	 */
	public function testSerializeUnserializeConvesion(){
		$config	= new \Blight\Config();

		$data	= array(
			'test'	=> array(
				'value'	=> true
			),
			'other'	=> 'value'
		);

		$this->assertEquals($data, $config->unserialize($config->serialize($data)));
	}

	/**
	 * @covers \Blight\Config::unserialize
	 * @covers \Blight\Config::serialize
	 */
	public function testUnserializeSerializeConvesion(){
		$config	= new \Blight\Config();

		$data	= <<<EOD
{
    "test": {
        "value": true
    },
    "other": "value"
}
EOD;

		$this->assertEquals($data, $config->serialize($config->unserialize($data)));
	}
};