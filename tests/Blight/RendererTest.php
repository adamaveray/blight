<?php
namespace Blight\Tests;

require_once(__DIR__.'/mock/RendererTestManager.php');

class RendererTest extends \PHPUnit_Framework_TestCase {
	static public function setUpBeforeClass(){
		$dir	= __DIR__.'/';
		if(!is_dir($dir.'files/web')){
			mkdir($dir.'files/web');
		}
	}

	static public function tearDownAfterClass(){
		self::deleteDir(__DIR__.'/files/web');
	}

	static protected function deleteDir($dir){
		if(!is_dir($dir)){
			return;
		}

		$dir	= rtrim($dir, '/');

		$files	= glob($dir.'/'.'*');

		foreach($files as $file){
			if(is_dir($file)){
				self::deleteDir($file);
			} else {
				unlink($file);
			}
		}

		rmdir($dir);
	}

	/** @var \Blight\Interfaces\Blog */
	protected $blog;
	/** @var \Blight\Tests\Mock\RendererTestManager */
	protected $manager;
	/** @var \Blight\Renderer */
	protected $renderer;

	public function setUp(){
		global $config;
		$test_config	= $config;
		$test_config['paths']['web']		= __DIR__.'/files/web/';
		$test_config['paths']['drafts-web']	= __DIR__.'/files/web/_drafts/';
		$test_config['paths']['templates']	= __DIR__.'/files/templates/';
		$this->blog		= new \Blight\Blog($test_config);

		$this->manager	= new \Blight\Tests\Mock\RendererTestManager($this->blog);
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
	public function testRenderDrafts(){
		$content	= <<<EOD
Test Post
=========
Date: 2013-02-01

Test content
EOD;

		$posts	= array(
			new \Blight\Post($this->blog, $content, 'test-1', true),
			new \Blight\Post($this->blog, $content, 'test-2', true),
			new \Blight\Post($this->blog, $content, 'test-3', true)
		);
		$this->manager->set_mock_posts($posts, 'drafts');

		$this->renderer->render_drafts();

		$output_dir	= $this->blog->get_path_drafts_web();
		$files	= glob($output_dir.'*');

		$this->assertEquals(count($files), count($posts));

		foreach($posts as $post){
			// Check each post had file created with same slug
			$this->assertTrue(in_array($output_dir.$post->get_slug().'.html', $files));
		}
	}

	/**
	 * @covers \Blight\Renderer::render_archives
	 */
	public function testRenderArchives(){
		$content	= <<<EOD
Test Post
=========
Date: 2013-02-01

Test content
EOD;

		$posts	= array(
			new \Blight\Post($this->blog, $content, 'test-1'),
			new \Blight\Post($this->blog, $content, 'test-2'),
			new \Blight\Post($this->blog, $content, 'test-3')
		);

		$date_counts	= array(
			'2013'	=> array(
				'posts'		=> 1,
				'months'	=> array(
					'02'	=> 3
				)
			)
		);

		$this->manager->set_mock_posts($posts, 'posts');

		$this->renderer->render_archives();

		$output_dir	= $this->blog->get_path_www().'archive/';
		$files	= glob($output_dir.'*');
		$this->assertEquals(count($files), count($date_counts));
	}

	/**
	 * @covers \Blight\Renderer::render_year
	 */
	/*public function testRenderYear(){
		$this->renderer->render_year();
	}*/

	/**
	 * @covers \Blight\Renderer::render_tags
	 */
	public function testRenderTags(){
		$content	= <<<EOD
Test Post
=========
Date: 2013-02-01
Tags: Test Tag

Test content
EOD;

		$posts	= array(
			new \Blight\Post($this->blog, $content, 'test-1'),
			new \Blight\Post($this->blog, $content, 'test-2'),
			new \Blight\Post($this->blog, $content, 'test-3')
		);

		$this->manager->set_mock_posts($posts, 'posts');

		$tag_counts	= array(
			'test-tag'	=> 3
		);

		$dir	= $this->blog->get_path_www('tag/');

		$this->renderer->render_tags();

		foreach($tag_counts as $tag => $count){
			$this->assertTrue(file_exists($dir.$tag.'.html'));
		}
	}

	/**
	 * @covers \Blight\Renderer::render_categories
	 */
	public function testRenderCategories(){
		$content	= <<<EOD
Test Post
=========
Date: 2013-02-01
Category: General

Test content
EOD;

		$posts	= array(
			new \Blight\Post($this->blog, $content, 'test-1'),
			new \Blight\Post($this->blog, $content, 'test-2'),
			new \Blight\Post($this->blog, $content, 'test-3')
		);

		$this->manager->set_mock_posts($posts, 'posts');

		$category_counts	= array(
			'general'	=> 3
		);

		$dir	= $this->blog->get_path_www('category/');

		$this->renderer->render_categories();

		foreach($category_counts as $category => $count){
			$this->assertTrue(file_exists($dir.$category.'.html'));
		}
	}

	/**
	 * @covers \Blight\Renderer::render_home
	 */
	public function testRenderHome(){
		$content	= <<<EOD
Test Post
=========
Date: 2013-02-01

Test content
EOD;

		$posts	= array(
			new \Blight\Post($this->blog, $content, 'test-1'),
			new \Blight\Post($this->blog, $content, 'test-2'),
			new \Blight\Post($this->blog, $content, 'test-3')
		);

		$this->renderer->render_home();

		$path	= $this->blog->get_path_www('index.html');

		$this->assertTrue(file_exists($path));
	}

	/**
	 * @covers \Blight\Renderer::render_feed
	 */
	public function testRenderFeed(){
		$content	= <<<EOD
Test Post
=========
Date: 2013-02-01

Test content
EOD;

		$posts	= array(
			new \Blight\Post($this->blog, $content, 'test-1'),
			new \Blight\Post($this->blog, $content, 'test-2'),
			new \Blight\Post($this->blog, $content, 'test-3')
		);

		$this->renderer->render_feed();

		$path	= $this->blog->get_path_www('feed.xml');

		$this->assertTrue(file_exists($path));
	}
};