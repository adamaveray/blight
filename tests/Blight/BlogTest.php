<?php
namespace Blight;

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
		$this->assertEquals($this->blog->get_path_root(), $this->root_path);

		$this->assertEquals($this->blog->get_path_root('test'), $this->root_path.'test');
	}

	/**
	 * @covers \Blight\Blog::get_path_cache
	 */
	public function testGetPathCache(){
		$this->assertEquals($this->blog->get_path_cache(), $this->root_path.$this->config['paths']['cache']);

		$this->assertEquals($this->blog->get_path_cache('test'), $this->root_path.$this->config['paths']['cache'].'test');
	}

	/**
	 * @covers \Blight\Blog::get_path_app
	 */
	public function testGetPathApp(){
		$path	= 'blight/';

		$this->assertEquals($this->blog->get_path_app(), $this->root_path.$path);

		$this->assertEquals($this->blog->get_path_app('test'), $this->root_path.$path.'test');
	}

	/**
	 * @covers \Blight\Blog::get_path_templates
	 */
	public function testGetPathTemplates(){
		$this->assertEquals($this->blog->get_path_templates(), $this->root_path.$this->config['paths']['templates']);

		$this->assertEquals($this->blog->get_path_templates('test'), $this->root_path.$this->config['paths']['templates'].'test');
	}

	/**
	 * @covers \Blight\Blog::get_path_posts
	 */
	public function testGetPathPosts(){
		$this->assertEquals($this->blog->get_path_posts(), $this->root_path.$this->config['paths']['posts']);

		$this->assertEquals($this->blog->get_path_posts('test'), $this->root_path.$this->config['paths']['posts'].'test');
	}

	/**
	 * @covers \Blight\Blog::get_path_drafts
	 */
	public function testGetPathDrafts(){
		$this->assertEquals($this->blog->get_path_drafts(), $this->root_path.$this->config['paths']['drafts']);

		$this->assertEquals($this->blog->get_path_drafts('test'), $this->root_path.$this->config['paths']['drafts'].'test');
	}

	/**
	 * @covers \Blight\Blog::get_path_drafts_web
	 */
	public function testGetPathDraftsWeb(){
		$this->assertEquals($this->blog->get_path_drafts_web(), $this->root_path.$this->config['paths']['drafts-web']);

		$this->assertEquals($this->blog->get_path_drafts_web('test'), $this->root_path.$this->config['paths']['drafts-web'].'test');
	}

	/**
	 * @covers \Blight\Blog::get_path_www
	 */
	public function testGetPathWWW(){
		$this->assertEquals($this->blog->get_path_www(), $this->root_path.$this->config['paths']['web']);

		$this->assertEquals($this->blog->get_path_www('test'), $this->root_path.$this->config['paths']['web'].'test');
	}

	/**
	 * @covers \Blight\Blog::test_url
	 */
	public function testGetURL(){
		$this->assertEquals($this->blog->get_url(), $this->config['url']);

		$this->assertEquals($this->blog->get_url('test'), $this->config['url'].'test');
	}

	/**
	 * @covers \Blight\Blog::get_name
	 */
	public function testGetName(){
		$this->assertEquals($this->blog->get_name(), $this->config['name']);
	}

	/**
	 * @covers \Blight\Blog::get_description
	 */
	public function testGetDescription(){
		$this->assertEquals($this->blog->get_description(), $this->config['description']);
	}

	/**
	 * @covers \Blight\Blog::get_feed_url
	 */
	public function testGetFeedUrl(){
		$this->assertEquals($this->blog->get_feed_url(), $this->config['url'].'feed');
	}

	/**
	 * @covers \Blight\Blog::get_eol
	 */
	public function testGetEOL(){
		$this->assertEquals($this->blog->get_eol(), "\n");
	}

	/**
	 * @covers \Blight\Blog::is_linkblog
	 */
	public function testIsLinkblog(){
		$this->assertEquals($this->blog->is_linkblog(), $this->config['linkblog']['linkblog']);

		$config	= $this->config;

		$config['linkblog']['linkblog']	= false;
		$blog	= new \Blight\Blog($config);
		$this->assertEquals($blog->is_linkblog(), false);

		$config['linkblog']['linkblog']	= true;
		$blog	= new \Blight\Blog($config);
		$this->assertEquals($blog->is_linkblog(), true);
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
	 * @covers \Blight\Blog::get
	 */
	public function testGet(){
		// Test existing, non-grouped
		$this->assertEquals($this->blog->get('description'), $this->config['description']);
		$this->assertEquals($this->blog->get('description', null), $this->config['description']);
		$this->assertEquals($this->blog->get('description', null, '(notfound)'), $this->config['description']);

		// Test non-existing, non-grouped
		$this->assertNull($this->blog->get('nonexistent'));
		$this->assertNull($this->blog->get('nonexistent', null));
		$this->assertEquals($this->blog->get('nonexistent', null, '(notfound)'), '(notfound)');

		// Test existing, grouped
		$this->assertNull($this->blog->get('web'));
		$this->assertEquals($this->blog->get('web', 'paths'), $this->config['paths']['web']);
		$this->assertEquals($this->blog->get('web', 'paths', '(notfound)'), $this->config['paths']['web']);

		// Test non-existing, grouped, group existing
		$this->assertNull($this->blog->get('nonexistent', 'paths'));
		$this->assertEquals($this->blog->get('nonexistent', 'paths', '(notfound)'), '(notfound)');

		// Test non-existing, grouped, group non-existing
		$this->assertNull($this->blog->get('nonexistent', 'nogroup'));
		$this->assertEquals($this->blog->get('nonexistent', 'nogroup', '(notfound)'), '(notfound)');
	}
};