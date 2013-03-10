<?php
namespace Blight\Tests;

class TextProcessorTest extends \PHPUnit_Framework_TestCase {
	/** @var \Blight\Interfaces\Blog */
	protected $blog;

	/** @var \Blight\Interfaces\TextProcessor */
	protected $text_processor;

	public function setUp(){
		global $config;
		$this->blog		= new \Blight\Blog($config);

		$this->text_processor	= new \Blight\TextProcessor($this->blog);
	}

	/**
	 * @covers \Blight\TextProcessor::process
	 */
	public function testProcess(){
		$raw	= <<<EOD
Test
====

Test 123

- Test
EOD;
		$processed	= <<<EOD
<h1>Test</h1>

<p>Test <span class="numbers">123</span></p>

<ul>
<li>Test</li>
</ul>

EOD;
		$this->assertEquals($processed, $this->text_processor->process($raw));
	}

	/**
	 * @covers \Blight\TextProcessor::process_markdown
	 */
	public function testProcessMarkdown(){
		// Default filters
		$raw	= <<<EOD
Test
====

Test 123

- Test
EOD;
		$processed	= <<<EOD
<h1>Test</h1>

<p>Test 123</p>

<ul>
<li>Test</li>
</ul>

EOD;
		$this->assertEquals($processed, $this->text_processor->process_markdown($raw));
	}

	/**
	 * @covers \Blight\TextProcessor::process_typography
	 */
	public function testProcessTypography(){
		// Default filters
		$raw		= '<p>Test 123</p>';
		$processed	= '<p>Test <span class="numbers">123</span></p>';

		$this->assertEquals($processed, $this->text_processor->process_typography($raw));
	}

	/**
	 * @covers \Blight\TextProcessor::truncate_html
	 */
	public function testTruncateHTML(){
		$original	= '<p>Lorem ipsum dolor sit amet</p>';
		$truncated	= '<p>Lorem...</p>';
		$this->assertEquals($truncated, $this->text_processor->truncate_html($original, 10));

		$original	= '<p>Lorem ipsum dolor sit amet</p>';
		$truncated	= '<p>Lorem ipsum...</p>';
		$this->assertEquals($truncated, $this->text_processor->truncate_html($original, 20));

		$original	= '<p>Lorem ipsum dolor sit amet</p>';
		$truncated	= '<p>Lorem ipsum dolor!</p>';
		$this->assertEquals($truncated, $this->text_processor->truncate_html($original, 20, '!'));

		$original	= '<p>Lorem ipsum dolor sit amet</p>';
		$this->assertEquals($original, $this->text_processor->truncate_html($original, 100));

		$original	= '';
		$this->assertEquals($original, $this->text_processor->truncate_html($original, 100));
	}
};