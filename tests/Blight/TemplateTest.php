<?php
namespace Blight\Tests;

require_once(__DIR__.'/mock/Theme.php');

class TemplateTest extends \PHPUnit_Framework_TestCase {
	/** @var \Blight\Interfaces\Blog */
	protected $blog;
	/** @var \Blight\Tests\Mock\Theme */
	protected $theme;

	protected $template_php_name;
	protected $template_twig_name;
	protected $template_content;
	protected $template_content_variable;

	/** @var \Blight\Interfaces\Models\Template */
	protected $template_php;
	/** @var \Blight\Interfaces\Models\Template */
	protected $template_twig;

	public function setUp(){
		global $config;
		$this->blog		= new \Blight\Blog($config);

		$this->theme	= new \Blight\Tests\Mock\Theme($this->blog, array(
			'path'	=> __DIR__.'/files/'
		));

		$this->template_php_name	= 'test_php';
		$this->template_twig_name	= 'test_twig';

		$this->template_content				= 'Template'."\n".'No Variable';
		$this->template_content_variable	= 'Template'."\n".'Variable: %s';

		$this->template_php		= new \Blight\Models\Template($this->blog, $this->theme, $this->template_php_name);
		$this->template_twig	= new \Blight\Models\Template($this->blog, $this->theme, $this->template_twig_name);
	}

	/**
	 * @covers \Blight\Models\Template::__construct
	 */
	public function testPHPConstruct(){
		$template	= new \Blight\Models\Template($this->blog, $this->theme, $this->template_php_name);
		$this->assertInstanceOf('\Blight\Models\Template', $template);
	}

	/**
	 * @covers \Blight\Models\Template::__construct
	 */
	public function testTwigConstruct(){
		$template	= new \Blight\Models\Template($this->blog, $this->theme, $this->template_twig_name);
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
		$this->assertEquals($this->template_content, $this->template_php->render());

		// With params
		$this->assertEquals(sprintf($this->template_content_variable, 'test'), $this->template_php->render(array(
			'variable'	=> 'test'
		)));
	}

	/**
	 * @covers \Blight\Models\Template::render
	 */
	public function testTwigRender(){
		// No params
		$this->assertEquals($this->template_content, $this->template_twig->render());

		// With params
		$this->assertEquals(sprintf($this->template_content_variable, 'test'), $this->template_twig->render(array(
			'variable'	=> 'test'
		)));
	}

	/**
	 * @covers \Blight\Models\Template
	 * @expectedException \InvalidArgumentException
	 */
	public function testInvalidRender(){
		$this->template_php->render('Not Array');
	}
};