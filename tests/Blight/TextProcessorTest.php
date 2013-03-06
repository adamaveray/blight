<?php
namespace Blight;

class TextProcessorTest extends \PHPUnit_Framework_TestCase {
	/** @var \Blight\Blog */
	protected $blog;

	/** @var \Blight\TextProcessor */
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
		$this->assertEquals($this->text_processor->process($raw), $processed);
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
		$this->assertEquals($this->text_processor->process_markdown($raw), $processed);
	}

	/**
	 * @covers \Blight\TextProcessor::process_typography
	 */
	public function testProcessTypography(){
		// Default filters
		$raw		= '<p>Test 123</p>';
		$processed	= '<p>Test <span class="numbers">123</span></p>';

		$this->assertEquals($this->text_processor->process_typography($raw), $processed);
	}

	/**
	 * @covers \Blight\TextProcessor::truncate_html
	 */
	public function testTruncateHTML(){
		$original	= '<p>Lorem ipsum dolor sit amet</p>';
		$truncated	= '<p>Lorem...</p>';
		$this->assertEquals($this->text_processor->truncate_html($original, 10), $truncated);

		$original	= '<p>Lorem ipsum dolor sit amet</p>';
		$truncated	= '<p>Lorem ipsum...</p>';
		$this->assertEquals($this->text_processor->truncate_html($original, 20), $truncated);

		$original	= '<p>Lorem ipsum dolor sit amet</p>';
		$truncated	= '<p>Lorem ipsum dolor!</p>';
		$this->assertEquals($this->text_processor->truncate_html($original, 20, '!'), $truncated);

		$original	= '<p>Lorem ipsum dolor sit amet</p>';
		$this->assertEquals($this->text_processor->truncate_html($original, 100), $original);

		$original	= '';
		$this->assertEquals($this->text_processor->truncate_html($original, 100), $original);
	}
};