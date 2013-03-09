<?php
namespace Blight;

require_once(__DIR__.'/mock/RendererTestManager.php');

class RendererTest extends \PHPUnit_Framework_TestCase {
	static public function setUpBeforeClass(){
		$dir	= __DIR__.'/';
		if(!is_dir($dir.'files/web')){
			mkdir($dir.'files/web');
		}
	}

	/** @var \Blight\Interfaces\Blog */
	protected $blog;
	/** @var \Blight\Interfaces\Manager */
	protected $manager;
	/** @var \Blight\Interfaces\Renderer */
	protected $renderer;

	public function setUp(){
		global $config;
		$test_config	= $config;
		$test_config['paths']['web']		= __DIR__.'/files/web/';
		$test_config['paths']['templates']	= __DIR__.'/files/templates/';
		$this->blog		= new \Blight\Blog($test_config);

		$this->manager	= new \Blight\RendererTestManager($this->blog);
		$content	= <<<EOD
Test Post
=========
Date: 2013-02-01
Tags: Test Tag
Category: General

Test content
EOD;
		$posts	= array(
			new \Blight\Post($this->blog, $content, 'test-post-1'),
			new \Blight\Post($this->blog, $content, 'test-post-2'),
			new \Blight\Post($this->blog, $content, 'test-post-3')
		);
		$this->manager->set_mock_posts($posts, 'posts');
		$this->manager->set_mock_posts($posts, 'drafts');
		$this->manager->set_mock_posts($posts, 'drafts');

		$this->renderer	= new \Blight\Renderer($this->blog, $this->manager);

	}

	/**
	 * @covers \Blight\Renderer::__construct
	 */
	public function testConstruct(){
		$renderer	= new \Blight\Renderer($this->blog, $this->manager);
		$this->assertInstanceOf('\Blight\Interfaces\Renderer', $renderer);
	}

	/**
	 * @covers \Blight\Renderer::__construct
	 * @expectedException \RuntimeException
	 */
	public function testInvalidConstruct(){
		global $config;
		$test_config	= $config;
		$test_config['paths']['templates']	= 'nonexistent';

		$blog	= new \Blight\Blog($test_config);
		new \Blight\Renderer($blog, new \Blight\Manager($blog));
	}

	/**
	 * @covers \Blight\Renderer::render_post
	 */
	public function testRenderPost(){
		$content	= <<<EOD
Test Post
=========
Date: 2013-02-01

Test content
EOD;
		$rendered_content	= <<<EOD
<p>Test content</p>

EOD;

		$post	= new \Blight\Post($this->blog, $content, 'test-post');
		$this->renderer->render_post($post);

		$this->assertEquals(file_get_contents($this->blog->get_path_www($post->get_relative_permalink().'.html')), $rendered_content);
	}

	/**
	 * @covers \Blight\Renderer::render_drafts
	 */
	/*public function testRenderDrafts(){
		$this->renderer->render_drafts();
	}*/

	/**
	 * @covers \Blight\Renderer::render_archives
	 */
	/*public function testRenderArchives(){
		$this->renderer->render_drafts();
	}*/

	/**
	 * @covers \Blight\Renderer::render_year
	 */
	/*public function testRenderYear(){
		$this->renderer->render_year();
	}*/

	/**
	 * @covers \Blight\Renderer::render_tags
	 */
	/*public function testRenderTags(){
		$this->renderer->render_tags();
	}*/

	/**
	 * @covers \Blight\Renderer::render_categories
	 */
	/*public function testRenderCategories(){
		$this->renderer->render_categories();
	}*/

	/**
	 * @covers \Blight\Renderer::render_home
	 */
	/*public function testRenderHome(){
		$this->renderer->render_home();
	}*/

	/**
	 * @covers \Blight\Renderer::render_feed
	 */
	/*public function testRenderFeed(){
		$this->renderer->render_feed();
	}*/
};