<?php
namespace Blight\Tests;

class BlogTest extends \PHPUnit_Framework_TestCase {
	/** @var \Blight\Interfaces\Blog */
	protected $blog;
	protected $root_path;
	protected $config;

	public function setUp(){
		global $root_path, $config;

		$this->root_path	= $root_path;
		$this->config		= $config;

		$this->blog	= new \Blight\Blog($this->config);
	}

	/**
	 * @covers \Blight\Blog::__construct
	 */
	public function testConstruct(){
		$blog	= new \Blight\Blog($this->config);
		$this->assertInstanceOf('\Blight\Interfaces\Blog', $blog);
	}

	/**
	 * @covers \Blight\Blog::__construct
	 * @expectedException \InvalidArgumentException
	 */
	public function testInvalidConstruct(){
		new \Blight\Blog(null);
	}

	/**
	 * @covers \Blight\Blog::__construct
	 * @expectedException \InvalidArgumentException
	 */
	public function testIncompleteConstruct(){
		$config	= $this->config;
		unset($config['paths']);

		new \Blight\Blog($config);
	}

	/**
	 * @covers \Blight\Blog::get_path_root
	 */
	public function testGetPathRoot(){
		$this->assertEquals($this->root_path, $this->blog->get_path_root());

		$this->assertEquals($this->root_path.'test', $this->blog->get_path_root('test'));
	}

	/**
	 * @covers \Blight\Blog::get_path_cache
	 */
	public function testGetPathCache(){
		$this->assertEquals($this->root_path.$this->config['paths']['cache'], $this->blog->get_path_cache());

		$this->assertEquals($this->root_path.$this->config['paths']['cache'].'test', $this->blog->get_path_cache('test'));
	}

	/**
	 * @covers \Blight\Blog::get_path_app
	 */
	public function testGetPathApp(){
		$path	= 'Blight.phar/';

		$this->assertEquals('phar://'.$this->root_path.$path, $this->blog->get_path_app());

		$this->assertEquals('phar://'.$this->root_path.$path.'test', $this->blog->get_path_app('test'));
	}

	/**
	 * @covers \Blight\Blog::get_path_templates
	 */
	public function testGetPathTemplates(){
		$this->assertEquals($this->root_path.$this->config['paths']['templates'], $this->blog->get_path_templates());

		$this->assertEquals($this->root_path.$this->config['paths']['templates'].'test', $this->blog->get_path_templates('test'));
	}

	/**
	 * @covers \Blight\Blog::get_path_plugins
	 */
	public function testGetPathPlugins(){
		$this->assertEquals($this->root_path.$this->config['paths']['plugins'], $this->blog->get_path_plugins());

		$this->assertEquals($this->root_path.$this->config['paths']['plugins'].'test', $this->blog->get_path_plugins('test'));
	}

	/**
	 * @covers \Blight\Blog::get_path_pages
	 */
	public function testGetPathPages(){
		$this->assertEquals($this->root_path.$this->config['paths']['pages'], $this->blog->get_path_pages());

		$this->assertEquals($this->root_path.$this->config['paths']['pages'].'test', $this->blog->get_path_pages('test'));
	}

	/**
	 * @covers \Blight\Blog::get_path_posts
	 */
	public function testGetPathPosts(){
		$this->assertEquals($this->root_path.$this->config['paths']['posts'], $this->blog->get_path_posts());

		$this->assertEquals($this->root_path.$this->config['paths']['posts'].'test', $this->blog->get_path_posts('test'));
	}

	/**
	 * @covers \Blight\Blog::get_path_drafts
	 */
	public function testGetPathDrafts(){
		$this->assertEquals($this->root_path.$this->config['paths']['drafts'], $this->blog->get_path_drafts());

		$this->assertEquals($this->root_path.$this->config['paths']['drafts'].'test', $this->blog->get_path_drafts('test'));
	}

