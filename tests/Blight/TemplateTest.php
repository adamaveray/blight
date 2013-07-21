<?php
namespace Blight\Tests;

require_once(__DIR__.'/mock/Theme.php');

class TemplateTest extends \PHPUnit_Framework_TestCase {
	/** @var \Blight\Interfaces\Blog */
	protected $blog;
	/** @var \Blight\Tests\Mock\Theme */
	protected $theme;

	protected $templatePHPName;
	protected $templateTwigName;
	protected $templateContent;
	protected $templateContentVariable;

	/** @var \Blight\Interfaces\Models\Template */
	protected $templatePHP;
	/** @var \Blight\Interfaces\Models\Template */
	protected $templateTwig;

	public function setUp(){
		global $config;
		$this->blog		= new \Blight\Blog($config);

		$this->theme	= new \Blight\Tests\Mock\Theme($this->blog, array(
			'path'	=> __DIR__.'/files/'
		));

		$this->templatePHPName	= 'test_php';
		$this->templateTwigName	= 'test_twig';

		$this->templateContent			= 'Template'."\n".'No Variable';
		$this->templateContentVariable	= 'Template'."\n".'Variable: %s';

		$this->templatePHP		= new \Blight\Models\Template($this->blog, $this->theme, $this->templatePHPName);
		$this->templateTwig	= new \Blight\Models\Template($this->blog, $this->theme, $this->templateTwigName);
	}

	/**
	 * @covers \Blight\Models\Template::__construct
	 */
	public function testPHPConstruct(){
		$template	= new \Blight\Models\Template($this->blog, $this->theme, $this->templatePHPName);
		$this->assertInstanceOf('\Blight\Models\Template', $template);
	}

	/**
	 * @covers \Blight\Models\Template::__construct
	 */
	public function testTwigConstruct(){
		$template	= new \Blight\Models\Template($this->blog, $this->theme, $this->templateTwigName);
		$this->assertInstanceOf('\Blight\Models\Template', $template);
	}

	/**
	 * @covers \Blight\Models\Template::__construct
	 * @expectedException \RuntimeException
	 */
	public function testNonexistentConstruct(){
		new \Blight\Models\Template($this->blog, $this->theme, 'nonexistent');
	}

	/**
	 * @covers \Blight\Models\Template::render
	 */
	public function testPHPRender(){
		// No params
		$this->assertEquals($this->templateContent, $this->templatePHP->render());

		// With params
		$this->assertEquals(sprintf($this->templateContentVariable, 'test'), $this->templatePHP->render(array(
			'variable'	=> 'test'
		)));
	}

	/**
	 * @covers \Blight\Models\Template::render
	 */
	public function testTwigRender(){
		// No params
		$this->assertEquals($this->templateContent, $this->templateTwig->render());

		// With params
		$this->assertEquals(sprintf($this->templateContentVariable, 'test'), $this->templateTwig->render(array(
			'variable'	=> 'test'
		)));
	}

	/**
	 * @covers \Blight\Models\Template
	 * @expectedException \InvalidArgumentException
	 */
	public function testInvalidRender(){
		$this->templatePHP->render('Not Array');
	}
};