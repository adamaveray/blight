<?php
namespace Blight\Tests;

class BlogTest extends \PHPUnit_Framework_TestCase {
	/** @var \Blight\Interfaces\Blog */
	protected $blog;
	protected $rootPath;
	protected $config;

	public function setUp(){
		global $rootPath, $config;

		$this->rootPath	= $rootPath;
		$this->config	= $config;

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
	 * @covers \Blight\Blog::getPathRoot
	 */
	public function testGetPathRoot(){
		$this->assertEquals($this->rootPath, $this->blog->getPathRoot());

		$this->assertEquals($this->rootPath.'test', $this->blog->getPathRoot('test'));
	}

	/**
	 * @covers \Blight\Blog::getPathCache
	 */
	public function testGetPathCache(){
		$this->assertEquals($this->rootPath.$this->config['paths']['cache'], $this->blog->getPathCache());

		$this->assertEquals($this->rootPath.$this->config['paths']['cache'].'test', $this->blog->getPathCache('test'));
	}

	/**
	 * @covers \Blight\Blog::getPathApp
	 */
	public function testGetPathApp(){
		$path	= 'Blight.phar/';

		$this->assertEquals('phar://'.$this->rootPath.$path, $this->blog->getPathApp());

		$this->assertEquals('phar://'.$this->rootPath.$path.'test', $this->blog->getPathApp('test'));
	}

	/**
	 * @covers \Blight\Blog::getPathThemes
	 */
	public function testGetPathThemes(){
		$this->assertEquals($this->rootPath.$this->config['paths']['themes'], $this->blog->getPathThemes());

		$this->assertEquals($this->rootPath.$this->config['paths']['themes'].'test', $this->blog->getPathThemes('test'));
	}

	/**
	 * @covers \Blight\Blog::getPathPlugins
	 */
	public function testGetPathPlugins(){
		$this->assertEquals($this->rootPath.$this->config['paths']['plugins'], $this->blog->getPathPlugins());

		$this->assertEquals($this->rootPath.$this->config['paths']['plugins'].'test', $this->blog->getPathPlugins('test'));
	}

	/**
	 * @covers \Blight\Blog::getPathPages
	 */
	public function testGetPathPages(){
		$this->assertEquals($this->rootPath.$this->config['paths']['pages'], $this->blog->getPathPages());

		$this->assertEquals($this->rootPath.$this->config['paths']['pages'].'test', $this->blog->getPathPages('test'));
	}

	/**
	 * @covers \Blight\Blog::getPathPosts
	 */
	public function testGetPathPosts(){
		$this->assertEquals($this->rootPath.$this->config['paths']['posts'], $this->blog->getPathPosts());

		$this->assertEquals($this->rootPath.$this->config['paths']['posts'].'test', $this->blog->getPathPosts('test'));
	}

	/**
	 * @covers \Blight\Blog::getPathAssets
	 */
	public function testGetPathAssets(){
		$this->assertEquals($this->rootPath.$this->config['paths']['assets'], $this->blog->getPathAssets());

		$this->assertEquals($this->rootPath.$this->config['paths']['assets'].'test', $this->blog->getPathAssets('test'));
	}

	/**
	 * @covers \Blight\Blog::getPathDrafts
	 */
	public function testGetPathDrafts(){
		$this->assertEquals($this->rootPath.$this->config['paths']['drafts'], $this->blog->getPathDrafts());

		$this->assertEquals($this->rootPath.$this->config['paths']['drafts'].'test', $this->blog->getPathDrafts('test'));
	}

	/**
	 * @covers \Blight\Blog::getPathDraftsWeb
	 */
	public function testGetPathDraftsWeb(){
		$this->assertEquals($this->rootPath.$this->config['paths']['drafts-web'], $this->blog->getPathDraftsWeb());

		$this->assertEquals($this->rootPath.$this->config['paths']['drafts-web'].'test', $this->blog->getPathDraftsWeb('test'));
	}

	/**
	 * @covers \Blight\Blog::getPathWWW
	 */
	public function testGetPathWWW(){
		$this->assertEquals($this->rootPath.$this->config['paths']['web'], $this->blog->getPathWWW());

		$this->assertEquals($this->rootPath.$this->config['paths']['web'].'test', $this->blog->getPathWWW('test'));
	}

	/**
	 * @covers \Blight\Blog::test_url
	 */
	public function testGetURL(){
		$this->assertEquals($this->config['site']['url'], $this->blog->getURL());

		$this->assertEquals($this->config['site']['url'].'test', $this->blog->getURL('test'));

		$this->assertEquals($this->config['site']['url'].'test', $this->blog->getURL('/test'));
	}

	/**
	 * @covers \Blight\Blog::getName
	 */
	public function testGetName(){
		$this->assertEquals($this->config['site']['name'], $this->blog->getName());
	}

	/**
	 * @covers \Blight\Blog::getDescription
	 */
	public function testGetDescription(){
		$this->assertEquals($this->config['site']['description'], $this->blog->getDescription());
	}

	/**
	 * @covers \Blight\Blog::getTimezone
	 */
	public function testGetTimezone(){
		$this->assertInstanceOf('\DateTimezone', $this->blog->getTimezone());
		$this->assertEquals($this->config['site']['timezone'], $this->blog->getTimezone()->getName());
	}

	/**
	 * @covers \Blight\Blog::getFeedURL
	 */
	public function testGetFeedUrl(){
		$this->assertEquals($this->config['site']['url'].'feed', $this->blog->getFeedURL());
	}

	/**
	 * @covers \Blight\Blog::isLinkblog
	 */
	public function testIsLinkblog(){
		$this->assertEquals($this->config['linkblog']['linkblog'], $this->blog->isLinkblog());

		$config	= $this->config;

		$config['linkblog']['linkblog']	= false;
		$blog	= new \Blight\Blog($config);
		$this->assertFalse($blog->isLinkblog());

		$config['linkblog']['linkblog']	= true;
		$blog	= new \Blight\Blog($config);
		$this->assertTrue($blog->isLinkblog());
	}

	/**
	 * @covers \Blight\Blog::setAuthors
	 */
	public function testSetAuthors(){
		$authorName	= 'Test Author';

		$alternateConfig	= $this->config;
		$alternateConfig['author']	= $authorName;
		$blog	= new \Blight\Blog($alternateConfig);
		$author	= new \Blight\Models\Author($blog, array(
			'name'	=> $authorName
		));
		$blog->setAuthors(array($author));
	}

	/**
	 * @covers \Blight\Blog::setAuthors
	 * @expectedException \InvalidArgumentException
	 */
	public function testSetInvalidAuthors(){
		$blog	= new \Blight\Blog($alternateConfig);
		$blog->setAuthors(array('not an author'));
	}

	/**
	 * @covers \Blight\Blog::getAuthor
	 * @depends testSetAuthors
	 */
	public function testGetAuthor(){
		$authorName	= 'Test Author';

		$alternateConfig	= $this->config;
		$alternateConfig['author']	= $authorName;
		$blog	= new \Blight\Blog($alternateConfig);
		$author	= new \Blight\Models\Author($blog, array(
			'name'	=> $authorName
		));
		$blog->setAuthors(array($author));

		$this->assertEquals($author, $blog->getAuthor());
		$this->assertEquals($author, $blog->getAuthor($authorName));
	}

	/**
	 * @covers \Blight\Blog::getAuthors
	 * @depends testSetAuthors
	 */
	public function testGetAuthors(){
		$authorNames	= array('Test Author', 'Test Author 2');

		$alternateConfig	= $this->config;
		$alternateConfig['author']	= $authorName;
		$blog	= new \Blight\Blog($alternateConfig);
		$authors	= array(
			new \Blight\Models\Author($blog, array(
				'name'	=> $authorNames[0]
			)),
			new \Blight\Models\Author($blog, array(
				'name'	=> $authorNames[1]
			))
		);
		$blog->setAuthors($authors);

		$resultAuthors	= $blog->getAuthors();
		$this->assertCount(count($authors), $resultAuthors);
		$this->assertEquals($author[0], $resultAuthors[0]);
	}

	/**
	 * @covers \Blight\Blog::get
	 */
	public function testGet(){
		// Test existing, non-grouped
		$this->assertEquals($this->config['root_path'], $this->blog->get('root_path'));
		$this->assertEquals($this->config['root_path'], $this->blog->get('root_path', '(notfound)'));

		// Test non-existing, non-grouped
		$this->assertNull($this->blog->get('nonexistent'));
		$this->assertEquals('(notfound)', $this->blog->get('nonexistent', '(notfound)'));

		// Test existing, grouped
		$this->assertNull($this->blog->get('web'));
		$this->assertEquals($this->config['paths']['web'], $this->blog->get('paths.web'));
		$this->assertEquals($this->config['paths']['web'], $this->blog->get('paths.web', '(notfound)'));

		// Test non-existing, grouped, with existing group
		$this->assertNull($this->blog->get('paths.nonexistent'));
		$this->assertEquals('(notfound)', $this->blog->get('paths.nonexistent', '(notfound)'));

		// Test non-existing, grouped, group non-existing
		$this->assertNull($this->blog->get('nogroup.nonexistent'));
		$this->assertEquals('(notfound)', $this->blog->get('nogroup.nonexistent', '(notfound)'));
	}
};