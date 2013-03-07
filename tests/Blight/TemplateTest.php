<?php
namespace Blight;

class TemplateTest extends \PHPUnit_Framework_TestCase {
	/** @var \Blight\Interfaces\Blog */
	protected $blog;

	protected $template_php_name;
	protected $template_twig_name;
	protected $template_content;
	protected $template_content_variable;

	/** @var \Blight\Interfaces\Template */
	protected $template_php;
	/** @var \Blight\Interfaces\Template */
	protected $template_twig;

	public function setUp(){
		global $config;
		$test_config	= $config;
		$test_config['paths']['templates']	= __DIR__.'/files/templates/';
		$this->blog		= new \Blight\Blog($test_config);

		$this->template_php_name	= 'test_php';
		$this->template_twig_name	= 'test_twig';

		$this->template_content				= 'Template'."\n".'No Variable';
		$this->template_content_variable	= 'Template'."\n".'Variable: %s';

		$this->template_php		= new \Blight\Template($this->blog, $this->template_php_name);
		$this->template_twig	= new \Blight\Template($this->blog, $this->template_twig_name);
	}

	/**
	 * @covers \Blight\Template::__construct
	 */
	public function testPHPConstruct(){
		$template	= new \Blight\Template($this->blog, $this->template_php_name);
		$this->assertInstanceOf('\Blight\Template', $template);
	}

	/**
	 * @covers \Blight\Template::__construct
	 */
	public function testTwigConstruct(){
		$template	= new \Blight\Template($this->blog, $this->template_twig_name);
		$this->assertInstanceOf('\Blight\Template', $template);
	}

	/**
	 * @covers \Blight\Template::__construct
	 * @expectedException \RuntimeException
	 */
	public function testNonexistentConstruct(){
		new \Blight\Template($this->blog, 'nonexistent');
	}

	/**
	 * @covers \Blight\Template::render
	 */
	public function testPHPRender(){
		// No params
		$this->assertEquals($this->template_php->render(), $this->template_content);

		// With params
		$this->assertEquals($this->template_php->render(array(
			'variable'	=> 'test'
		)), sprintf($this->template_content_variable, 'test'));
	}

	/**
	 * @covers \Blight\Template::render
	 */
	public function testTwigRender(){
		// No params
		$this->assertEquals($this->template_twig->render(), $this->template_content);

		// With params
		$this->assertEquals($this->template_twig->render(array(
			'variable'	=> 'test'
		)), sprintf($this->template_content_variable, 'test'));
	}

	/**
	 * @covers \Blight\Template
	 * @expectedException \InvalidArgumentException
	 */
	public function testInvalidRender(){
		$this->template_php->render('Not Array');
	}
};