	/**
	 * @covers \Blight\Blog::get_path_drafts_web
	 */
	public function testGetPathDraftsWeb(){
		$this->assertEquals($this->root_path.$this->config['paths']['drafts-web'], $this->blog->get_path_drafts_web());

		$this->assertEquals($this->root_path.$this->config['paths']['drafts-web'].'test', $this->blog->get_path_drafts_web('test'));
	}

	/**
	 * @covers \Blight\Blog::get_path_www
	 */
	public function testGetPathWWW(){
		$this->assertEquals($this->root_path.$this->config['paths']['web'], $this->blog->get_path_www());

		$this->assertEquals($this->root_path.$this->config['paths']['web'].'test', $this->blog->get_path_www('test'));
	}

	/**
	 * @covers \Blight\Blog::test_url
	 */
	public function testGetURL(){
		$this->assertEquals($this->config['site']['url'], $this->blog->get_url());

		$this->assertEquals($this->config['site']['url'].'test', $this->blog->get_url('test'));
	}

	/**
	 * @covers \Blight\Blog::get_name
	 */
	public function testGetName(){
		$this->assertEquals($this->config['site']['name'], $this->blog->get_name());
	}

	/**
	 * @covers \Blight\Blog::get_description
	 */
	public function testGetDescription(){
		$this->assertEquals($this->config['site']['description'], $this->blog->get_description());
	}

	/**
	 * @covers \Blight\Blog::get_feed_url
	 */
	public function testGetFeedUrl(){
		$this->assertEquals($this->config['site']['url'].'feed', $this->blog->get_feed_url());
	}

	/**
	 * @covers \Blight\Blog::is_linkblog
	 */
	public function testIsLinkblog(){
		$this->assertEquals($this->config['linkblog']['linkblog'], $this->blog->is_linkblog());

		$config	= $this->config;

		$config['linkblog']['linkblog']	= false;
		$blog	= new \Blight\Blog($config);
		$this->assertFalse($blog->is_linkblog());

		$config['linkblog']['linkblog']	= true;
		$blog	= new \Blight\Blog($config);
		$this->assertTrue($blog->is_linkblog());
	}

	/**
	 * @covers \Blight\Blog::get_file_system
	 */
	public function testGetFileSystem(){
		$this->assertInstanceOf('\Blight\FileSystem', $this->blog->get_file_system());

		// Should be same instance
		$this->assertEquals($this->blog->get_file_system(), $this->blog->get_file_system());
	}

	/**
	 * @covers \Blight\Blog::get_package_manager
	 */
	public function testGetPackageManager(){
		$this->assertInstanceOf('\Blight\PackageManager', $this->blog->get_package_manager());

		// Should be same instance
		$this->assertEquals($this->blog->get_package_manager(), $this->blog->get_package_manager());
	}

	/**
	 * @covers \Blight\Blog::get
	 */
	public function testGet(){
		// Test existing, non-grouped
		$this->assertEquals($this->config['root_path'], $this->blog->get('root_path'));
		$this->assertEquals($this->config['root_path'], $this->blog->get('root_path', null));
		$this->assertEquals($this->config['root_path'], $this->blog->get('root_path', null, '(notfound)'));

		// Test non-existing, non-grouped
		$this->assertNull($this->blog->get('nonexistent'));
		$this->assertNull($this->blog->get('nonexistent', null));
		$this->assertEquals('(notfound)', $this->blog->get('nonexistent', null, '(notfound)'));

		// Test existing, grouped
		$this->assertNull($this->blog->get('web'));
		$this->assertEquals($this->config['paths']['web'], $this->blog->get('web', 'paths'));
		$this->assertEquals($this->config['paths']['web'], $this->blog->get('web', 'paths', '(notfound)'));

		// Test non-existing, grouped, group existing
		$this->assertNull($this->blog->get('nonexistent', 'paths'));
		$this->assertEquals('(notfound)', $this->blog->get('nonexistent', 'paths', '(notfound)'));

		// Test non-existing, grouped, group non-existing
		$this->assertNull($this->blog->get('nonexistent', 'nogroup'));
		$this->assertEquals('(notfound)', $this->blog->get('nonexistent', 'nogroup', '(notfound)'));
	}
};