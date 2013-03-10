<?php
namespace Blight\Tests;

class PageTest extends \PHPUnit_Framework_TestCase {
	/** @var \Blight\Interfaces\Blog */
	protected $blog;
	protected $content_title;
	protected $content_date;
	protected $content_slug;
	protected $content_text;
	protected $content_metadata;
	protected $content;

	/** @var \Blight\Interfaces\Page */
	protected $page;

	public function setUp(){
		global $config;
		$this->blog	= new \Blight\Blog($config);

		$this->content_title	= 'Test Page';
		$this->content_slug		= 'test-page';
		$this->content_date		= new \DateTime();
		$this->content_metadata	= array(
			'Date'		=> $this->content_date->format('Y-m-d H:i:s'),
			'Test Meta'	=> 'Test Value'
		);

		$meta	= array();
		foreach($this->content_metadata as $key => $value){
			$meta[]	= $key.': '.$value;
		}
		$meta	= implode("\n", $meta);

		$this->content_text		= 'Test content.';

		$this->content	= <<<EOD
$this->content_title
=========
$meta

$this->content_text
EOD;

		$this->page	= new \Blight\Page($this->blog, $this->content, $this->content_slug);
	}

	/**
	 * @covers \Blight\Page::__construct
	 */
	public function testConstruct(){
		$post	= new \Blight\Page($this->blog, $this->content, $this->content_slug);
		$this->assertInstanceOf('\Blight\Page', $post);
	}

	/**
	 * @covers \Blight\Page::__construct
	 * @expectedException \InvalidArgumentException
	 */
	public function testInvalidPageConstruct(){
		$content	= <<<EOD
Not A Page
EOD;
		new \Blight\Page($this->blog, $content, 'test');
	}

	/**
	 * @covers \Blight\Page::get_title
	 */
	public function testGetTitle(){
		$this->assertEquals($this->content_title, $this->page->get_title(true));
	}

	/**
	 * @covers \Blight\Page::get_slug
	 */
	public function testGetSlug(){
		$this->assertEquals($this->content_slug, $this->page->get_slug());
	}

	/**
	 * @covers \Blight\Page::get_date
	 */
	public function testGetDate(){
		$this->assertEquals($this->content_date, $this->page->get_date());
	}

	/**
	 * @covers \Blight\Page::set_date
	 */
	public function testSetDate(){
		$date	= new \DateTime('now');
		$this->page->set_date($date);
		$this->assertEquals($date, $this->page->get_date());
	}

	/**
	 * @covers \Blight\Page::get_content
	 */
	public function testGetContent(){
		$this->assertEquals($this->content_text, $this->page->get_content());
	}

	/**
	 * @covers \Blight\Page::get_metadata
	 */
	public function testGetMetadata(){
		$meta	= array(
			'date'		=> $this->content_metadata['Date'],
			'test-meta'	=> $this->content_metadata['Test Meta']
		);

		$this->assertEquals($meta, $this->page->get_metadata());
	}

	/**
	 * @covers \Blight\Page::get_meta
	 */
	public function testGetMeta(){
		$this->assertEquals($this->content_metadata['Test Meta'], $this->page->get_meta('Test Meta'));
		$this->assertEquals($this->content_metadata['Test Meta'], $this->page->get_meta('test-meta'));

		// Non-existent
		$this->assertNull($this->page->get_meta('nonexistent'));
	}

	/**
	 * @covers \Blight\Page::has_meta
	 */
	public function testHasMeta(){
		$this->assertTrue($this->page->has_meta('Test Meta'));
		$this->assertTrue($this->page->has_meta('test-meta'));
		$this->assertFalse($this->page->has_meta('nonexistent'));
	}

	/**
	 * @covers \Blight\Page::get_link
	 */
	public function testGetLink(){
		$url	= $this->blog->get_url($this->content_slug);
		$this->assertEquals($url, $this->page->get_link());
	}

	/**
	 * @covers \Blight\Page::get_permalink
	 */
	public function testGetPermalink(){
		$url	= $this->blog->get_url($this->content_slug);
		$this->assertEquals($url, $this->page->get_permalink());
	}

	/**
	 * @covers \Blight\Page::get_relative_permalink
	 */
	public function testGetRelativePermalink(){
		$url	= $this->content_slug;
		$this->assertEquals($url, $this->page->get_relative_permalink());
	}
};