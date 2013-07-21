<?php
namespace Blight\Tests;

require_once(__DIR__.'/mock/RendererTestManager.php');
require_once(__DIR__.'/mock/Theme.php');

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
	/** @var \Blight\Tests\Mock\Theme */
	protected $theme;
	/** @var \Blight\Renderer */
	protected $renderer;

	public function setUp(){
		global $config;
		$testConfig	= $config;
		$testConfig['paths']['web']		= __DIR__.'/files/web/';
		$testConfig['paths']['drafts-web']	= __DIR__.'/files/web/_drafts/';
		$this->blog		= new \Blight\Blog($testConfig);

		$this->blog->setFileSystem(new \Blight\FileSystem($this->blog));
		$this->blog->setPackageManager(new \Blight\PackageManager($this->blog));

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
			new \Blight\Models\Post($this->blog, $content, 'test-post-1'),
			new \Blight\Models\Post($this->blog, $content, 'test-post-2'),
			new \Blight\Models\Post($this->blog, $content, 'test-post-3')
		);
		$this->manager->setMockPosts($posts, 'posts');
		$this->manager->setMockPosts($posts, 'drafts');
		$this->manager->setMockPosts($posts, 'drafts');


		$this->theme	= new \Blight\Tests\Mock\Theme($this->blog, array(
			'path'	=> __DIR__.'/files/'
		));

		$this->renderer	= new \Blight\Renderer($this->blog, $this->manager, $this->theme);

	}

	/**
	 * @covers \Blight\Renderer::__construct
	 */
	public function testConstruct(){
		$renderer	= new \Blight\Renderer($this->blog, $this->manager, $this->theme);
		$this->assertInstanceOf('\Blight\Interfaces\Renderer', $renderer);
	}

	/**
	 * @covers \Blight\Renderer::renderPage
	 */
	public function testRenderPage(){
		$content	= <<<EOD
Test Page
=========
Date: 2013-02-01

Test content
EOD;

		$page	= new \Blight\Models\Page($this->blog, $content, 'test-page');

		$this->renderer->renderPage($page);

		$path	= $this->blog->getPathWWW($page->getRelativePermalink().'.html');

		$this->assertTrue(file_exists($path));
	}

	/**
	 * @covers \Blight\Renderer::renderPages
	 */
	public function testRenderPages(){
		$content	= <<<EOD
Test Page
=========
Date: 2013-02-01

Test content
EOD;

		$pages	= array(
			new \Blight\Models\Page($this->blog, $content, 'test-1'),
			new \Blight\Models\Page($this->blog, $content, 'test-2')
		);

		$this->manager->setMockPages($pages);

		$this->renderer->renderPages();

		$path	= $this->blog->getPathWWW();

		foreach($pages as $page){
			/** @var \Blight\Interfaces\Models\Page $page */
			$this->assertTrue(file_exists($path.$page->getRelativePermalink().'.html'));
		}
	}

	/**
	 * @covers \Blight\Renderer::renderPost
	 */
	public function testRenderPost(){
		$content	= <<<EOD
Test Post
=========
Date: 2013-02-01

Test content
EOD;
		$renderedContent	= <<<EOD
<p>Test content</p>

EOD;

		$post	= new \Blight\Models\Post($this->blog, $content, 'test-post');
		$this->renderer->renderPost($post);

		$this->assertEquals($renderedContent, file_get_contents($this->blog->getPathWWW($post->getRelativePermalink().'.html')));
	}

	/**
	 * @covers \Blight\Renderer::renderPost
	 * @expectedException \InvalidArgumentException
	 */
	public function testInvalidRenderPost(){
		$content	= <<<EOD
Test Post
=========
Date: 2013-02-01

Test content
EOD;

		$post	= new \Blight\Models\Post($this->blog, $content, 'test-post');
		$this->renderer->renderPost($post, '(not a post)');
	}

	/**
	 * @covers \Blight\Renderer::renderDrafts
	 */
	public function testRenderDrafts(){
		$content	= <<<EOD
Test Post
=========
Date: 2013-02-01

Test content
EOD;

		$posts	= array(
			new \Blight\Models\Post($this->blog, $content, 'test-1', true),
			new \Blight\Models\Post($this->blog, $content, 'test-2', true),
			new \Blight\Models\Post($this->blog, $content, 'test-3', true)
		);
		$this->manager->setMockPosts($posts, 'drafts');

		$this->renderer->renderDrafts();

		$outputDir	= $this->blog->getPathDraftsWeb();
		$files	= glob($outputDir.'*');

		$this->assertEquals(count($posts), count($files));

		foreach($posts as $post){
			// Check each post had file created with same slug
			$this->assertTrue(in_array($outputDir.$post->getSlug().'.html', $files));
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
			new \Blight\Models\Post($this->blog, $content, 'test-1'),
			new \Blight\Models\Post($this->blog, $content, 'test-2'),
			new \Blight\Models\Post($this->blog, $content, 'test-3')
		);

		$dateCounts	= array(
			'2013'	=> array(
				'posts'		=> 1,
				'months'	=> array(
					'02'	=> 3
				)
			)
		);

		$this->manager->setMockPosts($posts, 'posts');

		$this->renderer->renderArchives();

		$outputDir	= $this->blog->getPathWWW().'archive/';
		$files	= glob($outputDir.'*');
		$this->assertEquals(count($dateCounts), count($files));
	}

	/**
	 * @covers \Blight\Renderer::renderYear
	 */
	public function testRenderYear(){
		$content	= <<<EOD
Test Post
=========
Date: 2012-02-01

Test content
EOD;

		$posts	= array(
			new \Blight\Models\Post($this->blog, $content, 'test-1'),
			new \Blight\Models\Post($this->blog, $content, 'test-2'),
			new \Blight\Models\Post($this->blog, $content, 'test-3')
		);

		$this->manager->setMockPosts($posts, 'posts');

		$years	= $this->manager->getPostsByYear();
		foreach($years as $year){
			if($year->getName() == '2012'){
				$this->renderer->renderYear($year);
				break;
			}
		}

		$this->assertTrue(file_exists($this->blog->getPathWWW().'archive/2012.html'));
	}

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
			new \Blight\Models\Post($this->blog, $content, 'test-1'),
			new \Blight\Models\Post($this->blog, $content, 'test-2'),
			new \Blight\Models\Post($this->blog, $content, 'test-3')
		);

		$this->manager->setMockPosts($posts, 'posts');

		$tagCounts	= array(
			'test-tag'	=> 3
		);

		$dir	= $this->blog->getPathWWW('tag/');

		$this->renderer->renderTags();

		foreach($tagCounts as $tag => $count){
			$this->assertTrue(file_exists($dir.$tag.'.html'));
		}
	}

	/**
	 * @covers \Blight\Renderer::renderCategories
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
			new \Blight\Models\Post($this->blog, $content, 'test-1'),
			new \Blight\Models\Post($this->blog, $content, 'test-2'),
			new \Blight\Models\Post($this->blog, $content, 'test-3')
		);

		$this->manager->setMockPosts($posts, 'posts');

		$categoryCounts	= array(
			'general'	=> 3
		);

		$dir	= $this->blog->getPathWWW('category/');

		$this->renderer->renderCategories();

		foreach($categoryCounts as $category => $count){
			$this->assertTrue(file_exists($dir.$category.'.html'));
		}
	}

	/**
	 * @covers \Blight\Renderer::renderHome
	 */
	public function testRenderHome(){
		$content	= <<<EOD
Test Post
=========
Date: 2013-02-01

Test content
EOD;

		$posts	= array(
			new \Blight\Models\Post($this->blog, $content, 'test-1'),
			new \Blight\Models\Post($this->blog, $content, 'test-2'),
			new \Blight\Models\Post($this->blog, $content, 'test-3')
		);

		$this->renderer->renderHome();

		$path	= $this->blog->getPathWWW('index.html');

		$this->assertTrue(file_exists($path));
	}

	/**
	 * @covers \Blight\Renderer::renderFeeds
	 */
	public function testRenderFeeds(){
		$content	= <<<EOD
Test Post
=========
Date: 2013-02-01

Test content
EOD;

		$posts	= array(
			new \Blight\Models\Post($this->blog, $content, 'test-1'),
			new \Blight\Models\Post($this->blog, $content, 'test-2'),
			new \Blight\Models\Post($this->blog, $content, 'test-3')
		);

		$this->manager->setMockPosts($posts, 'posts');

		$this->renderer->renderFeeds(array(
			'subfeeds'	=> false
		));

		$path	= $this->blog->getPathWWW('feed.xml');

		$this->assertTrue(file_exists($path));
	}

	/**
	 * @covers \Blight\Renderer::renderSitemap
	 */
	public function testRenderSitemap(){
		$content	= <<<EOD
Test Page
=========
Date: 2013-02-01

Test content
EOD;

		$pages	= array(
			new \Blight\Models\Page($this->blog, $content, 'test-1'),
			new \Blight\Models\Page($this->blog, $content, 'test-2')
		);

		$this->manager->setMockPages($pages);

		$this->renderer->renderSitemap();

		$path	= $this->blog->getPathWWW('sitemap.xml');

		$this->assertTrue(file_exists($path));
	}
};